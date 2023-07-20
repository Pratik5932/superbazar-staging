<?php

namespace AstraPrefixed;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use AstraPrefixed\GetAstra\Client\Helper\BrowserAstraHelper;
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\GetAstra\Client\Helper\IpBlockingHelper;
use AstraPrefixed\GetAstra\Client\Helper\UrlHelper;
use AstraPrefixed\GetAstra\Client\Helper\PluginInterface;
use AstraPrefixed\GetAstra\Client\Tclient\ApiException;
use AstraPrefixed\GetAstra\Client\Tclient\Configuration;
use AstraPrefixed\GetAstra\Client\Tclient\Login\LoginApi;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
/**
 * Description of LoginProtectionPlugin.
 *
 * @author aditya
 */
class LoginProtectionPlugin implements PluginInterface
{
    private $container;
    private $urlHelper;
    private $ipBlockingHelper;
    private $commonHelper;
    private $browser;
    /**
     * @var LoggerInterface
     */
    private $logger;
    public function getMigrationDirPath() : string
    {
        return '';
    }
    public function getName() : string
    {
        return 'LoginProtectionPlugin';
    }
    public function getRoutes() : array
    {
        return '';
    }
    public function getVersion() : string
    {
        return 'v0.1';
    }
    public function isApiUser() : bool
    {
        return \true;
    }
    public function isRequestBlocker() : bool
    {
        return \true;
    }
    public function isMiddlewareBasedPlugin() : bool
    {
        return \false;
    }
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->urlHelper = new UrlHelper();
        $this->commonHelper = new CommonHelper();
        $this->ipBlockingHelper = new IpBlockingHelper($this->container);
        $this->browser = new BrowserAstraHelper();
        $this->logger = $this->container->get('logger');
    }
    /**
     * Prepares login data to be saved in Symfony.
     */
    public function login($data)
    {
        $siteId = $this->container->get('options')->get('siteId');
        $iri = 'api/waf/sites/' . $siteId;
        $ip = $this->urlHelper->getClientIp();
        if (!$ip) {
            $this->logger->warning('Client IP address not found in LoginProtection Module, exiting.');
            exit;
        }
        $countryObj = $this->ipBlockingHelper->getIpInformation($ip);
        if ($countryObj) {
            $countryIso = $countryObj->raw['country']['iso_code'];
        } else {
            $countryIso = '';
        }
        $block = \false;
        //initial
        if (!$data['success']) {
            //if failed login - decide blocked column value based on thresholds
            $block = $this->ipBlockingHelper->recordFailedLogin($ip, $this->getName());
        }
        $loginUrl = (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $loginData = ['site' => $iri, 'ipAddress' => $ip, 'blocked' => $block, 'country' => $countryIso, 'loginUrl' => \htmlspecialchars($loginUrl, \ENT_QUOTES, 'UTF-8'), 'username' => $data['username'], 'userAgent' => $this->browser->getUserAgent(), 'device' => $this->browser->getBrowser(), 'os' => $this->browser->getPlatform(), 'loggedAt' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('c')];
        if ($data['success']) {
            $loginData['success'] = \true;
            $loginData['email'] = $data['email'];
            $loginData['displayName'] = $data['displayName'];
        } else {
            $loginData['success'] = \false;
        }
        $this->saveLoginInSymfony($loginData);
    }
    private function saveLoginInSymfony($loginData)
    {
        $oauthService = $this->container->get('oauth');
        $tokenObject = $oauthService->getTokenObject();
        $options = $this->container->get('options');
        $oauthClientId = $this->container->get('options')->get('oauthClientId');
        $oauthClientSecret = $this->container->get('options')->get('oauthClientSecret');
        $apiUrl = \substr($this->container->get('settings')['relay']['api_url_https'], 0, -1);
        if (isset($tokenObject, $oauthClientId, $oauthClientSecret, $apiUrl)) {
            $apiConfiguration = (new Configuration())->setAccessToken($tokenObject->getToken());
            $apiConfiguration->setHost($apiUrl)->setDebug(\false)->setUsername($oauthClientId)->setPassword($oauthClientSecret);
            $loginApi = new LoginApi(null, $apiConfiguration);
        } else {
            $loginApi = null;
        }
        if ($loginApi) {
            try {
                $loginApi->postLoginCollection($loginData);
            } catch (\Exception $e) {
                if ($e instanceof ApiException && $e->getCode() == '401') {
                    $options->delete('accessToken');
                }
                return \false;
            }
            return \true;
        } else {
            return \false;
        }
    }
}
/**
 * Description of LoginProtectionPlugin.
 *
 * @author aditya
 */
\class_alias('AstraPrefixed\\LoginProtectionPlugin', 'LoginProtectionPlugin', \false);
