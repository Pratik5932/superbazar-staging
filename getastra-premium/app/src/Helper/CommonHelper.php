<?php

/**
 * This file is part of the Astra Security Suite.
 *
 *  Copyright (c) 2019 (https://www.getastra.com/)
 *
 *  For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
/**
 * @author HumansofAstra-WZ <help@getastra.com>
 * @date   2019-03-14
 */
namespace AstraPrefixed\GetAstra\Client\Helper;

use AstraPrefixed\HttpSignatures\KeyStore;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Http\Message\ServerRequestInterface;
use AstraPrefixed\IPLib\Factory;
class CommonHelper
{
    private $urlHelper;
    /**
     * @todo central place to store API URLs ??
     */
    private const THREAT_POST_URL = 'api/waf/sites/';
    public function __construct()
    {
        $this->urlHelper = new UrlHelper();
    }
    public static function customGetEnv($key)
    {
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        } else {
            if (\getenv($key) != \false) {
                return \getenv($key);
            } else {
                return null;
            }
        }
    }
    public static function bigRandomHex()
    {
        $bytes = \openssl_random_pseudo_bytes(16);
        return \bin2hex($bytes);
    }
    /**
     * Prepare threat data to be posted to Symfony according to the Request object.
     * Attack Parameter and Vector not set by this function, and instead is set by the module itself
     * i.e these values are set from where this function is called.
     *
     * @param \GetAstra\Client\Helper\ServerRequestInterface $request
     * @param string                                         $siteId
     * @param string                                         $blockingStatus
     *
     * @return array $finalThreat
     */
    public function threatPostPrepare(ServerRequestInterface $request, $siteId, ContainerInterface $container, $blockingStatus = 'monitored')
    {
        $iri = $this::THREAT_POST_URL . $siteId;
        //this IP is not checked for falsy values, since that check is performed directly in calling module
        //of this function (threatPostPrepare).
        //if found to be falsy the module is exited before this function gets called.
        $ipAddress = $this->urlHelper->getClientIp();
        $attackedUrl = $this->urlHelper->getCurrentUri(null, \true, \true);
        $countryIso = '';
        //@todo check if country detection is enabled, for now hardcoded true
        $countryCheckingEnabled = \true;
        if ($countryCheckingEnabled) {
            $ipBlockingHelper = new IpBlockingHelper($container);
            $countryObj = $ipBlockingHelper->getIpInformation($ipAddress);
            if ($countryObj) {
                //$countryIso = $countryObj->country->isoCode; //geoIp bundle broken, doesnt work anymore
                $countryIso = $countryObj->raw['country']['iso_code'];
            }
        }
        $browserHelper = new BrowserAstraHelper();
        $finalThreat = ['site' => $iri, 'ipAddress' => $ipAddress, 'useragent' => $browserHelper->getUserAgent(), 'country' => $countryIso, 'device' => $browserHelper->getBrowser(), 'os' => $browserHelper->getPlatform(), 'attackedUrl' => $attackedUrl, 'blockingStatus' => $blockingStatus, 'rawHttpRequest' => $this->getRawHttpRequestData(), 'loggedAt' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('c')];
        return $finalThreat;
    }
    /**
     * Checks if the options necessary for Middle-ware modules to work are populated or not.
     *
     * @return bool false if options are not present M/W cant be used true otherwise
     */
    public function checkIfOptionsPopulated($options)
    {
        $siteSettings = $options->get('siteSettings');
        $wafRules = $options->get('wafRules');
        if (!$wafRules) {
            return \false;
        }
        if (!$siteSettings) {
            return \false;
        }
        if (!isset($siteSettings['protectionEnabled'])) {
            //required by ChecksMiddleware
            return \false;
        }
        //required by IpBlockingHelper / IpRule Core plugin/Middlewware
        if (!isset($siteSettings['waf']['defaultThresholds'])) {
            return \false;
        }
        if (!isset($siteSettings['waf']['customThresholds'])) {
            return \false;
        }
        if (!isset($siteSettings['waf']['verifySearchCrawlers'])) {
            //required by BotPlugin
            return \false;
        }
        return \true;
    }
    /**
     * Full HTTP Request data as string.
     * Link to script - https://gist.github.com/magnetikonline/650e30e485c0f91f2f40.
     *
     * @return string
     */
    public function getRawHttpRequestData()
    {
        $data = ['requestMethod' => $_SERVER['REQUEST_METHOD'], 'requestUri' => $_SERVER['REQUEST_URI'], 'serverProtocol' => $_SERVER['SERVER_PROTOCOL']];
        foreach ($_SERVER as $name => $value) {
            if (\preg_match('/^HTTP_/', $name)) {
                // convert HTTP_HEADER_NAME to Header-Name
                $name = \strtr(\substr($name, 5), '_', ' ');
                $name = \ucwords(\strtolower($name));
                $name = \strtr($name, ' ', '-');
                // add to list
                $data['httpHeaders'][$name] = $value;
            }
        }
        $data['requestBody'] = $_REQUEST;
        return \json_encode($data);
    }
    /**
     * Retrieves the public key path from the container, then verifies the $request object.
     * Also checks for Client API token before verifying the request object.
     *
     * @return array first key in the array will have a boolean = True if request is successfully verified, false otherwise, 
     * the second key will specify the error message if any
     */
    public function verifySignedHttpRequest(ServerRequestInterface $request, ContainerInterface $container)
    {
        //Check Whitelisted routes
        $route = $request->getAttribute('route');
        if (!empty($route)) {
            $ignoredRouteNames = ['plugins.scanner.testAjax', 'plugins.scanner.scans.perform', 'auth.login', 'auth.token'];
            if (\in_array($route->getName(), $ignoredRouteNames)) {
                return ['requestVerified' => \true];
            }
        }
        $isSigningEnabled = \filter_var($container->get('settings')['app']['isRequestSigningEnabled'], \FILTER_VALIDATE_BOOLEAN);
        if (!$isSigningEnabled) {
            return ['requestVerified' => \true];
        }
        $publicKeys = $container->get('settings')['app']['publicKeyPath'];
        $c = $publicKeys;
        foreach ($c as $keyId => $keyFile) {
            if (\file_exists($keyFile)) {
                continue;
            } else {
                unset($publicKeys[$keyId]);
            }
        }
        if (\count($publicKeys) <= 0) {
            return ['requestVerified' => \false, 'errorMessage' => 'Key/KeyId not found'];
        }
        $keyStoreArray = [];
        foreach ($publicKeys as $keyId => $file) {
            $keyStoreArray[$keyId] = \file_get_contents($file);
        }
        $keyStore = new KeyStore($keyStoreArray);
        $verifier = new \AstraPrefixed\HttpSignatures\Verifier($keyStore);
        try {
            $requestVerified = $verifier->isSigned($request);
        } catch (\Throwable $e) {
            $requestVerified = \false;
        }
        $errorMessage = 'Unauthorized';
        if (\defined('ASTRA_DEBUG_MODE') && \true == ASTRA_DEBUG_MODE) {
            $errorMessage = $verifier->getStatus();
        }
        return ['requestVerified' => $requestVerified, 'errorMessage' => $errorMessage];
    }
    /**
     * function to verify clientApiToken
     * @return bool true if request verified i.e clientApiToken in Gk matches the token sent in header, false otherwise.
     */
    public function clientApiTokenCheck(ServerRequestInterface $request, ContainerInterface $container)
    {
        $route = $request->getAttribute('route');
        $clientapiToken = $container->get('options')->get('clientApiToken');
        $ignoredRouteForClientToken = ['plugins.scanner.testAjax', 'plugins.scanner.scans.perform', 'auth.login', 'auth.token', 'auth.directLogin'];
        if (\in_array($route->getName(), $ignoredRouteForClientToken)) {
            return \true;
        }
        if (empty($clientapiToken)) {
            return \false;
        }
        $tokenInHeader = $request->getHeader('X-Token');
        if (!$tokenInHeader || \count($tokenInHeader) < 1 || !isset($tokenInHeader[0])) {
            return \false;
        }
        if ($tokenInHeader[0] == $clientapiToken) {
            return \true;
        }
        return \false;
    }
    public function IsIpAllowedToAccessPrivateApis(ServerRequestInterface $request, ContainerInterface $container)
    {
        $route = $request->getAttribute('route');
        $astraWhitelistedIps = \explode(',', $container->get('settings')->get('astraWhitelistedIps'));
        $m = $container->get('options')->get('siteSettings', null);
        if ($m && isset($m['astraWhitelist'])) {
            $astraWhitelistedIps = \array_merge($astraWhitelistedIps, $m['astraWhitelist']);
        }
        $ignoredRouteForClientToken = ['plugins.scanner.testAjax', 'plugins.scanner.scans.perform', 'auth.login', 'auth.token'];
        if (\in_array($route->getName(), $ignoredRouteForClientToken)) {
            return \true;
        }
        $ip = $this->urlHelper->getClientIp();
        $address = Factory::parseAddressString($ip);
        \array_walk($astraWhitelistedIps, function (&$val, $key) {
            $val = Factory::parseRangeString($val);
        });
        foreach ($astraWhitelistedIps as $ipRange) {
            if (!empty($ipRange) && $address->matches($ipRange)) {
                return \true;
            }
        }
        return \false;
    }
}
