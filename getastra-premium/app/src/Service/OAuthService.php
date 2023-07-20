<?php

namespace AstraPrefixed\GetAstra\Client\Service;

use InvalidArgumentException;
use AstraPrefixed\League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use AstraPrefixed\League\OAuth2\Client\Provider\GenericProvider;
use AstraPrefixed\League\OAuth2\Client\Token\AccessToken;
use LogicException;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use AstraPrefixed\GetAstra\Client\Tclient\Configuration;
use AstraPrefixed\GetAstra\Client\Tclient\SiteApi;
use AstraPrefixed\GuzzleHttp\Client;
use AstraPrefixed\GetAstra\Client\Service\SiteSettingsService;
use AstraPrefixed\GetAstra\Client\Service\LogService;
class OAuthService
{
    /**
     * @var GenericProvider
     */
    private $oauthProvider;
    /**
     * @var AccessToken
     */
    private $oauthToken;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CacheInterface
     */
    private $options;
    private $settings;
    private $scope;
    private $container;
    /**
     * @var LogService
     */
    private $logService;
    private const LOGIN_RETRY_LIMIT = 15;
    // Gk disconnects after showing block page 14 times
    public const LOGIN_CREDS = ['wafClientId', 'wafClientPassword', 'oauthClientId', 'oauthClientSecret', 'redirectUri', 'clientApiToken'];
    public const OAUTH_RETRY_LOGIN_KEY = 'oAuthLoginCounter';
    public const OAUTH_LOCK_KEY = 'oAuthLoginFirstRetryAttempt';
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->settings = $container->get('settings');
        $this->logger = $container->get('logger');
        $this->options = $container->get('options');
        $this->logService = $container->get('log2');
        $httpClient = new Client(['headers' => ['User-Agent' => 'GetAstra.com GK ' . (\defined('GATEKEEPER_VERSION') ? GATEKEEPER_VERSION : '[Unknown version]')]]);
        $this->oauthProvider = new GenericProvider([
            //'clientId' => $this->settings['oauth']['clientId'],
            //'clientSecret' => $this->settings['oauth']['clientSecret'],
            //'redirectUri' => $this->settings['oauth']['redirectUri'],
            'urlAuthorize' => $this->settings['oauth']['authorizeEndpoint'],
            'urlAccessToken' => $this->settings['oauth']['tokenEndpoint'],
            'urlResourceOwnerDetails' => null,
        ], ['httpClient' => $httpClient]);
        $this->scope = 'GATEKEEPER';
    }
    public function getTokenObject($disconnect = \true) : ?AccessToken
    {
        $dbToken = $this->options->get('accessToken');
        if ($this->oauthToken) {
            $accessToken = $this->oauthToken;
        } elseif ($dbToken) {
            try {
                $accessToken = new AccessToken($dbToken);
            } catch (InvalidArgumentException $e) {
                $message = 'Serialized accessToken(stored in options) cannot be re-instantiated in getTokenObject(), exception : ' . $e->getMessage();
                $this->logService->setLog($message, "s11n_token_err", $this->logService::GK_DISCONNECT_LOG_KEY);
                $this->logger->warning($message);
            }
        }
        $res = null;
        if (!isset($accessToken)) {
            if (!$this->checkIfAllCredsExist()) {
                $msg = "Token not found and Creds also not found in datastore.";
                if ($this->logService->getLatestLog($this->logService::GK_DISCONNECT_LOG_KEY, 'reason') !== $msg) {
                    $this->logService->setLog($msg, "token_creds_not_found", $this->logService::GK_DISCONNECT_LOG_KEY);
                }
            } else {
                if ($this->checkIfGkCanAttemptRelogin()) {
                    foreach (self::LOGIN_CREDS as $credName) {
                        $loginCreds[$credName] = $this->options->get($credName, null);
                    }
                    $login = $this->login($loginCreds);
                    // re-attempting login
                    if ($login['error'] == \false) {
                        // success
                        $res = $this->oauthToken;
                        // Token not found but Creds found in storage and they work i.e login success
                        $this->resetOauthCounterAndLock();
                        // reset counters
                    } else {
                        // login failed
                        $res = null;
                        $this->logService->setLog("Token not found and Creds found but they don't work.", "stored_creds_err", $this->logService::GK_DISCONNECT_LOG_KEY);
                        $loginCounter = $this->options->get(self::OAUTH_RETRY_LOGIN_KEY, 0);
                        if ($loginCounter >= self::LOGIN_RETRY_LIMIT) {
                            // disconnect if login re-attempt threshold crossed
                            $this->disconnectGk($disconnect);
                            $this->resetOauthCounterAndLock();
                            // reset counters
                        }
                    }
                }
            }
        } else {
            if (!$accessToken->hasExpired()) {
                $this->oauthToken = $accessToken;
                $this->options->set('accessToken', $accessToken->jsonSerialize());
                $res = $accessToken;
                // Token present in datasystem not yet expired i.e still valid
            } else {
                $res = $this->refresh();
                // Token expired therefore refreshing.
            }
        }
        return $res;
    }
    /**
     * Function to disconnect GK.
     * If login limit is exceeded then all creds and deleted and GK is disconnected
     * otherwise login counter option is incremented
     * 
     * @param $disconnect If false the function won't execute, this is called with false value when disconnection is not required
     * but usually its set to true. // this is meant for scanner, since scanner module wont necessarily want to disconnect GK
     * 
     * @param $removeCache If true then it will remove all cache files.
     * 
     * @return void
     */
    private function disconnectGk($disconnect, $removeCache = \false) : void
    {
        if (!$disconnect) {
            return;
        }
        // if Login retry limit exceeded, delete all creds if they are present
        foreach (self::LOGIN_CREDS as $cred) {
            if ($this->options->has($cred)) {
                $this->options->delete($cred);
            }
        }
        $this->options->delete('siteId');
        // Disable Astra
        if ($this->options->has(SiteSettingsService::SITE_OPTIONS_KEY)) {
            $siteSettingsArray = $this->options->get(SiteSettingsService::SITE_OPTIONS_KEY);
            $siteSettingsArray['protectionEnabled'] = \false;
            $this->options->set(SiteSettingsService::SITE_OPTIONS_KEY, $siteSettingsArray);
        }
        $this->logService->setLog("Disconnected GK, login retry limit exceeded", "retry_limit_crossed", $this->logService::GK_DISCONNECT_LOG_KEY);
        if ($removeCache) {
            $clearCacheFile = ASTRAROOT . 'scripts/removeCache.php';
            if (\file_exists($clearCacheFile)) {
                include_once $clearCacheFile;
            }
            if (\function_exists('AstraPrefixed\\clearAllCache') && $flag) {
                clearAllCache();
            }
        }
        return;
    }
    /**
     * Function to check if all credentials required for GK login are present in datasystem or not.
     * 
     * @return bool true if all of the creds are present, false otherwise
     */
    private function checkIfAllCredsExist() : bool
    {
        foreach (self::LOGIN_CREDS as $cred) {
            if (!$this->options->has($cred)) {
                return \false;
            }
        }
        return \true;
    }
    public function login($username, $checkBaseUrl = \false)
    {
        if (!isset($username['wafClientId']) || !isset($username['wafClientPassword']) || !isset($username['oauthClientId']) || !isset($username['oauthClientSecret']) || !isset($username['redirectUri'])) {
            return ['error' => \true, 'errorMsg' => 'Invalid credentials'];
        }
        if ($checkBaseUrl && isset($_SERVER['SERVER_NAME'])) {
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'];
            if (\strpos($username['redirectUri'], $baseUrl) === \false) {
                // === strict check necessary
                return ['error' => \true, 'errorMsg' => "Invalid activation code entered. The code was generated for {$username['redirectUri']} but you are trying to activate it for {$baseUrl}"];
            }
        }
        try {
            $accessToken = $this->oauthProvider->getAccessToken('password', ['scope' => $this->scope, 'username' => $username['wafClientId'], 'password' => $username['wafClientPassword'], 'client_id' => $username['oauthClientId'], 'client_secret' => $username['oauthClientSecret'], 'redirect_uri' => $username['redirectUri']]);
            $this->logger->notice('OAuth Login Successful');
            $this->oauthToken = $accessToken;
            $this->options->set('accessToken', $accessToken->jsonSerialize());
            return ['error' => \false, 'errorMsg' => "Success"];
        } catch (\Throwable $e) {
            $this->logger->critical('OAuth Login Exception' . $e->getMessage());
            return ['error' => \true, 'errorMsg' => "Looks like the activation code is not valid. Please try copy-pasting the code from the Dashboard again, or contact support if this error persists."];
        }
    }
    protected function refresh() : ?AccessToken
    {
        $dbToken = $this->options->get('accessToken');
        if ($this->oauthToken) {
            $accessToken = $this->oauthToken;
        } elseif ($dbToken) {
            try {
                $accessToken = new AccessToken($dbToken);
            } catch (InvalidArgumentException $e) {
                $message = 'Serialized accessToken(stored in options) cannot be re-instantiated in refresh(), exception : ' . $e->getMessage();
                $this->logService->setLog($message, "s11n_token_err", $this->logService::GK_DISCONNECT_LOG_KEY);
                $this->logger->warning($message);
            }
        }
        try {
            if (!$accessToken) {
                $this->logger->warning('refreshToken called with no token in db or memory');
                throw new LogicException('refreshToken called with no token in db or memory');
            }
            $refreshToken = $accessToken->getRefreshToken();
            $newAccessToken = $this->oauthProvider->getAccessToken('refresh_token', ['scope' => $this->scope, 'refresh_token' => $refreshToken, 'client_id' => $this->options->get('oauthClientId'), 'client_secret' => $this->options->get('oauthClientSecret')]);
            $this->oauthToken = $newAccessToken;
            $this->options->set('accessToken', $newAccessToken->jsonSerialize());
            $this->logger->info('Token refreshed');
            return $newAccessToken;
        } catch (InvalidArgumentException $e) {
            $message = 'Token could not be refreshed, exception : ' . $e->getMessage();
            $this->logService->setLog($message, "token_refresh_err", $this->logService::GK_DISCONNECT_LOG_KEY);
            $this->logger->warning($message);
            return null;
        } catch (LogicException $e) {
            $this->logger->warning($e->getMessage());
            return null;
        } catch (IdentityProviderException $e) {
            $message = 'Token could not be refreshed, exception : ' . $e->getMessage();
            $this->logService->setLog($message, "token_refresh_err", $this->logService::GK_DISCONNECT_LOG_KEY);
            $this->logger->warning($e->getMessage());
            return null;
        }
    }
    public function isLoggedIn($hardCheck = \false) : bool
    {
        if (!$this->oauthToken) {
            return \false;
        }
        if ($this->oauthToken->hasExpired()) {
            return \false;
        }
        if (!$hardCheck) {
            return \true;
        }
        $hardCheckRateLimit = $this->options->get('loginCheckRateLimit', null);
        if (\is_null($hardCheckRateLimit)) {
            $this->options->set('loginCheckRateLimit', \true, 300);
        } else {
            return \true;
        }
        $httpCode = [404, 403];
        if (\is_null($this->container)) {
            return \false;
        }
        $oauthClientId = $this->container->get('options')->get('oauthClientId');
        $oauthClientSecret = $this->container->get('options')->get('oauthClientSecret');
        $tokenObject = $this->oauthToken;
        $apiUrl = \substr($this->container->get('settings')['relay']['api_url_https'], 0, -1);
        if (empty($oauthClientId) || empty($oauthClientSecret) || empty($tokenObject) || empty($apiUrl)) {
            return \false;
        }
        $siteId = $this->options->get('siteId');
        $apiConfiguration = (new Configuration())->setAccessToken($tokenObject->getToken());
        $apiConfiguration->setHost($apiUrl)->setDebug(\false)->setUsername($oauthClientId)->setPassword($oauthClientSecret);
        $siteApi = new SiteApi(null, $apiConfiguration);
        try {
            $siteSettings = $siteApi->getSiteSettingsSiteItem($siteId);
        } catch (\Throwable $e) {
            //if(in_array($e->getCode(),$httpCode)){
            return \false;
            //}
        }
        return \true;
    }
    private function checkIfGkCanAttemptRelogin()
    {
        $threshold = $this::LOGIN_RETRY_LIMIT;
        $loginCounter = $this->options->get(self::OAUTH_RETRY_LOGIN_KEY, 0);
        if ($loginCounter > $threshold) {
            return \false;
        }
        if (!empty($this->options->get(self::OAUTH_LOCK_KEY, null))) {
            return \false;
        }
        if ($loginCounter < 5) {
            $intervalObj = new \DateInterval("PT5M");
            $this->options->set(self::OAUTH_LOCK_KEY, \true, $intervalObj);
            $this->options->set(self::OAUTH_RETRY_LOGIN_KEY, ++$loginCounter);
            return \true;
        } else {
            $intervalObj = new \DateInterval("PT1H");
            $this->options->set(self::OAUTH_LOCK_KEY, \true, $intervalObj);
            $this->options->set(self::OAUTH_RETRY_LOGIN_KEY, ++$loginCounter);
            return \true;
        }
        return \false;
    }
    public function resetOauthCounterAndLock()
    {
        $this->options->set(self::OAUTH_LOCK_KEY, null);
        $this->options->set(self::OAUTH_RETRY_LOGIN_KEY, 0);
    }
    //    public function login(string $username, string $password): bool
    //    {
    //        try {
    //            $this->oauthProvider->authorize([
    //                'response_type' => 'code',
    //                'scope' => 'GATEKEEPER',
    //                //'client_id' => $username,
    //                //'redirect_uri' => $this->settings['oauth']['redirectUri'],
    //            ]);
    //
    //            return true;
    //        } catch (IdentityProviderException $e) {
    //            $this->logger->error($e->getMessage());
    //
    //            return false;
    //        }
    //    }
    //    public function stepTwoService($code)
    //    {
    //        try {
    //            $accessToken = $this->oauthProvider->getAccessToken('authorization_code', [
    //                'grant_type' => 'authorization_code',
    //                'code' => $code,
    //                //'client_id' => $this->settings['oauth']['clientId'],
    //                //'client_secret' => $this->settings['oauth']['clientSecret'],
    //                //'redirect_uri' => $this->settings['oauth']['redirectUri'],
    //            ]);
    //
    //            $this->logger->debug('OAuth Login Successful');
    //            $this->oauthToken = $accessToken;
    //            $this->options->set('accessToken', $accessToken->jsonSerialize());
    //            $this->options->set('oauthUsername', $this->settings['oauth']['clientId']);
    //            $this->options->set('oauthPassword', $this->settings['oauth']['clientSecret']);
    //
    //            return true;
    //        } catch (IdentityProviderException $e) {
    //            $this->logger->error($e->getMessage());
    //
    //            return false;
    //        }
    //    }
}
