<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Middleware;

use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\GetAstra\Client\Helper\IpBlockingHelper;
use AstraPrefixed\GetAstra\Client\Helper\UrlHelper;
use AstraPrefixed\IPLib\Factory;
use AstraPrefixed\JWadhams\JsonLogic;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
use AstraPrefixed\Psr\Http\Message\ServerRequestInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
/**
 * Description of IpRuleMiddleware.
 *
 * @author aditya
 */
class IpRuleMiddleware
{
    private $container;
    private const MODULE_NAME = 'ipRule';
    /**
     * @var CacheInterface
     */
    //private $options;
    private $urlHelper;
    private $ipBlockingHelper;
    private $commonHelper;
    /**
     * @var LoggerInterface
     */
    private $logger;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->urlHelper = new UrlHelper();
        $this->commonHelper = new CommonHelper();
        $this->ipBlockingHelper = new IpBlockingHelper($this->container);
        $this->logger = $container->get('logger');
    }
    /**
     * @param ServerRequestInterface $request  PSR7 request
     * @param ResponseInterface      $response PSR7 response
     * @param callable               $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        // if ($this->ignoreGkRoutesWithRequestSigning()) {
        //     return $next($request->withAttribute('alreadyAllowed', true), $response);
        // }
        // if ($this->ignoreAllowedWPAdminPages()) {
        //     return $next($request->withAttribute('alreadyAllowed', true), $response);
        // }
        if (!$this->commonHelper->checkIfOptionsPopulated($this->container->get('options'))) {
            $response = $next($request, $response);
            return $response;
        }
        $this->ipBlockingHelper = new IpBlockingHelper($this->container);
        if (!$request->getAttribute('astraEnabled')) {
            $response = $next($request, $response);
            return $response;
        }
        $currentIpAddress = $this->urlHelper->getClientIp();
        //Fetch ip
        if (!$currentIpAddress) {
            $this->logger->warning('Visitor IP detected as null in IP Rule Module, exiting.');
            $response = $next($request, $response);
            return $response;
        }
        if ($this->isIpTrustedOrBlocked($currentIpAddress)) {
            //Allowed IP_Rules specified in Symfony.
            return $next($request->withAttribute('alreadyAllowed', \true), $response);
        }
        if ($this->isIpTrustedOrBlocked($currentIpAddress, \false)) {
            //Blocked IP_Rules specified in Symfony.
            $blockRefId = $this->ipBlockingHelper->generateBlockReferenceId(self::MODULE_NAME);
            return $this->ipBlockingHelper->blockPageDataPrep($response, $currentIpAddress, 'IP Block rule in Symfony', self::MODULE_NAME, null, $blockRefId);
            //show block page
        }
        if ($this->ipBlockingHelper->isIpAllowedByGatekeeper($currentIpAddress)) {
            //Allowed IP_Rules stored in GK.
            return $next($request->withAttribute('alreadyAllowed', \true), $response);
        }
        if ($this->ipBlockingHelper->isIpBlockedByGatekeeper($currentIpAddress)) {
            //if its in blocked IP list of GKCache
            $blockRefId = $this->ipBlockingHelper->generateBlockReferenceId(self::MODULE_NAME);
            return $this->ipBlockingHelper->blockPageDataPrep($response, $currentIpAddress, 'IP Block rule in Gatekeeper cache', self::MODULE_NAME, null, $blockRefId);
            //show block page
        }
        $fnr = $this->evaluateAllBoosterRules($currentIpAddress, $request);
        if ($fnr['decision'] !== \false) {
            if ('block' === $fnr['decision']) {
                $blockRefId = $this->ipBlockingHelper->generateBlockReferenceId("Booster", $fnr['boosterId']);
                return $this->ipBlockingHelper->blockPageDataPrep($response, $currentIpAddress, 'Booster rule evaluated to true', self::MODULE_NAME, null, $blockRefId);
                //show block page
            } else {
                return $next($request->withAttribute('alreadyAllowed', \true), $response);
                //allowed by booster rule
            }
        }
        //        if ($this->isIpFromCountry($currentIpAddress)) { //Allowed Countries
        //            return $next($request->withAttribute('alreadyAllowed', true), $response);
        //        }
        //
        //        if ($this->isIpFromCountry($currentIpAddress, false)) { //Blocked countries
        //            return $response->withHeader($this->ipBlockingHelper::BLOCK_PAGE, true);
        //        }
        $this->ipBlockingHelper->clearOldIpLogRecords();
        $response = $next($request, $response);
        return $response;
    }
    /**
     * Checks if IP is trusted or blocked in the IP rules(Symfony).
     *
     * @param string $ip      The IP address to check
     * @param bool   $trusted If true checks in trustedRules else in blockedRules
     *
     * @return bool true if IP exists in the specified rules, false otherwise
     */
    public function isIpTrustedOrBlocked($ip, $trusted = \true)
    {
        $address = Factory::addressFromString($ip);
        $options = $this->container->get('options');
        $ipRules = $options->get('ipRules');
        if (\is_null($ipRules)) {
            $ipRules = [];
        }
        if ($trusted) {
            //TrustedIPs filter
            $ipArray = \array_filter($ipRules, function ($val) {
                $now = new \DateTime();
                //if key doesnt exists then value is null i.e trust/block permanently
                $expiresAt = \array_key_exists('expiresAt', $val) ? new \DateTime($val['expiresAt']) : new \DateTime('+ 1 year');
                if ('trust' === $val['type'] && $now < $expiresAt) {
                    return \true;
                } else {
                    return \false;
                }
            });
            //check Astra API IPs which will be white listed by default
            $astraWhitelistedIps = \explode(',', $this->container->get('settings')->get('astraWhitelistedIps'));
            $m = $this->container->get('options')->get('siteSettings', null);
            if (!empty($m) && isset($m['astraWhitelist'])) {
                $astraWhitelistedIps = \array_merge($astraWhitelistedIps, $m['astraWhitelist']);
            }
            $ipArrayParsed = \array_merge(\array_column($ipArray, 'ipAddress'), $astraWhitelistedIps);
        } else {
            //BlockedIPs filter
            $ipArray = \array_filter($ipRules, function ($val) {
                $now = new \DateTime();
                $expiresAt = \array_key_exists('expiresAt', $val) ? new \DateTime($val['expiresAt']) : new \DateTime('+ 1 year');
                if ('block' === $val['type'] && $now < $expiresAt) {
                    return \true;
                } else {
                    return \false;
                }
            });
            $ipArrayParsed = \array_column($ipArray, 'ipAddress');
        }
        \array_walk($ipArrayParsed, function (&$val, $key) {
            $val = Factory::rangeFromString($val);
        });
        foreach ($ipArrayParsed as $ipRange) {
            if (!empty($ipRange) && $address->matches($ipRange)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Evaluate all booster rules, if any of them fails then the request should be blocked.
     * Respects booster rule priority.
     * Lowest priority runs first.
     * If a rule matches then decision is taken basis that, and no other rules are checked.
     *
     * @param string                 $ip
     * @param ServerRequestInterface $request PSR7 request
     *
     * @return mixed $decision  Non falsy value (trust/block) if any booster rules evaluates to true, falsy value otherwise
     */
    public function evaluateAllBoosterRules($ip, $request)
    {
        if (!$this->container->has('options')) {
            return ['decision' => \false, 'boosterId' => null];
        }
        //dont block
        /** @var CacheInterface $allOptions */
        $allOptions = $this->container->get('options');
        if (!$allOptions->has('boosters')) {
            return ['decision' => \false, 'boosterId' => null];
        }
        //dont block
        $allBoosterRules = $allOptions->get('boosters');
        if (empty($allBoosterRules) || !\is_array($allBoosterRules) || \count($allBoosterRules) <= 0) {
            return ['decision' => \false, 'boosterId' => null];
        }
        //dont block
        $jsonLogicLibrary = new JsonLogic();
        $dataSet = $this->prepareJsonDataSet($ip, $request, $allBoosterRules);
        \uasort($allBoosterRules, function ($a, $b) {
            if ($a['priority'] == $b['priority']) {
                $createdAtOne = new \DateTime($a['createdAt']);
                $createdAtTwo = new \DateTime($b['createdAt']);
                if ($createdAtOne == $createdAtTwo) {
                    return 0;
                }
                return $createdAtOne > $createdAtTwo ? -1 : 1;
                //sorted by createdAt Desc
            }
            return $a['priority'] < $b['priority'] ? -1 : 1;
            //sorted by priority Asc
        });
        foreach ($allBoosterRules as $rule) {
            if (!isset($rule['jsonLogicFormat']) || !$rule['status'] || isset($rule['deletedAt'])) {
                continue;
            }
            $ruleSet = $rule['jsonLogicFormat'];
            if (!isset($ruleSet['rule']) || !isset($ruleSet['data'])) {
                continue;
            }
            $dataFormat = $ruleSet['data'];
            $data = $this->applyBoosterRuleFormat($dataFormat, $dataSet);
            $bool = $jsonLogicLibrary->apply($ruleSet['rule'], $data);
            //    echo '<pre>';
            //    var_dump($bool);
            //    print_r($dataFormat);
            //    print_r($data);
            //    print_r($dataSet);
            //    print_r($rule);
            //    exit;
            if ($bool) {
                if ('trust' == $rule['action']) {
                    $decision = 'trust';
                }
                //dont block since rule action = trust
                if ('block' == $rule['action']) {
                    $decision = 'block';
                }
                //block
                $boosterLogs = $allOptions->get($this->ipBlockingHelper::BOOSTER_LOG_KEY);
                //fetch log
                $existingId = \array_column($boosterLogs, 'id');
                if (\in_array($rule['id'], $existingId)) {
                    $key = \array_search($rule['id'], $existingId);
                    ++$boosterLogs[$key]['counter'];
                } else {
                    $rule['counter'] = 1;
                    $boosterLogs[] = $rule;
                }
                $allOptions->set($this->ipBlockingHelper::BOOSTER_LOG_KEY, $boosterLogs);
                //set again
                return ['decision' => $decision, 'boosterId' => $rule['id']];
                //returning from here since a higher priority rule has evaluated to true, no need to check other rules with a lower priority
            }
        }
        return ['decision' => \false, 'boosterId' => null];
    }
    /**
     * Recursively searches and replaces the variable values in booster format with current request dataSet values.
     *
     * @param array $format
     * @param array $dataSet
     *
     * @return $format
     */
    private function applyBoosterRuleFormat($format, $dataSet)
    {
        foreach ($format as $key => $val) {
            if ($this->isAssoc($val)) {
                $format[$key] = $this->applyBoosterRuleFormat($val, $dataSet);
            } else {
                if (\array_key_exists($key, $dataSet['ip'])) {
                    $format[$key] = $dataSet['ip'][$key];
                }
                if (\array_key_exists($key, $dataSet['request'])) {
                    $format[$key] = $dataSet['request'][$key];
                }
            }
        }
        return $format;
    }
    /**
     * Helper function to check if the given argument is an associative array or not.
     * stack overflow - https://stackoverflow.com/a/173479.
     *
     * @param $arr
     *
     * @return bool
     */
    private function isAssoc($arr)
    {
        if (!\is_array($arr)) {
            return \false;
        }
        if ([] === $arr) {
            return \false;
        }
        return \array_keys($arr) !== \range(0, \count($arr) - 1);
    }
    /**
     * Prepares JSON data set according to the current request object.
     * for the JSON logic checking library to check the booster rules against.
     *
     * @param string $ip
     *
     * @return array $dataSet
     */
    private function prepareJsonDataSet($ip, ServerRequestInterface $request, array $boosterRules)
    {
        $detectCountry = \false;
        //Only detect country if atleast one of the booster has country variable in it
        foreach ($boosterRules as $key => $val) {
            if (\false !== \strpos(\json_encode($val['jsonLogicFormat']), 'ip.country')) {
                $detectCountry = \true;
                break;
            }
        }
        if ($detectCountry) {
            $countryObj = $this->ipBlockingHelper->getIpInformation($ip);
            $countryIso = empty($countryObj) ? null : $countryObj->raw['country']['iso_code'];
        }
        //$uri = $request->getServerParam('X_ASTRA_ORIGINAL_REQUEST_URI');
        $uri = $request->getUri()->getBasePath();
        $uriFull = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . $request->getUri()->getBasePath();
        $uriPath = $request->getUri()->getBasePath();
        if ($request->getUri()->getPath()) {
            if ($request->getUri()->getPath() != '/') {
                $uriFull .= "/" . $request->getUri()->getPath();
                $uri .= "/" . $request->getUri()->getPath();
                $uriPath .= "/" . $request->getUri()->getPath();
            } else {
                $uriFull .= $request->getUri()->getPath();
                $uri .= $request->getUri()->getPath();
                $uriPath .= $request->getUri()->getPath();
            }
        }
        if ($request->getUri()->getQuery()) {
            $uriFull .= '?' . $request->getUri()->getQuery();
            $uri .= '?' . $request->getUri()->getQuery();
        }
        // echo'$uri=';var_dump($uri);echo "<br>";
        // echo'$uriFull=';var_dump($uriFull);echo "<br>";
        // echo'$uriPath=';var_dump($uriPath);echo "<br>";
        // echo'$uriQuery=';var_dump($request->getUri()->getQuery());echo "<br>";
        // echo'$getPath=';var_dump($request->getUri()->getPath());echo "<br>";
        // exit;
        //prepare data array with all possible variables.
        $dataSet = ['ip' => [
            'address' => $ip,
            'country' => $countryIso ?? null,
            'addresses' => [$ip],
            //multiselect nuance/caveat in front-end
            'xffs' => [$request->getHeader('X-Forwarded-For')[0] ?? null],
        ], 'request' => ['host' => $request->getUri()->getHost(), 'referer' => $request->getHeader('Referer')[0] ?? null, 'method' => $request->getMethod(), 'uriFull' => $uriFull, 'uri' => $uri, 'uriPath' => $uriPath, 'uriQuery' => $request->getUri()->getQuery(), 'userAgent' => $request->getHeader('User-Agent')[0] ?? null, 'cookie' => \http_build_query($request->getCookieParams()), 'xff' => $request->getHeader('X-Forwarded-For')[0] ?? null]];
        return $dataSet;
    }
    /**
     * Function prevents WAF from running on Admin panel pages with some exceptions.
     * For wordpress right now all wp-admin routes are ignored except for wp-login & wp-signup.
     *
     * @return bool true if request should NOT be checked i.e Astra WAF will not run on the specified cases,
     *              false if request should be checked further.
     */
    private function ignoreAllowedWPAdminPages()
    {
        //check cms
        /** @var CacheInterface $options */
        $options = $this->container->get('options');
        if (!$options->has('fullSiteObject')) {
            return \false;
        }
        $fullSiteObj = $options->get('fullSiteObject');
        if (!isset($fullSiteObj['cms'])) {
            return \false;
        }
        $cms = $fullSiteObj['cms'];
        if (empty($cms) || 'wordpress' !== $cms) {
            return \false;
        }
        $urls = ['wp-signup', 'wp-login', 'wp-signup.php', 'wp-login.php'];
        /** @todo Maybe not hardcode it and get it from options or env */
        $wpAdminSlug = 'wp-admin';
        $actualLink = (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        if (\false !== \strpos($actualLink, $wpAdminSlug)) {
            foreach ($urls as $u) {
                if (\false !== \strpos($actualLink, $u)) {
                    return \false;
                    //request will be checked further by WAF
                }
            }
            return \true;
            //request will not be checked further by WAF
        }
        return \false;
        //request will be checked further by WAF
    }
    /**
     * @deprecated
     * Ignores certain routes of GK Slim App, which need not be checked because
     * those routes are already verifying signed requests.
     *
     * 
     * @return bool true if route should be ignored, false otherwise
     */
    private function ignoreGkRoutesWithRequestSigning()
    {
        $actualLink = (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $routesToIgnore = ['updateSettings', 'bulkUpdateOptions'];
        //TODO: common flag to see if it is an astra api route
        if (ASTRA_API_ROUTE) {
            foreach ($routesToIgnore as $val) {
                if (\false !== \strpos($actualLink, $val)) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * Checks if IP/IP Range originates from a country which is present in
     * blockedCountry or trustedCountry.
     *
     * @param string $ip               The IP address to check
     * @param bool   $allowedCountries If true checks in trustedCountry else in blockedCountry
     *
     * @return bool true if IP country exists in country, false otherwise
     */
    public function isIpFromCountry($ip, $allowedCountries = \true) : bool
    {
        $options = $this->container->get('options');
        $siteSettings = $options->get('siteSettings');
        if ($allowedCountries) {
            $countryArray = $siteSettings['waf']['trustedCountry'];
        } else {
            $countryArray = $siteSettings['waf']['blockedCountry'];
        }
        $countryObj = $this->ipBlockingHelper->getIpInformation($ip);
        if ($countryObj) {
            $countryIso = $countryObj->raw['country']['iso_code'];
        } else {
            $countryIso = '';
        }
        if (\in_array($countryIso, $countryArray)) {
            return \true;
        }
        return \false;
    }
    //        echo '<pre>';
    //        $fullUri = (string) $request->getUri();
    //        echo ('getUri() - '. $fullUri);
    //        echo '<br>';
    //        $baseUrl = (string) $request->getUri()->getBaseUrl();
    //        echo ('getBaseUrl() - '. $baseUrl);
    //        echo '<br>';
    //        $basePath = (string) $request->getUri()->getBasePath();
    //        echo ('getBasePath() - '. $basePath);
    //        echo '<br>';
    //        $query = (string) $request->getUri()->getQuery();
    //        echo ('getQuery() - '. $query);
    //        echo '<br>';
    //        $path = (string) $request->getUri()->getPath();
    //        echo ('getPath() - '. $path);
    //        echo '<br>';
    //        $var4 =  $request->getServerParam('X_ASTRA_ORIGINAL_REQUEST_URI');
    //        echo ('original reuest URI - '. json_encode($var4));
    //
    //        echo '<br>';
    //        $uriFull = (string) $request->getUri()->getBaseUrl(). "/?" . (string) $request->getUri()->getQuery();
    //        echo ('Final full URI - '.$uriFull);
    //
    //        echo '<br>';
    //        $basePathAndQuery = (string) $request->getUri()->getBasePath(). "/?" . (string) $request->getUri()->getQuery();
    //        echo ('Base Path +query - '.$basePathAndQuery);
    //        exit;
}
//var_dump($countryIso);exit;
