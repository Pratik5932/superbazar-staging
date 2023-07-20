<?php

namespace AstraPrefixed\GetAstra\Client\Controller\Status;

use AstraPrefixed\League\OAuth2\Client\Token\AccessToken;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use AstraPrefixed\Slim\Http\Request;
use AstraPrefixed\Slim\Http\Response;
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use PDO;
use AstraPrefixed\GetAstra\Client\Service\LogService;
use AstraPrefixed\GetAstra\Client\Service\UpdateService;
class StatusController
{
    /**
     * @var CacheInterface
     */
    private $options;
    private $commonHelper;
    private $container;
    private const STATUS_KEYS = [];
    private const STATUS_EXIST_KEYS = ['oauthClientId', 'siteId'];
    /**
     * @var LogService
     */
    private $logService;
    /**
     * @var UpdateService
     */
    private $updateService;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->options = $container->get('options');
        $this->commonHelper = new CommonHelper();
        $this->logService = $container->get('log2');
        $this->updateService = $container->get('update');
    }
    /**
     * @todo:future to do apache nginx info, and loaded modules info
     * Gatekeeper Health Check API
     */
    public function show(Request $request, Response $response)
    {
        $data = (require ASTRAROOT . 'dependencyChecker.php');
        if ($accessToken = $this->options->get('accessToken')) {
            $data['meta']['tokenExists'] = \true;
            $data['meta']['tokenExpired'] = (new AccessToken($accessToken))->hasExpired();
        } else {
            $data['meta']['tokenExists'] = \false;
            $data['meta']['tokenExpired'] = \false;
        }
        foreach (self::STATUS_KEYS as $STATUS_KEY) {
            $data['meta'][$STATUS_KEY] = $this->options->get($STATUS_KEY);
        }
        foreach (self::STATUS_EXIST_KEYS as $STATUS_EXIST_KEY) {
            $data['meta'][$STATUS_EXIST_KEY] = null !== $this->options->get($STATUS_EXIST_KEY);
        }
        $data['meta']['currentPhpVersion'] = \phpversion();
        $data['meta']['docRoot'] = ASTRA_DOC_ROOT;
        $data['meta']['storagePath'] = ASTRA_STORAGE_ROOT;
        $data['meta']['gkDisconnectLogs'] = $this->logService->getStackTrace($this->logService::GK_DISCONNECT_LOG_KEY);
        $data['meta']['workerVersion'] = GATEKEEPER_VERSION;
        $data['meta']['isUpdaterDirectoryWritable'] = $this->updaterService->isUpdateExtractDirWritable();
        $data['meta']['isAstraRootWritable'] = \is_writable(ASTRAROOT);
        return $response->withStatus(200)->withJson($data);
    }
}
