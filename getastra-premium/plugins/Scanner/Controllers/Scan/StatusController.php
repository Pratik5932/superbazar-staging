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
namespace AstraPrefixed\GetAstra\Plugins\Scanner\Controllers\Scan;

use AstraPrefixed\GetAstra\Plugins\Scanner\Models\ScanStatus;
use AstraPrefixed\GetAstra\Plugins\Scanner\Transformers\ScanStatusTransformer;
//use Interop\Container\ContainerInterface;
use AstraPrefixed\League\Fractal\Resource\Collection;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Slim\Http\Request;
use AstraPrefixed\Slim\Http\Response;
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
class StatusController
{
    /** @var \Illuminate\Database\Capsule\Manager */
    protected $db;
    ///** @var \GetAstra\Services\Auth\Auth */
    //protected $auth;
    /** @var \League\Fractal\Manager */
    protected $fractal;
    /** @var \Psr\Container\ContainerInterface; */
    protected $container;
    private $commonHelper;
    /**
     * Scan Status constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        //$this->auth = $container->get('auth');
        $this->fractal = $container->get('fractal');
        $this->db = $container->get('db');
        $this->container = $container;
        $this->commonHelper = new CommonHelper();
    }
    /**
     * Return List of Options.
     *
     * @return \Slim\Http\Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $builder = ScanStatus::query()->limit(100)->orderBy('id', 'DESC');
        if ($limit = $request->getParam('limit')) {
            $builder->limit($limit);
        }
        if ($offset = $request->getParam('offset')) {
            $builder->offset($offset);
        }
        $statusCount = $builder->count();
        $status = $builder->get();
        $data = $this->fractal->createData(new Collection($status, new ScanStatusTransformer()))->toArray();
        return $response->withJson(['status' => $data['data'], 'statusCount' => $statusCount]);
    }
    public function destroy(Request $request, Response $response, array $args)
    {
        ScanStatus::query()->truncate();
        return $response->withJson([], 200);
    }
}
