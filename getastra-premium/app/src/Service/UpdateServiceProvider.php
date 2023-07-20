<?php

namespace AstraPrefixed\GetAstra\Client\Service;

use AstraPrefixed\Pimple\Container;
use AstraPrefixed\Pimple\ServiceProviderInterface;
class UpdateServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $updateService = new UpdateService($pimple);
        $pimple['update'] = function ($c) use($updateService) {
            return $updateService;
        };
    }
}
