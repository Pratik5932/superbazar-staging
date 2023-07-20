<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Service;

use AstraPrefixed\GetAstra\Client\Helper\UrlHelper;
use AstraPrefixed\GetAstra\Client\Tclient\Booster\BoosterApi;
use AstraPrefixed\GetAstra\Client\Tclient\Configuration;
use AstraPrefixed\GetAstra\Client\Tclient\Exception\ExceptionApi;
use AstraPrefixed\GetAstra\Client\Tclient\IpRule\IpRuleApi;
use AstraPrefixed\GetAstra\Client\Tclient\SiteApi;
use AstraPrefixed\GetAstra\Client\Tclient\WafRule\WafRuleApi;
use AstraPrefixed\GuzzleHttp\Client;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use Throwable;
use AstraPrefixed\GetAstra\Client\Helper\Cms\AbstractCmsHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Services\ScanEngine;
use AstraPrefixed\GetAstra\Client\Helper\IpBlockingHelper;
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\Slim\Http\StatusCode;
use AstraPrefixed\GuzzleHttp\Exception\ClientException;
use AstraPrefixed\GuzzleHttp\Exception\TransferException;
use AstraPrefixed\GetAstra\Client\Service\LogService;
class SiteSettingsService
{
    /**
     * @var CacheInterface
     */
    private $options;
    /**
     * @var SiteApi
     */
    private $siteApi;
    /**
     * @var WafRuleApi
     */
    private $wafRuleApi;
    /**
     * @var IpRuleApi
     */
    private $ipRuleApi;
    /**
     * @var ExceptionApi
     */
    private $exceptionApi;
    /**
     * @var BoosterApi
     */
    private $boosterApi;
    /**
     * @var OAuthService
     */
    private $oauthService;
    /**
     * @var LoggerInterface
     */
    private $logger;
    private $siteId;
    private $apiUrl;
    private $container;
    /**
     * @var LogService
     */
    private $logService;
    public const CACHE_EXPIRY_KEY = 'cacheExpiry';
    public const SITE_OPTIONS_KEY = 'siteSettings';
    public const WAF_RULES_KEY = 'wafRules';
    public const IP_RULES_KEY = 'ipRules';
    public const EXCEPTIONS_KEY = 'exceptions';
    public const BOOSTER_RULES_KEY = 'boosters';
    public const FULL_SITE_OBJECT_KEY = 'fullSiteObject';
    public const OPTIONS_CACHE_EXPIRY = '+1 day';
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->options = $container->get('options');
        $this->oauthService = $container->get('oauth');
        $this->logger = $container->get('logger');
        $this->logService = $container->get('log2');
        $this->apiUrl = \substr($container->get('settings')['relay']['api_url_https'], 0, -1);
    }
    private function initializeApiClients()
    {
        $tokenObject = $this->oauthService->getTokenObject();
        $oauthClientId = $this->container->get('options')->get('oauthClientId');
        $oauthClientSecret = $this->container->get('options')->get('oauthClientSecret');
        if (isset($tokenObject, $oauthClientId, $oauthClientSecret, $this->apiUrl)) {
            $apiConfiguration = (new Configuration())->setAccessToken($tokenObject->getToken());
            $apiConfiguration->setHost($this->apiUrl)->setDebug(\false)->setUsername($oauthClientId)->setPassword($oauthClientSecret);
            $this->siteApi = new SiteApi(null, $apiConfiguration);
            $this->wafRuleApi = new WafRuleApi(null, $apiConfiguration);
            $this->ipRuleApi = new IpRuleApi(null, $apiConfiguration);
            $this->exceptionApi = new ExceptionApi(null, $apiConfiguration);
            $this->boosterApi = new BoosterApi(null, $apiConfiguration);
            return \true;
        } else {
            $this->siteApi = null;
            $this->wafRuleApi = null;
            $this->ipRuleApi = null;
            $this->exceptionApi = null;
            $this->boosterApi = null;
            //$this->logger->warning('Cannot initialize API clients token not found.');
            return \false;
        }
    }
    /**
     * @param bool $firstLogin if its the first login on GK
     * @param bool $updateCacheExpiryTime pass true if GK needs to set a new cache expiry time, false if no change required
     */
    public function saveSiteSettingsLocally($firstLogin = \false, $updateCacheExpiryTime = \true)
    {
        $this->siteId = $this->options->get('siteId');
        if (!$this->siteApi || !$this->wafRuleApi || !$this->ipRuleApi || !$this->exceptionApi || !$this->boosterApi) {
            if (!$this->initializeApiClients()) {
                $this->logger->warning('Cannot update options due to unavailablity of APIs.');
                return ['error' => \true, 'errorMessage' => 'Cannot update options due to unavailablity of APIs, api could not be initialized because login token not found'];
            }
        }
        try {
            $siteSettings = $this->siteApi->getSiteSettingsSiteItem($this->siteId);
            $a = \json_decode($siteSettings, \true);
            $this->options->set($this::SITE_OPTIONS_KEY, $a);
        } catch (\AstraPrefixed\GetAstra\Client\Tclient\ApiException $e) {
            if (404 === $e->getCode()) {
                //site Not found
                $this->options->delete('siteId');
                //this is necessary to remove the incorrect siteId from options.
                $this->logService->setLog($e->getMessage(), 'site_not_found_err', $this->logService::GK_LOGIN_LOG_KEY);
                return ['error' => \true, 'errorMessage' => 'You site ID not found on Astra server. Please contact support.'];
            } else {
                throw $e;
            }
        } catch (\Throwable $e) {
            $this->logService->setLog($e->getMessage(), 'site_not_found_err', $this->logService::GK_LOGIN_LOG_KEY);
            return ['error' => \true, 'errorMessage' => $e->getMessage()];
        }
        try {
            //separate try catch blocks are required because a common try catch doesn't work.
            $boosterRules = $this->boosterApi->getBoosterCollection(null, $this->siteId);
            $br = \json_decode($boosterRules, \true);
            //hydra:member key has the collection resource, because this API client returns in JSON ld response.
            $this->options->set($this::BOOSTER_RULES_KEY, $br['hydra:member']);
        } catch (\AstraPrefixed\GetAstra\Api\Client\ApiException $e) {
            $msg = 'Site get boosters call failed - ' . $e->getMessage();
            $this->logService->setLog($msg, 'site_boosters_get_err', $this->logService::GK_LOGIN_LOG_KEY);
        } catch (\Throwable $e) {
            $this->logService->setLog($e->getMessage(), 'site_boosters_get_err', $this->logService::GK_LOGIN_LOG_KEY);
        }
        if ($updateCacheExpiryTime) {
            $this->options->set($this::CACHE_EXPIRY_KEY, (new \DateTime($this::OPTIONS_CACHE_EXPIRY, new \DateTimeZone('UTC')))->format('c'));
        }
        try {
            $wafRules = $this->wafRuleApi->getWafRuleCollection();
            $this->options->set($this::WAF_RULES_KEY, $wafRules['hydra:member']);
        } catch (\AstraPrefixed\GetAstra\Client\Tclient\ApiException $e) {
            $msg = 'Site get wafRules call failed - ' . $e->getMessage();
            $this->logService->setLog($msg, 'site_waf-rules_get_err', $this->logService::GK_LOGIN_LOG_KEY);
        } catch (\Throwable $e) {
            $this->logService->setLog($e->getMessage(), 'site_waf-rules_get_err', $this->logService::GK_LOGIN_LOG_KEY);
        }
        try {
            $iprules = $this->ipRuleApi->getIpRuleCollection($this->siteId);
            $this->options->set($this::IP_RULES_KEY, $iprules['hydra:member']);
        } catch (\AstraPrefixed\GetAstra\Client\Tclient\ApiException $e) {
            $msg = 'Site get ipRules call failed - ' . $e->getMessage();
            $this->logService->setLog($msg, 'site_ip-rules_get_err', $this->logService::GK_LOGIN_LOG_KEY);
        } catch (\Throwable $e) {
            $this->logService->setLog($e->getMessage(), 'site_ip-rules_get_err', $this->logService::GK_LOGIN_LOG_KEY);
        }
        try {
            $exceptions = $this->exceptionApi->getExceptionCollection($this->siteId);
            $b = \json_decode($exceptions, \true);
            $final = [];
            foreach ($b['hydra:member'] as $key => $val) {
                if (\false !== @\preg_match('/^' . $val['parameter'] . '$/', '')) {
                    //strict === type check necessary, only if preg match is false then reject. preg_match result===0 is OK
                    $final[] = $val;
                }
            }
            $this->options->set($this::EXCEPTIONS_KEY, $final);
        } catch (\AstraPrefixed\GetAstra\Client\Tclient\ApiException $e) {
            $msg = 'Site get exception call failed - ' . $e->getMessage();
            $this->logService->setLog($msg, 'site_exception_get_err', $this->logService::GK_LOGIN_LOG_KEY);
        } catch (\Throwable $e) {
            $this->logService->setLog($e->getMessage(), 'site_exception_get_err', $this->logService::GK_LOGIN_LOG_KEY);
        }
        //store entire site Object
        //likewise we can store other required values in any schema.
        try {
            $site = $this->siteApi->getSiteItem($this->siteId);
            $siteDecoded = \json_decode($site, \true);
        } catch (\AstraPrefixed\GetAstra\Client\Tclient\ApiException $e) {
            $msg = 'Site get call failed - ' . $e->getMessage();
            $this->logService->setLog($msg, 'site_get_err', $this->logService::GK_LOGIN_LOG_KEY);
        } catch (\Throwable $e) {
            $this->logService->setLog($e->getMessage(), 'site_get_err', $this->logService::GK_LOGIN_LOG_KEY);
        }
        $cmsAdapter = new AbstractCmsHelper();
        $cms = $cmsAdapter->getCms();
        $cmsName = $cms->getName();
        $cmsVersion = $cms->getVersion();
        if (\in_array($cmsName, ['magento', 'drupal', 'opencart'])) {
            $cmsName .= $cmsVersion[0];
            //eg - opencart3, drupal2, magento1
        } else {
            if ($cmsName == 'prestashop') {
                $cmsName .= \substr($cmsVersion, 0, 3);
                //eg - prestashop1.6
            } elseif (\in_array($cmsName, ['wordpress', 'joomla', 'moodle'])) {
                $cmsName = $cmsName;
                //keep the name same
            } else {
                $cmsName = 'php';
                $cmsVersion = null;
            }
        }
        if (empty($cmsName)) {
            // final check
            $cmsName = 'php';
        }
        if ($cmsVersion !== null && empty($cmsVersion)) {
            $cmsVersion = null;
        }
        $dataForPatch = ['gkSync' => \true, 'workerVersion' => GATEKEEPER_VERSION, 'connected' => \true, 'phpVersion' => \PHP_VERSION, 'gkSyncErrorReason' => 'Astra Connected', 'lastSyncedAt' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('c'), 'cms' => $cmsName, 'version' => $cmsVersion];
        $calculateApiUrl = UrlHelper::getCurrentUri(null, \true, \true);
        if ($firstLogin) {
            // on first login we save whatever URL we get
            $dataForPatch['apiUrl'] = $calculateApiUrl;
            $this->options->set('apiUrl', $calculateApiUrl);
        } else {
            // on subsequent cache refreshes, we check if URL is valid or not
            $existingApiUrl = $this->options->get('apiUrl', null);
            if ($existingApiUrl && $this->checkApiUrl($existingApiUrl)) {
                // do not update on symfony since existing apiUrl is working
            } else {
                if ($this->checkApiUrl()) {
                    // if its not working then check the new detected url & patch if it works
                    $dataForPatch['apiUrl'] = $calculateApiUrl;
                    $this->options->set('apiUrl', $calculateApiUrl);
                }
            }
        }
        //update site Object for GK, with the new values that have been detected by GK
        $this->options->set($this::FULL_SITE_OBJECT_KEY, \array_merge($siteDecoded, $dataForPatch));
        try {
            //send the new site object to symfony now.
            $this->siteApi->patchSiteItem($this->siteId, \json_encode($dataForPatch));
        } catch (\AstraPrefixed\GetAstra\Client\Tclient\ApiException $e) {
            $msg = 'Site patch call failed - ' . $e->getMessage();
            $this->logService->setLog($msg, 'site_patch_err', $this->logService::GK_LOGIN_LOG_KEY);
        } catch (\Throwable $e) {
            $this->logService->setLog($e->getMessage(), 'site_state_patch_err', $this->logService::GK_LOGIN_LOG_KEY);
        }
        try {
            //another request to site, for updating status
            $this->updateSiteStatusApiCall($siteDecoded);
        } catch (\AstraPrefixed\GetAstra\Client\Tclient\ApiException $e) {
            $msg = 'Site state patch call failed - ' . $e->getMessage();
            $this->logService->setLog($msg, 'site_state_patch_err', $this->logService::GK_LOGIN_LOG_KEY);
        } catch (\Throwable $e) {
            $this->logService->setLog($e->getMessage(), 'site_state_patch_err', $this->logService::GK_LOGIN_LOG_KEY);
        }
        ScanEngine::requestKill();
        // stopping existing running scan
        if ($firstLogin) {
            // reseting login counters
            $this->oauthService->resetOauthCounterAndLock();
        }
        $this->downloadMaxMindDb();
        // download maxmind db
        return ['error' => \false];
    }
    /**
     * API call to update Site entity `status` property to connected on successful GK login.
     * 
     * this request has been made manually here because there was no matching request in the open API generated client.
     */
    private function updateSiteStatusApiCall(array $site) : void
    {
        if (empty($site) || \count($site) <= 0) {
            return;
        }
        if ('connected' == $site['state']) {
            return;
        } else {
            if ('disconnected' == $site['state']) {
                $jsonBody = ['state' => 'reconnect'];
            } else {
                $jsonBody = ['state' => 'connect'];
            }
        }
        $url = "{$this->apiUrl}/api/waf/sites/{$this->siteId}/state";
        $config = [
            'verify' => \false,
            //ssl verification disabled
            'json' => $jsonBody,
        ];
        $headers = ['request-target' => "patch {$url}", 'date' => \date('Y-m-d H:i:s'), 'content-type' => 'application/json', 'Authorization' => "Bearer {$this->oauthService->getTokenObject()->getToken()}", 'User-Agent' => 'GetAstra.com GK ' . (\defined('GATEKEEPER_VERSION') ? GATEKEEPER_VERSION : '[Unknown version]')];
        $client = new Client($config);
        $guzzleRequest = new \AstraPrefixed\GuzzleHttp\Psr7\Request('PATCH', $url, $headers);
        $client->send($guzzleRequest);
    }
    private function downloadMaxMindDb()
    {
        $tokenObject = $this->container->get('oauth')->getTokenObject();
        if (\is_null($tokenObject)) {
            return \false;
        }
        $token = $tokenObject->getToken();
        $apiUrl = \substr($this->container->get('settings')['relay']['api_url_https'], 0, -1);
        $siteId = $this->container->get('options')->get('siteId');
        $logger = $this->container->get('logger');
        if (!isset($token, $apiUrl, $siteId)) {
            $logger->critical('Cannot download GeoIpDatabase');
            return \false;
        }
        $url = "{$apiUrl}/api/waf/sites/{$siteId}/getGeoIpDb";
        $config = ['verify' => \false];
        $headers = ['request-target' => "get {$url}", 'date' => \date('Y-m-d H:i:s'), 'Authorization' => "Bearer " . $token, 'User-Agent' => 'GetAstra.com GK ' . (\defined('GATEKEEPER_VERSION') ? GATEKEEPER_VERSION : '[Unknown version]')];
        $client = new Client($config);
        $guzzleRequest = new \AstraPrefixed\GuzzleHttp\Psr7\Request('GET', $url, $headers);
        try {
            $response = $client->send($guzzleRequest);
            $resource = $response->getBody()->getContents();
            $tmpfile = \tmpfile();
            \fwrite($tmpfile, $resource);
            if (\mime_content_type($tmpfile) !== "application/octet-stream") {
                \fclose($tmpfile);
                throw new \Exception('file mime type error');
            }
        } catch (\Exception $e) {
            return \false;
        }
        \file_put_contents(IpBlockingHelper::GEOIP_DB_PATH, $resource);
        \fclose($tmpfile);
        return \true;
    }
    /**
     * Checks API URL on cache refresh
     * @param string $urlToCheck  if null then will use url=UrlHelper::getcurrentUri
     * @return bool true if URL detected is valid and should be updated to symfony, false otherwise
     */
    private function checkApiUrl($urlToCheck = null) : bool
    {
        // lock check, to prevent frequent forks
        if ($this->options->get('apiUrlCheck')) {
            return \false;
        } else {
            $this->options->set('apiUrlCheck', \true);
        }
        // check if GK is able to ping itself with the detected URI
        if (!$urlToCheck) {
            if (\filter_var($_SERVER['SERVER_NAME'], \FILTER_VALIDATE_IP) !== \false && \filter_var($_SERVER['SERVER_NAME'], \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE) === \false) {
                $this->options->set('apiUrlCheck', \false);
                // remove lock
                return \false;
            }
            $uri = UrlHelper::getCurrentUri(null, \true, \true);
            if (!$uri) {
                $this->options->set('apiUrlCheck', \false);
                // remove lock
                return \false;
            }
        } else {
            $uri = $urlToCheck;
        }
        $url = $uri . '?astraRoute=api';
        $headers = ['request-target' => "get {$url}", 'date' => \date('Y-m-d H:i:s'), 'content-type' => 'application/json', 'User-Agent' => 'GetAstra.com GK ' . (\defined('GATEKEEPER_VERSION') ? GATEKEEPER_VERSION : '[Unknown version]')];
        $client = new Client([
            'verify' => \false,
            //ssl verification disabled
            'timeout' => 10,
        ]);
        $guzzleRequest = new \AstraPrefixed\GuzzleHttp\Psr7\Request('GET', $url, $headers);
        try {
            $response = $client->send($guzzleRequest);
        } catch (ClientException $e) {
            // only if an 400 HTTP exception is thrown we can verify that apiUrl detected works fine
            $errorResponse = $e->getResponse();
            $contents = \json_decode($errorResponse->getBody()->getContents(), \true);
            if (!$contents) {
                $this->options->set('apiUrlCheck', \false);
                // remove lock
                return \false;
            }
            if ($errorResponse->getStatusCode() === StatusCode::HTTP_BAD_REQUEST && isset($contents['error']) && $contents['error'] == 'Unauthorized') {
                $this->options->set('apiUrlCheck', \false);
                // remove lock
                return \true;
            }
        } catch (\Throwable $e) {
            // if timeout reached, response body will not be there only status code 400
            if ($response && $response->getStatusCode() === StatusCode::HTTP_BAD_REQUEST) {
                $this->options->set('apiUrlCheck', \false);
                // remove lock
                return \true;
            }
        }
        $this->options->set('apiUrlCheck', \false);
        // remove lock
        return \false;
    }
}
