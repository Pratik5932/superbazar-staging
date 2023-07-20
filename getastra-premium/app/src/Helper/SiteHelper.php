<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Helper;

use AstraPrefixed\GetAstra\Client\Tclient\Configuration;
use AstraPrefixed\GetAstra\Client\Tclient\SiteApi;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
class SiteHelper
{
    private $container;
    /**
     * @var SiteApi
     */
    private $siteApi;
    private $siteId;
    private $oauthService;
    /**
     * @var LoggerInterface
     */
    private $logger;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->oauthService = $this->container->get('oauth');
        $this->logger = $this->container->get('logger');
    }
    /**
     * @deprecated - No longer used
     *
     * @return bool False if site doesn't exists and this has been verified with certainty. True otherwise
     */
    public function checkIfSiteExists()
    {
        $tokenObject = $this->oauthService->getTokenObject();
        $oauthClientId = $this->container->get('options')->get('oauthClientId');
        $oauthClientSecret = $this->container->get('options')->get('oauthClientSecret');
        $apiUrl = \substr($this->container->get('settings')['relay']['api_url_https'], 0, -1);
        $this->siteId = $this->container->get('settings')['app']['siteId'];
        if (!$oauthClientId) {
            \var_dump('OauthClientId not found');
            exit;
        }
        if (!$oauthClientSecret) {
            \var_dump('OauthClientSecret not found');
            exit;
        }
        if (!$apiUrl) {
            \var_dump('API url not found');
            exit;
        }
        if (!$this->siteId) {
            \var_dump('Site ID not found');
            exit;
        }
        //Initialize Site API
        if ($tokenObject) {
            $apiConfiguration = (new Configuration())->setAccessToken($tokenObject->getToken());
            $apiConfiguration->setHost($apiUrl)->setDebug(\false)->setUsername($oauthClientId)->setPassword($oauthClientSecret);
            try {
                $this->siteApi = new SiteApi(null, $apiConfiguration);
                //this will throw exception if site not found
            } catch (\Exception $e) {
                //only when an exception is thrown, we can say with certainty that site doesn't exist.
                //otherwise return true;
                return 'site not found for sure';
                return \false;
            }
            return '100% sure site found';
            return \true;
            //site found with certainity
        } else {
            //if site API could not be initialized because of empty token and $this->siteApi = null
            //we cannot verify with certainity if the site exists or not on Symfony side
            //so we cannot return false because false means that site doesn't exists for sure.
            return 'not sure if site found or not because token not found';
            return \true;
        }
    }
}
