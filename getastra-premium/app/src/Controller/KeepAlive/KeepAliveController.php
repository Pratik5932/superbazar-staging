<?php

namespace AstraPrefixed\GetAstra\Client\Controller\KeepAlive;

use AstraPrefixed\GetAstra\Client\Helper\UrlHelper;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use AstraPrefixed\Slim\Http\Request;
use AstraPrefixed\Slim\Http\Response;
class KeepAliveController
{
    /**
     * @var CacheInterface
     */
    private $options;
    public function __construct(ContainerInterface $container)
    {
        $this->options = $container->get('options');
    }
    public function keepAlive(Request $request, Response $response)
    {
        $requestPrimaryKey = $request->getParsedBodyParam('primaryKey');
        $serverKeys = $this->options->get('serverKeys');
        if ($requestPrimaryKey !== $serverKeys['primary']) {
            $serverKeys['primary'] = $requestPrimaryKey;
            $this->options->set('serverKeys', $serverKeys);
        }
        return $response->withStatus(200)->withJson(['clientUrl' => UrlHelper::getCurrentUri()]);
    }
}
