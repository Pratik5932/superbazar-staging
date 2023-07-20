<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Service;

use AstraPrefixed\Pimple\Container;
use AstraPrefixed\Pimple\ServiceProviderInterface;
/**
 * Description of SiteSettingsServiceProvider.
 *
 * @author aditya
 */
class SiteSettingsServiceProvider implements ServiceProviderInterface
{
    //put your code here
    public function register(Container $pimple)
    {
        $settingsService = new SiteSettingsService($pimple);
        $pimple['siteSettings'] = function ($c) use($settingsService) {
            return $settingsService;
        };
    }
}
