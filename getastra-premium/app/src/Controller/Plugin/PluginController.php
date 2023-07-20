<?php

namespace AstraPrefixed\GetAstra\Client\Controller\Plugin;

use AstraPrefixed\GetAstra\Api\Client\Api\PluginApi;
use AstraPrefixed\GetAstra\Api\Client\Api\PluginVersionApi;
use AstraPrefixed\GetAstra\Client\Tclient\Configuration;
use AstraPrefixed\GetAstra\Client\Service\OAuthService;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use AstraPrefixed\Psr\SimpleCache\InvalidArgumentException;
use AstraPrefixed\Slim\Http\Request;
use AstraPrefixed\Slim\Http\Response;
use AstraPrefixed\splitbrain\PHPArchive\Tar;
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
/**
 * @todo still incomplete
 * @deprecated - Please give correct namespace above for PluginVersionApi and PluginApi before using this class
 */
class PluginController
{
    /**
     * @var CacheInterface
     */
    private $options;
    /**
     * @var PluginApi
     */
    private $pluginApi;
    /**
     * @var PluginVersionApi
     */
    private $pluginVersionApi;
    /**
     * @var OAuthService
     */
    private $oauthService;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $pluginPath;
    private $commonHelper;
    private $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->commonHelper = new CommonHelper();
        $this->options = $container->get('options');
        $this->oauthService = $container->get('oauth');
        $this->logger = $container->get('logger');
        $this->pluginPath = $container->get('settings')['plugins']['path'];
        $oauthClientId = $container->get('options')->get('oauthClientId');
        $oauthClientSecret = $container->get('options')->get('oauthClientSecret');
        $apiUrl = \substr($container->get('settings')['relay']['api_url_https'], 0, -1);
        $tokenObject = $this->oauthService->getTokenObject();
        if (isset($tokenObject, $oauthClientId, $oauthClientSecret, $apiUrl)) {
            $apiConfiguration = (new Configuration())->setAccessToken($tokenObject->getToken());
            $apiConfiguration->setHost($apiUrl)->setDebug(\false)->setUsername($oauthClientId)->setPassword($oauthClientSecret);
            $this->pluginApi = new PluginApi(null, $apiConfiguration);
            $this->pluginVersionApi = new PluginVersionApi(null, $apiConfiguration);
        } else {
            $this->pluginApi = null;
            $this->pluginVersionApi = null;
        }
    }
    public function show(Request $request, Response $response)
    {
        try {
            $plugins = $this->options->get('plugins');
            //return $response->withStatus(200)->withJson("yo");
            return $response->withStatus(200)->withJson($plugins);
        } catch (InvalidArgumentException $e) {
            return $response->withStatus(500)->withJson(['error' => $e->getMessage()]);
        }
    }
    public function add(Request $request, Response $response)
    {
        try {
            if ($pluginId = $request->getParsedBodyParam('id')) {
                if (null !== $this->pluginApi) {
                    $plugin = $this->pluginApi->getPluginItem($pluginId);
                    $pluginFile = $this->pluginApi->getFilePluginItem($pluginId);
                    $pluginVersionIri = $plugin->getVersions()[0];
                    $pluginVersionIri = \explode('/', $pluginVersionIri);
                    $pluginVersionId = $pluginVersionIri[\count($pluginVersionIri) - 1];
                    $pluginVersion = $this->pluginVersionApi->getPluginVersionItem($pluginVersionId);
                    $storedPlugins = $this->options->get('plugins') ?? [];
                    $pluginTarFile = new Tar();
                    $pluginTarFile->open($pluginFile->getPathname());
                    $pluginTarFile->extract($this->pluginPath);
                    $storedPlugins[$pluginVersion->getName()] = ['fqcn' => $pluginVersion->getFqcn(), 'path' => $this->pluginPath . $pluginVersion->getName() . \DIRECTORY_SEPARATOR . $pluginVersion->getName() . '.php', 'active' => \false];
                    return $response->withStatus(200)->withJson(['status' => $pluginVersion->getName() . ' installed']);
                } else {
                    $this->logger->error('Unable to log into Astra API');
                    return $response->withStatus(500)->withJson(['error' => 'Unable to log into Astra API']);
                }
            } else {
                return $response->withStatus(400)->withJson(['error' => 'Please pass ID in request body']);
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
