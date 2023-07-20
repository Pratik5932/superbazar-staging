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
 * @date   2019-04-05
 */
namespace AstraPrefixed\GetAstra\Client\Helper\Cms;

class WordpressHelper
{
    private $path;
    private $version;
    private $locale;
    public function __construct($path)
    {
        //If scan was started from within WP, we'll have to load the global variable
        if (isset($GLOBALS['wp_version'])) {
            global $wp_local_package;
            global $wp_version;
        }
        $this->path = $path;
        include_once $this->path . 'wp-includes/version.php';
        $this->locale = isset($wp_local_package) ? $wp_local_package : 'en_US';
        $this->version = isset($wp_version) ? $wp_version : \false;
    }
    public function getName()
    {
        return 'wordpress';
    }
    public function getVersion()
    {
        return $this->version;
    }
    public function getLocale()
    {
        return $this->locale;
    }
}
