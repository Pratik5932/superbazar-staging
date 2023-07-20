<?php

namespace AstraPrefixed\GetAstra\Client\Service;

use AstraPrefixed\Pimple\Container;
use AstraPrefixed\Pimple\ServiceProviderInterface;
class LogServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        /** @noinspection PhpParamsInspection */
        $log2Service = new LogService($pimple);
        $pimple['log2'] = function ($c) use($log2Service) {
            return $log2Service;
        };
    }
}
