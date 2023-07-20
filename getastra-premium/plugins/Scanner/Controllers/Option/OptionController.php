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
namespace AstraPrefixed\GetAstra\Plugins\Scanner\Controllers\Option;

use AstraPrefixed\GetAstra\Plugins\Scanner\Models\Option;
use AstraPrefixed\GetAstra\Plugins\Scanner\Transformers\OptionTransformer;
//use Interop\Container\ContainerInterface;
use AstraPrefixed\League\Fractal\Resource\Collection;
use AstraPrefixed\League\Fractal\Resource\Item;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Respect\Validation\Validator as v;
use AstraPrefixed\Slim\Http\Request;
use AstraPrefixed\Slim\Http\Response;
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
class OptionController
{
    /** @var \GetAstra\Validation\Validator */
    protected $validator;
    /** @var \Illuminate\Database\Capsule\Manager */
    protected $db;
    ///** @var \GetAstra\Services\Auth\Auth */
    //protected $auth;
    /** @var \League\Fractal\Manager */
    protected $fractal;
    /** @var Psr\Container\ContainerInterface */
    protected $container;
    private $commonHelper;
    /**
     * UserController constructor.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @internal param $auth
     */
    public function __construct(ContainerInterface $container)
    {
        //$this->auth = $container->get('auth');
        $this->fractal = $container->get('fractal');
        $this->validator = $container->get('validator');
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
        //var_dump('next');exit;
        $requestUserId = 1;
        $requestUser = null;
        $builder = Option::query()->limit(100);
        if ($limit = $request->getParam('limit')) {
            $builder->limit($limit);
        }
        if ($offset = $request->getParam('offset')) {
            $builder->offset($offset);
        }
        $optionsCount = $builder->count();
        $options = $builder->get();
        $data = $this->fractal->createData(new Collection($options, new OptionTransformer()))->toArray();
        return $response->withJson(['options' => $data['data'], 'optionsCount' => $optionsCount]);
    }
    /**
     * Return a single Option to get option endpoint.
     *
     * @return \Slim\Http\Response
     */
    public function show(Request $request, Response $response, array $args)
    {
        $option = Option::query()->where('name', $args['name'])->firstOrFail();
        $data = $this->fractal->createData(new Item($option, new OptionTransformer()))->toArray();
        return $response->withJson(['option' => $data]);
    }
    /**
     * Create and store a new Option.
     *
     * @return Response
     */
    public function store(Request $request, Response $response)
    {
        $this->validator->validateArray($data = $request->getParam('option'), ['name' => v::notEmpty()->existsInTable($this->db->table('options'), 'name'), 'val' => v::notEmpty(), 'group' => v::notEmpty(), 'autoload' => v::notEmpty()->in(['yes', 'no'])]);
        if ($this->validator->failed()) {
            return $response->withJson(['errors' => $this->validator->getErrors()], 422);
        }
        //echo '<pre>';
        $option = new Option($request->getParam('option'));
        $option->save();
        //var_dump($option);exit;
        $data = $this->fractal->createData(new Item($option, new OptionTransformer()))->toArray();
        //var_dump($option);exit;
        return $response->withJson(['option' => $data]);
    }
    /**
     * Update Option Endpoint.
     *
     * @return \Slim\Http\Response
     */
    public function update(Request $request, Response $response, array $args)
    {
        $option = Option::query()->where('name', $args['name'])->first();
        if (\is_null($option)) {
            return $response->withJson(['errors' => ['Unable to find the option you are wanting to update']], 422);
        }
        $this->validator->validateArray($data = $request->getParam('option'), ['name' => v::optional(v::notEmpty()), 'group' => v::optional(v::notEmpty()), 'autoload' => v::optional(v::notEmpty()->in(['yes', 'no']))]);
        if ($this->validator->failed()) {
            return $response->withJson(['errors' => $this->validator->getErrors()], 422);
        }
        $params = $request->getParam('option', []);
        $option->update(['name' => isset($params['name']) ? $params['name'] : $option->name, 'val' => isset($params['val']) ? $params['val'] : $option->val, 'group' => isset($params['group']) ? $params['group'] : $option->group, 'autoload' => isset($params['autoload']) ? $params['autoload'] : $option->autoload]);
        $data = $this->fractal->createData(new Item($option, new OptionTransformer()))->toArray();
        return $response->withJson(['option' => $data]);
    }
    /**
     * Delete Option Endpoint.
     *
     * @return \Slim\Http\Response
     */
    public function destroy(Request $request, Response $response, array $args)
    {
        $option = Option::query()->where('name', $args['name'])->first();
        if (!\is_null($option)) {
            $option->delete();
        }
        return $response->withJson([], 200);
    }
}
