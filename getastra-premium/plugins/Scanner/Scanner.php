<?php

/**
 * This file is part of the Astra Security Suite.
 *
 *  Copyright (c) 2019 (https://www.getastra.com/)
 *
 *  For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
/**
 * Malware Scanner Plugin.
 *
 * @author HumansofAstra-WZ <help@getastra.com>
 * @date   2019-03-10
 */
namespace AstraPrefixed\GetAstra\Plugins;

use AstraPrefixed\Curl\MultiCurl;
//use GetAstra\Plugin;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\ConfigHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\OptionsHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\StatusHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Migrations\ScannerSchema;
//use Interop\Container\ContainerInterface;
use AstraPrefixed\GetAstra\Plugins\Scanner\Services\ScanEngine;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Slim\Http\Request;
if (!\defined('ASTRAROOT')) {
    exit;
}
class Scanner
{
    protected $container;
    protected $options;
    public static function getSubscribedEvents()
    {
        return ['plugin.loaded' => 'onPluginsLoadEvent'];
    }
    public function onPluginsLoadEvent($event)
    {
    }
    public function isMiddlewareBasedPlugin() : bool
    {
        return \false;
    }
    public function __construct(ContainerInterface $container)
    {
        //parent::__construct($container);
        $this->container = $container;
        $this->options = $container->get('options');
        //$this->setVersion('7.0.1');
        $this->fixMemoryLimits();
        $schema = new ScannerSchema($container);
        if (!$schema->exists()) {
            $schema->runInstall();
        }
        $oauthService = $container->get('oauth');
        //$jwt = $container->get('options')->get('accessToken'); //can directly fetch option or use oauthService
        $jwt = $oauthService->getTokenObject(\false);
        if ($jwt) {
            OptionsHelper::set('jwtToken', $jwt->getToken());
            OptionsHelper::set('jwtRefreshToken', $jwt->getRefreshToken());
        }
        //echo'<pre>';var_dump($jwt->getToken());exit;
        //$this->startScanIfRequired();
    }
    /*
     protected function startScanIfRequired()
     {
     $remoteScanRequest = (int)ConfigHelper::get('scanRequireRemoteStart', 0);
     ConfigHelper::set('scanRequireRemoteStart', 0);
    
     $math = (time() - strtotime(date('m/d/Y H:i:s', $remoteScanRequest))) < 600; //Request lasts for 10 minutes
     if ($remoteScanRequest && $math) {
    
     //$engine = new ScanEngine();
     StatusHelper::add(10, 'info', "Starting the remote scan");
     //$engine->go();
    
     $request = $this->container->get('request');
    
     try {
     $cronURL = $request->getUri();
     ConfigHelper::set('scanStartAttemptscanStartAttempt', time());
    
     $multiCurl = new MultiCurl();
     $multiCurl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
     $multiCurl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
     //$multiCurl->setRetry(3);
     $multiCurl->setHeader('Referer', false);
    
     $multiCurl->setOpt(CURLOPT_TIMEOUT_MS, 10);
     //$multiCurl->setTimeout(0.01);
     //$multiCurl->setTimeout(1);
    
     $multiCurl->addGet($cronURL);
    
     //TODO Notify SF about Scan Start
     } catch (\Exception $e) {
     ConfigHelper::set('lastScanCompleted', $e->getMessage());
     ConfigHelper::set('lastScanFailureType', 'scanner.callbackfailed');
     return false;
     }
    
     }
    
     }
    */
    protected function fixMemoryLimits()
    {
        $maxMemory = @\ini_get('memory_limit');
        $last = \strtolower(\substr($maxMemory, -1));
        $maxMemory = (int) $maxMemory;
        if ('g' == $last) {
            $maxMemory = $maxMemory * 1024 * 1024 * 1024;
        } elseif ('m' == $last) {
            $maxMemory = $maxMemory * 1024 * 1024;
        } elseif ('k' == $last) {
            $maxMemory = $maxMemory * 1024;
        }
        if ($maxMemory < 134217728 && $maxMemory > 0) {
            if (\false === \strpos(\ini_get('disable_functions'), 'ini_set')) {
                @\ini_set('memory_limit', '128M');
            }
        }
    }
}
