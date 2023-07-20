<?php

namespace AstraPrefixed;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use AstraPrefixed\Expose\Report;
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\GetAstra\Client\Helper\IpBlockingHelper;
use AstraPrefixed\GetAstra\Client\Helper\UrlHelper;
use AstraPrefixed\GetAstra\Client\Helper\PluginInterface;
use AstraPrefixed\Monolog\Logger;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
use AstraPrefixed\Psr\Http\Message\ServerRequestInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
use AstraPrefixed\Expose\Filter;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use AstraPrefixed\GetAstra\Client\Service\SiteSettingsService;
/**
 * Description of WafPlugin.
 *
 * @author aditya
 */
class WafPlugin implements PluginInterface
{
    private $container;
    private $urlHelper;
    private $ipBlockingHelper;
    private $commonHelper;
    /**
     * @var LoggerInterface
     */
    private $logger;
    private $impactThreshold;
    private $siteId;
    /**
     * @var SiteSettingsService
     */
    private $siteSettingsService;
    public function getMigrationDirPath() : string
    {
        return '';
    }
    public function getName() : string
    {
        return 'WafPlugin';
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
        return \true;
    }
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->impactThreshold = 5;
        //@todo fetch from env
        $this->siteId = $this->container->get('options')->get('siteId');
        $this->urlHelper = new UrlHelper();
        $this->commonHelper = new CommonHelper();
        $this->ipBlockingHelper = new IpBlockingHelper($this->container);
        $this->logger = $this->container->get('logger');
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
        if (!$request->getAttribute('astraEnabled')) {
            $response = $next($request, $response);
            return $response;
        }
        if ($request->getAttribute('alreadyAllowed')) {
            $response = $next($request, $response);
            return $response;
        }
        //Prepare data that will be checked
        $data = $this->prepareRequestData($request);
        if (!$data) {
            $response = $next($request, $response);
            //pass $req $res to net middleware without doing anything
            return $response;
        }
        $currentIpAddress = $this->urlHelper->getClientIp();
        //Fetch ip
        if (!$currentIpAddress) {
            $this->logger->warning('Visitor IP detected as null in Waf Module, exiting.');
            $response = $next($request, $response);
            return $response;
        }
        //Initialize Expose
        $this->clearDefaultFilters();
        $filters = new \AstraPrefixed\Expose\FilterCollection();
        $filters->load();
        //Load Astra WAF rules
        $filters = $this->loadWafRules($filters);
        //Logger
        $logger = new Logger('expose');
        $baseManager = new \AstraPrefixed\Expose\Manager($filters, $logger);
        //Set exceptions
        $manager = $this->loadExceptions($baseManager);
        $manager->setFilters($filters);
        //Check data
        $manager->run($data);
        //Determine requests fate
        $impact = $manager->getImpact();
        //Save Threat in dashboard if impact high & block app
        if ($impact > $this->impactThreshold) {
            //IP logging in GK if thresholds reached
            $blockRefId = $this->ipBlockingHelper->generateBlockReferenceId($this->getName());
            $ipData = $this->ipBlockingHelper->recordIpBlockingInstance($currentIpAddress, $this->getName(), $impact);
            $reports = $manager->getReports();
            $fnRes = $this->attachExposeReportDataToThreatAndPostThreat($request, $reports, $ipData, $blockRefId);
            if (!empty($fnRes)) {
                $attackParameters = \array_keys($fnRes);
            } else {
                $attackParameters = null;
            }
            return $this->ipBlockingHelper->blockPageDataPrep($response, $currentIpAddress, 'Malicious Request', $this->getName(), $attackParameters, $blockRefId);
            //show block page
        }
        $response = $next($request, $response);
        //pass the request to next middleware in stack
        return $response;
    }
    /**
     * Prepares array using Request object, which will be scanned by enygma expose.
     *
     * @return array
     */
    private function prepareRequestData(ServerRequestInterface $request)
    {
        $finalArray = [];
        $checkMethods = $this->container->get('options')->get('siteSettings')['waf']['httpMethods'];
        if (empty($checkMethods) || \count($checkMethods) <= 0) {
            return null;
        }
        $finalMethodsToScan = \array_intersect($checkMethods, [$request->getMethod(), 'COOKIE', 'HEADER']);
        foreach ($finalMethodsToScan as $httpVerb) {
            $allCapMethod = \strtoupper($httpVerb);
            switch ($allCapMethod) {
                case 'GET':
                    $finalArray[$allCapMethod] = $request->getQueryParams();
                    break;
                case 'POST':
                case 'PUT':
                case 'PATCH':
                case 'DELETE':
                    $finalArray[$allCapMethod] = $request->getParsedBody();
                    break;
                case 'REQUEST':
                    $finalArray[$allCapMethod] = $_REQUEST;
                    break;
                case 'COOKIE':
                    $finalArray[$allCapMethod] = $_COOKIE;
                    break;
                case 'HEADER':
                    $copy = \getallheaders();
                    unset($copy['Cookie']);
                    foreach ($copy as $key => $val) {
                        $finalArray['SERVER'][$key] = $val;
                    }
                    break;
            }
        }
        return $finalArray;
    }
    /**
     * @param array $reports Array of Expose\Reports
     * @param array $ipData  information about IP, against which threat has been recorded
     *
     * @return array true if Threat has been posted false otherwise
     */
    private function attachExposeReportDataToThreatAndPostThreat(ServerRequestInterface $request, $reports, $ipData, $blockRefId)
    {
        $siteMode = $this->container->get('options')->get('siteSettings')['protectionMode'];
        if ('monitoring' != $siteMode) {
            if ($ipData['blockedByGkDueToThresholdReached']) {
                $status = 'blocked';
            } else {
                $status = 'stopped';
            }
        } else {
            $status = 'monitored';
        }
        $threatBody = $this->commonHelper->threatPostPrepare($request, $this->siteId, $this->container, $status);
        \array_walk($reports, function (&$item1, $key) {
            /**  @var $item1 Report */
            $item1 = $item1->toArray();
        });
        $uniqueAttackParameters = [];
        foreach ($reports as $val) {
            if (!$val['varPath']) {
                continue;
            }
            $key = \array_reduce($val['varPath'], function ($carry, $item) {
                if (!$carry) {
                    return $item;
                }
                return $carry . '.' . $item;
            });
            if (\in_array($key, \array_keys($uniqueAttackParameters))) {
                //each unique attack parameters are POST-ed only once to Symfony
                $threatToPost = $uniqueAttackParameters[$key];
            } else {
                $threatToPost = $threatBody;
                $threatToPost['attackedParameter'] = $key;
                $threatToPost['attackVector'] = \base64_encode($val['varValue']);
                $threatToPost['expiresAt'] = isset($ipData['blockUntil']) ? $ipData['blockUntil'] : null;
                $threatToPost['blockRefId'] = $blockRefId;
                $threatToPost['loggedAt'] = (new \DateTime('now', new \DateTimeZone('UTC')))->format('c');
            }
            \usort($val['filterMatches'], function ($a, $b) {
                return $a->getImpact() < $b->getImpact();
                //Descending order of Impact
            });
            foreach ($val['filterMatches'] as $fm) {
                //in a single report - filterMatches can have multiple values in an array.
                /** @var CustomWafRule $fm */
                $isCustomRule = \strpos(\get_class($fm), 'CustomWafRule') !== \false;
                if (!isset($threatToPost['primaryWafRule']) && $isCustomRule) {
                    $threatToPost['primaryWafRule'] = $fm->getIri();
                }
                if ($isCustomRule) {
                    $threatToPost['wafRules'][] = $fm->getId();
                }
            }
            $uniqueAttackParameters[$key] = $threatToPost;
        }
        foreach ($uniqueAttackParameters as $threatsToPost) {
            $fnRes = $this->ipBlockingHelper->recordThreatInSymfony($threatsToPost);
            if ($fnRes) {
                //$decoded = json_decode($fnRes, true);
                //$threatGuid[] = $decoded['@id'];
            } else {
                $this->logger->warning('From WAF Plugin - Threat could not be posted to Symfony');
            }
        }
        return $uniqueAttackParameters;
    }
    /**
     * Loads all the Exception for a site and attaches it to the Expose Manager.
     *
     * @return \Expose\Manager
     */
    private function loadExceptions(Expose\Manager $manager)
    {
        $options = $this->container->get('options');
        $allSiteExceptions = $options->get('exceptions');
        $siteObject = $options->get(SiteSettingsService::FULL_SITE_OBJECT_KEY);
        $globalExceptions = isset($siteObject['globalExceptions']) ? $siteObject['globalExceptions'] : [];
        if (empty($allSiteExceptions) && empty($globalExceptions)) {
            return $manager;
        }
        foreach ($allSiteExceptions as $ex) {
            $manager->setException($ex['parameter']);
        }
        foreach ($globalExceptions as $val) {
            $manager->setException($val);
        }
        return $manager;
    }
    /**
     * Loads all the site WafRules, creates a custom Expose Filter for each and adds it to
     * the existing Filters.
     *
     * @param type $filters
     *
     * @return type
     */
    private function loadWafRules($filters)
    {
        $options = $this->container->get('options');
        $allRules = $options->get('wafRules');
        $allWafRules = \array_filter($allRules, function ($val) {
            return 'waf' == $val['evaluator'];
        });
        require_once 'CustomWafRule.php';
        foreach ($allWafRules as $rule) {
            try {
                $rule = new CustomWafRule($rule);
                $filters->addFilter($rule);
            } catch (\Exception $e) {
                $this->logger->warning('WafRule could not be loaded in WAF Plugin.');
                continue;
            }
        }
        return $filters;
    }
    /**
     * Cleares the default filters for enygma expose. it empties the filter_rules.json file.
     * All the fitlers are loaded as custom filters from waf rules provided by Symfony.
     */
    private function clearDefaultFilters()
    {
        /**@var CacheInterface $options */
        $options = $this->container->get('options');
        if ($options->has('defaultFiltersCleared') && $options->get('defaultFiltersCleared') == 'true') {
            return;
            //do nothing if filters already cleared
        } else {
            $contents = \file_get_contents(\ASTRAROOT . 'vendor/enygma/expose/src/Expose/filter_rules.json');
            $decode = \json_decode($contents, \true);
            $decode['filters'] = [];
            //all waf rules are loaded from symfony. default rules of enygma removed.
            \file_put_contents(\ASTRAROOT . 'vendor/enygma/expose/src/Expose/filter_rules.json', \json_encode($decode));
            $options->set('defaultFiltersCleared', 'true');
            return;
        }
    }
}
/**
 * Description of WafPlugin.
 *
 * @author aditya
 */
\class_alias('AstraPrefixed\\WafPlugin', 'WafPlugin', \false);
