<?php

namespace AstraPrefixed;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\GetAstra\Client\Helper\IpBlockingHelper;
use AstraPrefixed\GetAstra\Client\Helper\UrlHelper;
use AstraPrefixed\GetAstra\Client\Helper\PluginInterface;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
use AstraPrefixed\Psr\Http\Message\ServerRequestInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
/**
 * Description of BotPlugin.
 *
 * @author aditya
 */
class BotPlugin implements PluginInterface
{
    private $container;
    private $urlHelper;
    private $ipBlockingHelper;
    private $commonHelper;
    const GOOGLE_HOSTS = ['googlebot.com', 'google.com', 'googleusercontent.com'];
    const BING_HOSTS = ['search.msn.com'];
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
        return 'BotPlugin';
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
        if (!$request->getAttribute('astraEnabled')) {
            $response = $next($request, $response);
            return $response;
        }
        if ($request->getAttribute('alreadyAllowed')) {
            $response = $next($request, $response);
            return $response;
        }
        $currentIpAddress = $this->urlHelper->getClientIp();
        if (!$currentIpAddress) {
            $this->logger->warning('Visitor IP detected as null in BotPlugin, exiting.');
            $response = $next($request, $response);
            return $response;
        }
        $blockRefId = $this->ipBlockingHelper->generateBlockReferenceId($this->getName());
        $badBot = $this->isBadBot($request);
        if (!\is_null($badBot)) {
            //Bad Bots
            if ($badBot['logOnly'] == \true) {
                // if its a log only waf rule
                $fnRes = $this->recordBadBotThreatInSymfony($request, null, $blockRefId, $badBot);
                // record in Symfony only with monitored status
            } else {
                $ipData = $this->ipBlockingHelper->recordIpBlockingInstance($currentIpAddress, $this->getName(), null, \true);
                //record in GK
                $fnRes = $this->recordBadBotThreatInSymfony($request, $ipData, $blockRefId, $badBot);
                //record in Symfony with blocked status
                if (isset($fnRes)) {
                    $attackParameters = $fnRes;
                } else {
                    $attackParameters = null;
                }
                return $this->ipBlockingHelper->blockPageDataPrep($response, $currentIpAddress, 'Bad Bot Request', $this->getName(), $attackParameters, $blockRefId);
                //show block page
            }
        }
        if ($this->impersonatingGoogleOrBingBot($currentIpAddress, $request)) {
            //Google Bot check
            $ipData = $this->ipBlockingHelper->recordIpBlockingInstance($currentIpAddress, $this->getName(), null, \true);
            $impersonatorBot = $this->getImpersonatorBotWafRule("google");
            $fnRes = $this->recordBadBotThreatInSymfony($request, $ipData, $blockRefId, $impersonatorBot);
            //record in Symfony
            return $this->ipBlockingHelper->blockPageDataPrep($response, $currentIpAddress, 'Impersonating Bot', $this->getName(), null, $blockRefId);
            //show block page
        }
        if ($this->impersonatingGoogleOrBingBot($currentIpAddress, $request, 'bing')) {
            //Bing Bot check
            $ipData = $this->ipBlockingHelper->recordIpBlockingInstance($currentIpAddress, $this->getName(), null, \true);
            $impersonatorBot = $this->getImpersonatorBotWafRule("bing");
            $fnRes = $this->recordBadBotThreatInSymfony($request, $ipData, $blockRefId, $impersonatorBot);
            //record in Symfony
            return $this->ipBlockingHelper->blockPageDataPrep($response, $currentIpAddress, 'Impersonating Bot', $this->getName(), null, $blockRefId);
            //show block page
        }
        $response = $next($request, $response);
        return $response;
    }
    private function isBadBot(ServerRequestInterface $request)
    {
        $userAgent = \strtolower($request->getServerParam('HTTP_USER_AGENT'));
        $options = $this->container->get('options');
        $allRules = $options->get('wafRules');
        $allBadBots = \array_filter($allRules, function ($val) {
            return 'bot' == $val['evaluator'];
        });
        foreach ($allBadBots as $bbRule) {
            $pattern = "/(" . $bbRule['rule'] . ")/mi";
            if (\preg_match($pattern, $userAgent)) {
                return $bbRule;
            }
        }
        return null;
    }
    /**
     * @param type $ipAddress
     * @param type $googleOrBing
     *
     * @return bool False if IP is not pretending to be a bot, true otherwise
     */
    private function impersonatingGoogleOrBingBot($ipAddress, $request, $googleOrBing = 'google')
    {
        $options = $this->container->get('options');
        $siteSettings = $options->get('siteSettings');
        if (!$siteSettings['waf']['verifySearchCrawlers']) {
            //|| 'custom' != $siteSettings['securityLevel']
            return \false;
        }
        $userAgent = \strtolower($request->getServerParam('HTTP_USER_AGENT'));
        $hostName = \gethostbyaddr($ipAddress);
        switch ($googleOrBing) {
            case 'google':
                if (\strpos($userAgent, 'googlebot') === \false) {
                    return \false;
                    //not pretending
                }
                $checkAgainst = $this::GOOGLE_HOSTS;
                break;
            case 'bing':
                if (\strpos($userAgent, 'bingbot') === \false) {
                    return \false;
                    //not pretending
                }
                $checkAgainst = $this::BING_HOSTS;
                break;
            default:
                return \false;
                break;
        }
        $foundHost = \false;
        foreach ($checkAgainst as $checkHost) {
            if (\strpos($hostName, $checkHost) !== \false) {
                $foundHost = \true;
            }
        }
        if (!$foundHost) {
            return \true;
            //pretending
        }
        $checkIp = \gethostbyname($hostName);
        if ($checkIp !== $ipAddress) {
            return \true;
            //pretending
        } else {
            //not pretending so add IP to trusted
            $this->ipBlockingHelper->recordAllowedIpInstance($ipAddress, $this->getName());
            return \false;
        }
    }
    /**
     * fetch waf rule which 
     * will be used as primary waf rule id in case of fake google/bing bot threat post
     * evaluator type of these rules is "plugin"
     * 
     * @param string $botName can be google or bing
     * @return mixed $bbRule WafRule if name matches to $botName param, null otherwise
     */
    private function getImpersonatorBotWafRule($botName)
    {
        $options = $this->container->get('options');
        $allRules = $options->get('wafRules');
        $allBadBots = \array_filter($allRules, function ($val) {
            return 'plugin' == $val['evaluator'];
        });
        foreach ($allBadBots as $bbRule) {
            if ($bbRule['rule'] === $botName) {
                return $bbRule;
            }
        }
        return null;
    }
    private function recordBadBotThreatInSymfony($request, $ipData, $blockRefId, $bbRule = null)
    {
        $siteId = $this->container->get('options')->get('siteId');
        $siteMode = $this->container->get('options')->get('siteSettings')['protectionMode'];
        if ('blocking' == $siteMode) {
            $status = !\is_null($bbRule) && $bbRule['logOnly'] == \true ? 'monitored' : 'blocked';
        } else {
            $status = 'monitored';
        }
        $threatBody = $this->commonHelper->threatPostPrepare($request, $siteId, $this->container, $status);
        $threatBody['attackedParameter'] = $attackParameter = 'SERVER.HTTP_USER_AGENT';
        $threatBody['attackVector'] = \base64_encode($request->getServerParam('HTTP_USER_AGENT', null));
        if (!\is_null($ipData)) {
            $threatBody['expiresAt'] = isset($ipData['blockUntil']) ? $ipData['blockUntil'] : null;
        }
        $threatBody['blockRefId'] = $blockRefId;
        if (!\is_null($bbRule)) {
            $threatBody['primaryWafRule'] = $bbRule['@id'];
        }
        $fnRes = $this->ipBlockingHelper->recordThreatInSymfony($threatBody);
        if (!$fnRes) {
            $this->logger->warning('From Bot Plugin - Threat could not be posted to Symfony');
        }
        return $attackParameter;
    }
}
/**
 * Description of BotPlugin.
 *
 * @author aditya
 */
\class_alias('AstraPrefixed\\BotPlugin', 'BotPlugin', \false);
