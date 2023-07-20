<?php

namespace AstraPrefixed\GetAstra\Client\Controller;

use AstraPrefixed\Psr\Container\ContainerInterface;
class BaseController
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * BaseController constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
