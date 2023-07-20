<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Helper;

use AstraPrefixed\GetAstra\Client\Tclient\Configuration;
use AstraPrefixed\GetAstra\Client\Tclient\Threat\ThreatApi;
use AstraPrefixed\IPLib\Factory;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
use AstraPrefixed\GuzzleHttp\Client;
use AstraPrefixed\GeoIp2\Database\Reader;
use Throwable;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use AstraPrefixed\GetAstra\Client\Tclient\ApiException;
use Exception;
/**
 * Description of IpBlockingHelper.
 * *
 * @author aditya
 */
class IpBlockingHelper
{
    private $urlHelper;
    private $container;
    public const IP_ALLOWED_KEY = 'allowedIp';
    public const BOOSTER_LOG_KEY = 'boosterLog';
    public const IP_LOG_KEY = 'ipAddressLog';
    private const IP_LOG_EXPIRY = 604800;
    // 1 week
    private const IP_LOG_CLEAR_LAST_CRON_KEY = 'ipLogClearCron';
    private const IP_LOG_CLEAR_CRON_FREQUENCY = 86400;
    // 1 day
    public const BLOCK_PAGE = 'Block-Page';
    public const THREAT_POSTED_URL = 'threatGuid';
    private const IP_BLOCKED_STATUS = 'blocked';
    private const IP_NOT_BLOCKED_STATUS = 'not-blocked';
    public const GEOIP_DB_PATH = ASTRA_STORAGE_ROOT . 'GeoLite2-Country.mmdb';
    private $thresholds = [];
    /**
     * @var CacheInterface
     */
    private $options;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->options = $this->container->get('options');
        $this->urlHelper = new UrlHelper();
        $this->initializeLoginThresholds();
        //load login thresholds
    }
    /**
     * Initializes all default/custom values for login/attack thresholds and block durations.
     * Decides current values based on security level and customValues(if specified).
     */
    public function initializeLoginThresholds()
    {
        $options = $this->container->get('options');
        $siteSettings = $options->get('siteSettings');
        if (!$options || !$siteSettings) {
            return;
        }
        $defaults = $siteSettings['waf']['defaultThresholds'];
        $custom = $siteSettings['waf']['customThresholds'];
        //Login setting
        $this->thresholds['login']['low']['threshold'] = $defaults['loginFailureThresholdLow'];
        $this->thresholds['login']['low']['duration'] = $defaults['loginBlockDurationLow'];
        $this->thresholds['login']['medium']['threshold'] = $defaults['loginFailureThresholdMedium'];
        $this->thresholds['login']['medium']['duration'] = $defaults['loginBlockDurationMedium'];
        $this->thresholds['login']['high']['threshold'] = $defaults['loginFailureThresholdHigh'];
        $this->thresholds['login']['high']['duration'] = $defaults['loginBlockDurationHigh'];
        if (!empty($custom['loginFailureThreshold']) && !empty($custom['loginBlockDuration'])) {
            $this->thresholds['login']['current']['threshold'] = $custom['loginFailureThreshold'];
            $this->thresholds['login']['current']['duration'] = $custom['loginBlockDuration'];
        } else {
            $securityLevel = \strtolower($siteSettings['securityLevel']);
            if (!\in_array($securityLevel, ['low', 'medium', 'high'])) {
                $securityLevel = 'medium';
                //defaults to medium
            }
            $this->thresholds['login']['current']['threshold'] = $this->thresholds['login'][$securityLevel]['threshold'];
            $this->thresholds['login']['current']['duration'] = $this->thresholds['login'][$securityLevel]['duration'];
        }
        //Attack setting
        $this->thresholds['attack']['low']['threshold'] = $defaults['attackThresholdLow'];
        $this->thresholds['attack']['low']['duration'] = $defaults['attackBlockDurationLow'];
        $this->thresholds['attack']['medium']['threshold'] = $defaults['attackThresholdMedium'];
        $this->thresholds['attack']['medium']['duration'] = $defaults['attackBlockDurationMedium'];
        $this->thresholds['attack']['high']['threshold'] = $defaults['attackThresholdHigh'];
        $this->thresholds['attack']['high']['duration'] = $defaults['attackBlockDurationHigh'];
        if (!empty($custom['attackThreshold']) && !empty($custom['attackBlockDuration'])) {
            $this->thresholds['attack']['current']['threshold'] = $custom['attackThreshold'];
            $this->thresholds['attack']['current']['duration'] = $custom['attackBlockDuration'];
        } else {
            $securityLevel = \strtolower($siteSettings['securityLevel']);
            if (!\in_array($securityLevel, ['low', 'medium', 'high'])) {
                $securityLevel = 'medium';
                //defaults to medium
            }
            $this->thresholds['attack']['current']['threshold'] = $this->thresholds['attack'][$securityLevel]['threshold'];
            $this->thresholds['attack']['current']['duration'] = $this->thresholds['attack'][$securityLevel]['duration'];
        }
    }
    /**
     * Function to record attack by IP internally in Gatekeeper.
     *
     * @param string $ip
     * @param string $moduleName
     * @param mixed  $impact
     *
     * @return array $fnRes  Updated IP record in GK ()
     */
    public function recordIpBlockingInstance($ip, $moduleName, $impact = null, $permanentBlock = \false)
    {
        $block = \false;
        $existingBlockedIp = $this->options->get($this::IP_LOG_KEY);
        $ipAddresses = \array_column($existingBlockedIp, 'ip');
        if (!\in_array($ip, $ipAddresses)) {
            /** @question  can ip address be the key itself to its record ?? */
            $blockUntilVal = $permanentBlock ? (new \DateTime('+ 100 years', new \DateTimeZone('UTC')))->format('c') : null;
            $statusVal = $permanentBlock ? $this::IP_BLOCKED_STATUS : $this::IP_NOT_BLOCKED_STATUS;
            $newRecord = ['ip' => $ip, 'lastAttackAt' => \time(), 'attackCount' => 1, 'failedLoginCount' => 0, 'status' => $statusVal, 'blockUntil' => $blockUntilVal, 'moduleName' => $moduleName, 'impact' => $impact, 'createdAt' => \time()];
            $existingBlockedIp[] = $fnRes = $newRecord;
        } else {
            //modify existing record
            $key = \array_search($ip, $ipAddresses);
            $existingIpData = $existingBlockedIp[$key];
            $existingIpData['lastAttackAt'] = \time();
            ++$existingIpData['attackCount'];
            $existingIpData['moduleName'] = $moduleName;
            $existingIpData['impact'] = $impact;
            $statusVal = $existingIpData['status'];
            //default Status value
            //blocking and checking thresholds
            if ($existingIpData['attackCount'] > $this->thresholds['attack']['current']['threshold']) {
                $statusVal = $this::IP_BLOCKED_STATUS;
                $time = new \DateTime('+ ' . $this->thresholds['attack']['current']['duration'] . ' minutes', new \DateTimeZone('UTC'));
                $existingIpData['blockUntil'] = $permanentBlock ? (new \DateTime('+ 100 years', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s') : $time->format('Y-m-d H:i:s');
                $block = \true;
            } else {
                $existingIpData['blockUntil'] = null;
            }
            $statusValFinal = $permanentBlock ? $this::IP_BLOCKED_STATUS : $statusVal;
            $existingIpData['status'] = $statusValFinal;
            //replacing back the Ip data with new values
            $existingBlockedIp[$key] = $fnRes = $existingIpData;
        }
        $this->options->set($this::IP_LOG_KEY, $existingBlockedIp);
        $fnRes['blockedByGkDueToThresholdReached'] = $block;
        return $fnRes;
    }
    /**
     * Function to record details about failed login attempt and blocks IP if threshold is reached
     * Records IP internally in gatekeeper.
     *
     * @param string $ip
     * @param string $moduleName
     * @param mixed  $impact
     *
     * @return bool $block         True if IP thresholds reached & module should render blockPage, false otherwise
     */
    public function recordFailedLogin($ip, $moduleName, $impact = null, $permanentBlock = \false)
    {
        $block = \false;
        $existingBlockedIp = $this->options->get($this::IP_LOG_KEY);
        $ipAddresses = \array_column($existingBlockedIp, 'ip');
        if (!\in_array($ip, $ipAddresses)) {
            $blockUntilVal = $permanentBlock ? (new \DateTime('+ 100 years', new \DateTimeZone('UTC')))->format('c') : null;
            $statusVal = $permanentBlock ? $this::IP_BLOCKED_STATUS : $this::IP_NOT_BLOCKED_STATUS;
            $newRecord = ['ip' => $ip, 'lastAttackAt' => \time(), 'attackCount' => 0, 'failedLoginCount' => 1, 'status' => $statusVal, 'blockUntil' => $blockUntilVal, 'moduleName' => $moduleName, 'impact' => $impact, 'createdAt' => \time()];
            $existingBlockedIp[] = $newRecord;
        } else {
            //modify existing record
            $key = \array_search($ip, $ipAddresses);
            $existingIpData = $existingBlockedIp[$key];
            $existingIpData['lastAttackAt'] = \time();
            ++$existingIpData['failedLoginCount'];
            $existingIpData['moduleName'] = $moduleName;
            $statusVal = $existingIpData['status'];
            //default Status value
            //blocking and checking thresholds
            if ($existingIpData['failedLoginCount'] > $this->thresholds['login']['current']['threshold']) {
                $statusVal = $this::IP_BLOCKED_STATUS;
                $time = new \DateTime('+ ' . $this->thresholds['login']['current']['duration'] . ' minutes', new \DateTimeZone('UTC'));
                $existingIpData['blockUntil'] = $permanentBlock ? (new \DateTime('+ 100 years', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s') : $time->format('Y-m-d H:i:s');
                $block = \true;
            } else {
                $existingIpData['blockUntil'] = null;
            }
            $statusValFinal = $permanentBlock ? $this::IP_BLOCKED_STATUS : $statusVal;
            $existingIpData['status'] = $statusValFinal;
            //replacing back the Ip data with new values
            $existingBlockedIp[$key] = $existingIpData;
        }
        $this->options->set($this::IP_LOG_KEY, $existingBlockedIp);
        return $block;
    }
    /**
     * Function to trust an IP and store internally in Gatekeeper.
     * All IPs are Permanent allow (max duration) for now.
     *
     * @param string $ip
     *
     * @return void
     */
    public function recordAllowedIpInstance($ip, $moduleName)
    {
        $options = $this->container->get('options');
        $existingAllowedIp = $options->get($this::IP_ALLOWED_KEY);
        $ipAddresses = \array_column($existingAllowedIp, 'ip');
        if (!\in_array($ip, $ipAddresses)) {
            $allowUntilVal = (new \DateTime('+ 100 years', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
            $newRecord = ['ip' => $ip, 'allowUntil' => $allowUntilVal, 'moduleName' => $moduleName];
            $existingAllowedIp[] = $newRecord;
        } else {
            //modify existing record
            $key = \array_search($ip, $ipAddresses);
            $existingIpData = $existingAllowedIp[$key];
            //refreshing duration
            $existingIpData['allowUntil'] = (new \DateTime('+ 100 years', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
            $existingIpData['moduleName'] = $moduleName;
            //replacing back the Ip data with new values
            $existingAllowedIp[$key] = $existingIpData;
        }
        $options->set($this::IP_ALLOWED_KEY, $existingAllowedIp);
        //remove IP from blocked GK list since its allowed now
        $existingBlockedIp = $options->get($this::IP_LOG_KEY);
        $ipAddressesBlocked = \array_column($existingBlockedIp, 'ip');
        if (\in_array($ip, $ipAddressesBlocked)) {
            $key = \array_search($ip, $ipAddressesBlocked);
            unset($existingBlockedIp[$key]);
            $options->set($this::IP_LOG_KEY, $existingBlockedIp);
        }
        return;
    }
    /**
     * Checks if the IP is allowed by GK or not.
     * If its allowed and its duration (allowUntil) expired then its removed from GK allowedIp list.
     *
     * @param string $ip
     *
     * @return bool True if its allowed, false otherwise
     */
    public function isIpAllowedByGatekeeper($ip)
    {
        $options = $this->container->get('options');
        $existingAllowedIp = $options->get($this::IP_ALLOWED_KEY);
        $ipAddresses = $ipAddressesParsed = \array_column($existingAllowedIp, 'ip');
        \array_walk($ipAddressesParsed, function (&$val, $key) {
            $val = Factory::rangeFromString($val);
        });
        $ipParsed = Factory::addressFromString($ip);
        $notPresentFlag = \true;
        foreach ($ipAddressesParsed as $range) {
            if ($ipParsed->matches($range)) {
                $notPresentFlag = \false;
            }
        }
        if ($notPresentFlag) {
            return \false;
        }
        $key = \array_search($ip, $ipAddresses);
        $existingIpData = $existingAllowedIp[$key];
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $allowUntil = \DateTime::createFromFormat('Y-m-d\\TH:i:sP', $existingIpData['allowUntil'], new \DateTimeZone('UTC'));
        if (isset($existingIpData['allowUntil']) && $allowUntil >= $now) {
            return \true;
        } else {
            //since duration has expired now, remove IP from allowed list
            $key = \array_search($ip, $ipAddresses);
            unset($existingAllowedIp[$key]);
            $options->set($this::IP_ALLOWED_KEY, $existingAllowedIp);
            return \false;
        }
    }
    /**
     * Checks if the IP is blocked by GK or not.
     * If its blocked and it duration (blockUntil) expired then its removed from GK's blockedIp list.
     *
     * @param string $ip
     *
     * @return bool True if blocked by Gk false other wise
     */
    public function isIpBlockedByGatekeeper($ip)
    {
        $options = $this->container->get('options');
        $existingBlockedIp = $options->get($this::IP_LOG_KEY);
        //var_dump($existingBlockedIp);exit;
        $ipAddresses = $ipAddressesParsed = \array_column($existingBlockedIp, 'ip');
        \array_walk($ipAddressesParsed, function (&$val, $key) {
            $val = Factory::rangeFromString($val);
        });
        $ipParsed = Factory::addressFromString($ip);
        $notPresentFlag = \true;
        foreach ($ipAddressesParsed as $range) {
            if ($ipParsed->matches($range)) {
                $notPresentFlag = \false;
            }
        }
        if ($notPresentFlag) {
            return \false;
        }
        $key = \array_search($ip, $ipAddresses);
        $existingIpData = $existingBlockedIp[$key];
        if ($existingIpData['status'] === $this::IP_BLOCKED_STATUS) {
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $blockUntil = \DateTime::createFromFormat('Y-m-d\\TH:i:sP', $existingIpData['blockUntil'], new \DateTimeZone('UTC'));
            if (isset($existingIpData['blockUntil']) && $blockUntil >= $now) {
                return \true;
            } else {
                //since duration has expired now, remove IP from blocked list
                $key = \array_search($ip, $ipAddresses);
                unset($existingBlockedIp[$key]);
                //store the new IP array again in the options
                $options->set($this::IP_LOG_KEY, $existingBlockedIp);
                return \false;
            }
        } else {
            return \false;
        }
    }
    /**
     * Function to POST a threat resource to symfony.
     *
     * @param mixed $threatBody
     *
     * @return mixed Threat API response if successfully posted, false otherwise
     */
    public function recordThreatInSymfony($threatBody)
    {
        $oauthService = $this->container->get('oauth');
        $tokenObject = $oauthService->getTokenObject();
        $oauthClientId = $this->container->get('options')->get('oauthClientId');
        $oauthClientSecret = $this->container->get('options')->get('oauthClientSecret');
        $apiUrl = \substr($this->container->get('settings')['relay']['api_url_https'], 0, -1);
        //Initialize Threat API
        if (isset($tokenObject, $oauthClientId, $oauthClientSecret, $apiUrl)) {
            $config = new Configuration();
            $apiConfiguration = $config->setAccessToken($tokenObject->getToken());
            $apiConfiguration->setHost($apiUrl)->setDebug(\false)->setUsername($oauthClientId)->setPassword($oauthClientSecret);
            $threatApi = new ThreatApi(null, $apiConfiguration);
        } else {
            $threatApi = null;
        }
        if ($threatApi) {
            try {
                $response = $threatApi->postThreatCollection($threatBody);
            } catch (ApiException $ex) {
                if ($ex->getCode() == '401') {
                    /**@var CacheInterface $options */
                    $options = $this->container->get('options');
                    $options->delete('accessToken');
                }
                return \false;
            } catch (Throwable $e) {
                return \false;
            }
            return $response;
        } else {
            return \false;
        }
    }
    /**
     * Prepares the response object with appropriate block page header that gets checked in ChecksMiddleware
     * for block page rendering.
     * Also sets other data for block page template in header.
     *
     * @param string $visitorIp
     * @param string $blockReason
     * @param string $blockedByModule
     * @param        $attackParameters
     * @param array  $threats
     *
     * @return ResponseInterface $newResponse
     */
    public function blockPageDataPrep(ResponseInterface $response, $visitorIp = null, $blockReason = null, $blockedByModule = null, $attackParameters = null, $blockRefId = null)
    {
        $blockPageData['visitorIp'] = isset($visitorIp) ? $visitorIp : null;
        $blockPageData['blockReason'] = isset($blockReason) ? $blockReason : null;
        $blockPageData['blockedByModule'] = isset($blockedByModule) ? $blockedByModule : null;
        if ($attackParameters) {
            if ('array' === \gettype($attackParameters) && \count($attackParameters) > 0) {
                $blockPageData['attackParameters'] = \json_encode($attackParameters);
            } else {
                $blockPageData['attackParameters'] = $attackParameters;
            }
        } else {
            $blockPageData['attackParameters'] = null;
        }
        $blockPageData['blockRefId'] = isset($blockRefId) ? $blockRefId : null;
        $blockPageData['domain'] = $_SERVER['HTTP_HOST'];
        //setting block page template's data header
        $newResponse = $response->withAddedHeader($this::THREAT_POSTED_URL, $blockPageData);
        return $newResponse->withHeader($this::BLOCK_PAGE, \true);
        //setting block page header
    }
    /**
     * @param string $ip
     *
     * @return object $record
     */
    public function getIpInformation($ip)
    {
        $dbFilePath = self::GEOIP_DB_PATH;
        if (!\file_exists($dbFilePath)) {
            return null;
        }
        $this->ipReader = new Reader($dbFilePath);
        try {
            $record = $this->ipReader->country($ip);
            return $record;
        } catch (\Exception $e) {
            return null;
        }
    }
    public function generateBlockReferenceId($moduleName, $boosterId = null)
    {
        $siteObj = $this->container->get('options')->get('fullSiteObject');
        if (isset($siteObj)) {
            $site = $siteObj['domain'];
        } else {
            $site = '';
        }
        $s = '';
        switch ($moduleName) {
            case "ipRule":
                $s = '-D-';
                return "GK" . $s . \sha1($moduleName . \time() . $site);
                break;
            case "WafPlugin":
                $s = '-W-';
                return "GK" . $s . \sha1($moduleName . \time() . $site);
                break;
            case "Booster":
                $s = "-B-";
                return "GK" . $s . $boosterId;
                break;
            case "BotPlugin":
                $s = "-BB-";
                return "GK" . $s . \sha1($moduleName . \time() . $site);
                break;
        }
        return "GK-X-" . $s . \sha1($moduleName . \time() . $site);
        //default if none of the module matches in switch case
    }
    /**
     * Removes IP log records in GK with createdAt older than 1 week && lastAttacAt also older than 1 week (IP_LOG_EXPIRY const) 
     * Additionaly this fn only once each day, (Frequency according to IP_LOG_CLEAR_CRON_FREQUENCY const) 
     */
    public function clearOldIpLogRecords()
    {
        $now = \time();
        $lastCron = $this->options->get($this::IP_LOG_CLEAR_LAST_CRON_KEY, \time());
        if (\is_null($lastCron)) {
            $this->options->set($this::IP_LOG_CLEAR_LAST_CRON_KEY, $now);
            $lastCron = $now;
        }
        $diff = $now - $lastCron;
        if ($diff <= $this::IP_LOG_CLEAR_CRON_FREQUENCY) {
            return;
        }
        $ipLogs = $this->options->get($this::IP_LOG_KEY);
        if (empty($ipLogs)) {
            $this->options->set($this::IP_LOG_CLEAR_LAST_CRON_KEY, \time());
            return;
        }
        $collection = $ipLogs;
        foreach ($collection as $key => $ipLog) {
            $continue = \false;
            if (!isset($ipLog['createdAt'])) {
                $ipLog['createdAt'] = \time();
                $collection[$key] = $ipLog;
                $continue = \true;
            }
            if (!isset($ipLog['lastAttackAt'])) {
                $ipLog['lastAttackAt'] = \time();
                unset($ipLog['lastAttack']);
                // legacy key for storing lastAttack removed
                $collection[$key] = $ipLog;
                $continue = \true;
            }
            if ($continue == \true) {
                continue;
            }
            $createdAt = $ipLog['createdAt'];
            $diff2 = $now - $createdAt;
            $lastAttack = $ipLog['lastAttackAt'];
            $diff3 = $now - $lastAttack;
            if ($diff2 > $this::IP_LOG_EXPIRY && $diff3 > $this::IP_LOG_EXPIRY) {
                unset($collection[$key]);
            }
        }
        $this->options->set($this::IP_LOG_KEY, $collection);
        $this->options->set($this::IP_LOG_CLEAR_LAST_CRON_KEY, \time());
        return;
    }
}
