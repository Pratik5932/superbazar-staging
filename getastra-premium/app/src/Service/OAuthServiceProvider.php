<?php

namespace AstraPrefixed\GetAstra\Client\Service;

use AstraPrefixed\Pimple\Container;
use AstraPrefixed\Pimple\ServiceProviderInterface;
class OAuthServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        /** @noinspection PhpParamsInspection */
        $oauthService = new OAuthService($pimple);
        $pimple['oauth'] = function ($c) use($oauthService) {
            return $oauthService;
        };
    }
}
