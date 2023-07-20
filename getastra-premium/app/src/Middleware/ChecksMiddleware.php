<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Middleware;

use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\GetAstra\Client\Helper\IpBlockingHelper;
use AstraPrefixed\GetAstra\Client\Helper\SiteHelper;
use AstraPrefixed\GetAstra\Client\Service\SiteSettingsService;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
use AstraPrefixed\Psr\Http\Message\ServerRequestInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
use AstraPrefixed\GetAstra\Client\Helper\QueueHelper;
use AstraPrefixed\GetAstra\Client\Service\UpdateService;
use AstraPrefixed\Psr\Http\Message\StreamInterface;
use AstraPrefixed\GuzzleHttp\Client;
/**
 * Description of Checks.
 *
 * @author aditya
 */
class ChecksMiddleware
{
    private $ipBlockingHelper;
    private $commonHelper;
    private $siteHelper;
    /**
     * @var UpdateService
     */
    private $updateService;
    /**
     * @var LoggerInterface
     */
    private $logger;
    private $queueHelper;
    private $options;
    private const ASTRA_VERSION_OPTIONS_KEY = 'astraPluginVersion';
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->options = $container->get('options');
        $this->updateService = $container->get('update');
        $this->queueHelper = new QueueHelper($container);
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
        $view = $this->container->get('view');
        //Execute Queue Tasks
        $this->queueHelper->executeTask();
        //Patch Updater Logs if GK recently updated to new version
        $this->updateService->patchUpdaterMessagesToSymfony();
        //csrf check
        if (\false === $request->getAttribute('csrf_status')) {
            // display suitable csrf error here
            return $view->render($response, 'errorPage.php', ['error' => 'CSRF error, please reload the page.']);
        }
        //check if options populated, if not skip everything
        $this->commonHelper = new CommonHelper();
        if (!$this->commonHelper->checkIfOptionsPopulated($this->options)) {
            $response = $next($request, $response);
            return $response;
        }
        $this->ipBlockingHelper = new IpBlockingHelper($this->container);
        //$options = $this->container->get('options');
        //if Astra is disabled attach the appropriate setting with request so application can react accordingly
        if (!$this->options) {
            $request = $request->withAttribute('astraEnabled', \false);
            $response = $next($request, $response);
            return $response;
        }
        $siteSettingsArray = $this->options->get('siteSettings');
        if (!$siteSettingsArray) {
            $request = $request->withAttribute('astraEnabled', \false);
            $response = $next($request, $response);
            return $response;
        }
        //updating options if cache expired
        $this->checkIfOptionsCacheExpiryAndUpdate();
        //check if ASTRA plugin version number is in sync
        $this->checkIfAstraVersionInSync();
        //initializing required keys in cache to store
        if (!$this->options->has($this->ipBlockingHelper::IP_LOG_KEY)) {
            $this->options->set($this->ipBlockingHelper::IP_LOG_KEY, []);
        }
        if (!$this->options->has($this->ipBlockingHelper::IP_ALLOWED_KEY)) {
            $this->options->set($this->ipBlockingHelper::IP_ALLOWED_KEY, []);
        }
        if (!$this->options->has($this->ipBlockingHelper::BOOSTER_LOG_KEY)) {
            $this->options->set($this->ipBlockingHelper::BOOSTER_LOG_KEY, []);
        }
        $request = $request->withAttribute('astraEnabled', $siteSettingsArray['protectionEnabled']);
        $response = $next($request, $response);
        $protectionMode = $siteSettingsArray['protectionMode'];
        //if any module has requested to show block page for current request,
        //by attaching the block header in response object
        //then render it here
        if ($response->hasHeader($this->ipBlockingHelper::BLOCK_PAGE) && 'monitoring' !== $protectionMode) {
            $newResponse = $response->withStatus(403);
            if (!\headers_sent()) {
                $newResponse->withHeader('X-XSS-Protection', '1; mode=block');
                $newResponse->withHeader('X-Frame-Options', 'deny');
                $newResponse->withHeader('X-Content-Type-Options', 'nosniff');
                $newResponse->withHeader('X-LiteSpeed-Cache-Control', 'no-cache');
                $newResponse->withHeader('Expires', 'Tue, 03 Jul 2001 06:00:00 GMT');
                $newResponse->withHeader('Last-Modified', \gmdate('D, d M Y H:i:s') . ' GMT');
                $newResponse->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
                $newResponse->withHeader('Cache-Control', 'post-check=0, pre-check=0", false');
                $newResponse->withHeader('Pragma', 'no-cache');
                $newResponse->withHeader('Connection', 'close');
            }
            if (\file_exists($this->container->get('settings')['app']['customBlockPagePath'])) {
                return $view->render($newResponse, 'customBlockPage.php');
            }
            if ($response->hasHeader($this->ipBlockingHelper::THREAT_POSTED_URL)) {
                $blockPageData = $response->getHeader($this->ipBlockingHelper::THREAT_POSTED_URL);
                return $view->render($newResponse, 'blockPage.php', ['visitorIp' => $blockPageData[0] ?? null, 'blockReason' => $blockPageData[1] ?? null, 'blockedByModule' => $blockPageData[2] ?? null, 'attackParameters' => $blockPageData[3] ?? null, 'blockRefId' => $blockPageData[4] ?? null, 'domain' => $blockPageData[5] ?? null]);
            } else {
                return $view->render($newResponse, 'blockPage.php');
            }
        } elseif ($response->hasHeader($this->ipBlockingHelper::BLOCK_PAGE)) {
            //its blocked, but monitoring mode is there so site should load normally
            //which means slim app will redirect the control to CMS
            //therefore setting 301 status code
            //if its anyother status code like 200,403,401,201 then it will get handled by GK instead which we want to avoid
            //PLEASE DONT REMOVE THIS CODE, ITS NECESSARY TO NOT SHOW A BLANK PAGE.
            $path = (string) $request->getUri()->getPath();
            $isApi = \false !== \strpos($path, 'api');
            if ($isApi) {
                //if astraApi gets an attack, and waf is in monitoring mode.
                return $response->withStatus(403)->withAddedHeader('X-Halt-Request', \true)->withJson(['value' => 'Unauthorized']);
            } else {
                //if site gets an attack, and waf is in monitoring mode.
                return $response->withStatus(301);
            }
        }
        return $response;
    }
    /**
     * Checks the CACHE_EXPIRY_KEY entry in siteSettings to see if the cache expiry date has passed, if yes
     * then that means siteOptions, wafRules, ipRules, boosters, siteObject, exceptions need to be updated
     * before GK can proceed forward.
     *
     * @return void
     */
    private function checkIfOptionsCacheExpiryAndUpdate()
    {
        $cacheExpiry = $this->options->get(SiteSettingsService::CACHE_EXPIRY_KEY, null);
        if (!$cacheExpiry) {
            $this->logger->error('Cache Expiry Key missing, cache will not be refreshed');
            return;
        }
        $currentTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateExpiry = \DateTime::createFromFormat('Y-m-d\\TH:i:sP', $cacheExpiry);
        // because `c` format doesn't work with createFromFormat
        if ($dateExpiry < $currentTime) {
            $this->options->set(SiteSettingsService::CACHE_EXPIRY_KEY, (new \DateTime(SiteSettingsService::OPTIONS_CACHE_EXPIRY, new \DateTimeZone('UTC')))->format('c'));
            $class = new SiteSettingsService($this->container);
            $class->saveSiteSettingsLocally(\false, \false);
        }
        return;
    }
    private function checkIfAstraVersionInSync()
    {
        if (!$this->options->has(self::ASTRA_VERSION_OPTIONS_KEY)) {
            // set version if not already set
            $this->options->set(self::ASTRA_VERSION_OPTIONS_KEY, GATEKEEPER_VERSION);
        } else {
            // check if current version in constant is same as options
            $currentVersionInOptions = $this->options->get(self::ASTRA_VERSION_OPTIONS_KEY);
            if ($currentVersionInOptions == GATEKEEPER_VERSION) {
                return;
            } else {
                $this->options->set(self::ASTRA_VERSION_OPTIONS_KEY, GATEKEEPER_VERSION);
            }
        }
        // sync all options by cache expiry
        $newCacheExpiry = (new \DateTime('-1 day', new \DateTimeZone('UTC')))->format('c');
        $this->options->set($this->container->get('siteSettings')::CACHE_EXPIRY_KEY, $newCacheExpiry);
        // send request to symfony for patching workerVersion
        $apiUrl = \substr($this->container->get('settings')['relay']['api_url_https'], 0, -1);
        $siteId = $this->options->get('siteId');
        $token = $this->container->get('oauth')->getTokenObject()->getToken();
        if (empty($apiUrl) || empty($siteId) || empty($token)) {
            $this->logger->warning('Not able to sync latest plugin version to Symfony APIs, due to missing token/siteId/apiUrl');
            return;
        }
        $url = "{$apiUrl}/api/waf/sites/{$siteId}";
        $config = [
            'verify' => \false,
            //ssl verification disabled
            'json' => ['workerVersion' => GATEKEEPER_VERSION],
        ];
        $headers = ['request-target' => "patch {$url}", 'date' => \date('Y-m-d H:i:s'), 'content-type' => 'application/merge-patch+json', 'Authorization' => "Bearer {$token}", 'User-Agent' => 'GetAstra.com GK ' . (\defined('GATEKEEPER_VERSION') ? GATEKEEPER_VERSION : '[Unknown version]')];
        $client = new Client($config);
        $guzzleRequest = new \AstraPrefixed\GuzzleHttp\Psr7\Request('PATCH', $url, $headers);
        try {
            $client->send($guzzleRequest);
        } catch (\Throwable $e) {
            $this->logger->warning('Not able to sync latest plugin version to Symfony APIs');
        }
    }
}
