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
 * @date   2019-03-17
 */
namespace AstraPrefixed\GetAstra\Client\Controller\Option;

use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use AstraPrefixed\Psr\SimpleCache\InvalidArgumentException;
use AstraPrefixed\Slim\Http\Request;
use AstraPrefixed\Slim\Http\Response;
class OptionController
{
    /**
     * @var CacheInterface
     */
    private $options;
    private $container;
    /**
     * @var CommonHelper
     */
    private $commonHelper;
    /**
     * UserController constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->options = $container->get('options');
        $this->commonHelper = new CommonHelper();
    }
    /**
     * Return a single Option to get option endpoint.
     *
     * @return Response
     */
    public function show(Request $request, Response $response, array $args)
    {
        try {
            if ($option = $this->options->get($args['name'])) {
                return $response->withJson(['value' => $option]);
            } else {
                return $response->withStatus(404);
            }
        } catch (InvalidArgumentException $e) {
            return $response->withStatus(400)->withJson(['error' => $e->getMessage()]);
        }
    }
    /**
     * Create and store a new Option.
     *
     * @return Response
     */
    public function store(Request $request, Response $response, array $args)
    {
        try {
            if ($option = $this->options->get($args['name'])) {
                return $response->withStatus(405);
            } else {
                $value = $request->getParsedBodyParam('value');
                $this->options->set($args['name'], $value);
                return $response->withStatus(200);
            }
        } catch (InvalidArgumentException $e) {
            return $response->withStatus(400)->withJson(['error' => $e->getMessage()]);
        }
    }
    /**
     * Update Option Endpoint.
     *
     * @return Response
     */
    public function update(Request $request, Response $response, array $args)
    {
        try {
            if ($option = $this->options->get($args['name'])) {
                $this->options->set($args['name'], $request->getParsedBodyParam('value'));
                return $response->withStatus(200);
            } else {
                return $response->withStatus(405);
            }
        } catch (InvalidArgumentException $e) {
            return $response->withStatus(400)->withJson(['error' => $e->getMessage()]);
        }
    }
    /**
     * Delete Option Endpoint.
     *
     * @return Response
     */
    public function destroy(Request $request, Response $response, array $args)
    {
        try {
            if ($option = $this->options->get($args['name'])) {
                $this->options->delete($args['name']);
                return $response->withStatus(200);
            } else {
                return $response->withStatus(405);
            }
        } catch (InvalidArgumentException $e) {
            return $response->withStatus(400)->withJson(['error' => $e->getMessage()]);
        }
    }
    // public function try(Request $request, Response $response, array $args){
    //     $intervalObj = new \DateInterval("PT1M");
    //     $this->options->set('hello','world',$intervalObj);
    // }
}
