<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Twitter;

use Magento\Framework\View\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\Store;
use \Magento\Framework\Url;
use Magento\Framework\HTTP\ZendClient;
use Webkul\SocialSignup\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\Exception\LocalizedException;

/**
 * TwiiterClient class of Twitter
 */
class TwitterClient
{
    const REDIRECT_URI_ROUTE = 'socialsignup/twitter/connect';
    const REQUEST_TOKEN_URI_ROUTE = 'socialsignup/twitter/request';

    const OAUTH_URI = 'https://api.twitter.com/oauth';
    const OAUTH2_SERVICE_URI = 'https://api.twitter.com/1.1';

    protected $_clientId = null;
    protected $_clientSecret = null;
    protected $_redirectUri = null;
    protected $_client = null;
    protected $_token = null;
    protected $_protocol = "http";

    /**
     * @var Store
     */
    protected $_store;

    /**
     * @var Url
     */
    protected $_url;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;
    
    /**
     * helper
     * @var [type]
     */
    protected $_helper;

    /**
     * @param Data             $helper
     * @param Generic          $session
     * @param Store            $store
     * @param Context          $context
     * @param ManagerInterface $messageManager
     * @param Url              $url
     */
    public function __construct(
        Data $helper,
        Generic $session,
        Store $store,
        Context $context,
        ManagerInterface $messageManager,
        Url $url
    ) {
    
        $this->_context = $context;
        $this->_session = $session;
        $this->_messageManager = $messageManager;
        $this->_helper = $helper;
        $this->_store = $store;
        $this->_url = $url;
    }

    /**
     * set parameters
     */
    public function setParameters()
    {
        if (($this->isEnabled = $this->_isEnabled())) {
            $this->_clientId = $this->_getClientId();
            $this->_clientSecret = $this->_getClientSecret();
            
            $isSecure = $this->_store->isCurrentlySecure();
            if ($isSecure) {
                $this->_protocol = "https";
            }
            $this->_redirectUri = $this->_url->sessionUrlVar(
                $this->_url->getUrl(
                    self::REDIRECT_URI_ROUTE,
                    [
                        '_secure'=>true
                        ]
                )
            );
            $this->_client = new \Zend_Oauth_Consumer(
                [
                    'callbackUrl' => $this->_redirectUri,
                    'siteUrl' => self::OAUTH_URI,
                    'authorizeUrl' => self::OAUTH_URI.'/authenticate',
                    'consumerKey' => $this->_clientId,
                    'consumerSecret' => $this->_clientSecret
                ]
            );
        }
    }

    /**
     * check status
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool) $this->isEnabled;
    }

    /**
     * get client id
     * @return string
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * get secret key
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * get secret key
     */
    public function getClientSecret()
    {
        return $this->_clientSecret;
    }

    /**
     * get redirect url
     * @return String
     */
    public function getRedirectUri()
    {
        return $this->_redirectUri;
    }

    /**
     * set access token
     */
    public function setAccessToken($token)
    {
        $this->_token = $token;
    }

    /**
     * get Access token
     * @return string
     */
    public function getAccessToken()
    {
        if (empty($this->_token)) {
            $this->fetchAccessToken();
        }

        return $this->_token;
    }

    /**
     * create request url
     * @return string
     */
    public function createAuthUrl()
    {
        return $this->_url->getUrl('socialsignup/twitter/request', ["mainw_protocol" => $this->_protocol]);
    }

    /**
     * fetch request token
     */
    public function fetchRequestToken()
    {
        try {
            $requestToken = $this->_client->getRequestToken();
            if ($requestToken == false) {
                $this->_messageManager->addError(
                    __('Connect to https://api.twitter.com is false ! <br />Error code :')
                );
                return;
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError(
                __('Connect to https://api.twitter.com is false ! <br />Error code :')
            );
            return;
        }

        $this->_session
            ->setTwitterRequestToken($requestToken);
        $this->_client->redirect();
    }

    /**
     * get access token
     */
    protected function fetchAccessToken()
    {
        if (!($params = $this->_context->getParams())
            ||
            !($requestToken = $this->_session
                ->getTwitterRequestToken())
            ) {
            throw new LocalizedException(
                __('Unable to retrieve access code.')
            );
        }

        if (!($token = $this->_client->getAccessToken(
            $params,
            $requestToken
        )
            )
        ) {
            throw new LocalizedException(
                __('Unable to retrieve access token.')
            );
        }

        $this->_session->unsTwitterRequestToken();

        return $this->_token = $token;
    }

    /**
     * get response from the api
     * @param  string $url    endpoint url
     * @param  string $method name of method
     * @param  array  $params cotains param
     * @return object
     */
    public function api($endpoint, $method = 'GET', $params = [])
    {
        if (empty($this->_token)) {
            throw new LocalizedException(
                __('Unable to proceeed without an access token.')
            );
        }

        $url = self::OAUTH2_SERVICE_URI.$endpoint;
        
        $response = $this->_httpRequest($url, strtoupper($method), $params);

        return $response;
    }

    /**
     * get response from the api
     * @param  string $url    endpoint url
     * @param  string $method name of method
     * @param  array  $params cotains param
     * @return object
     */
    protected function _httpRequest($url, $method = 'GET', $params = [])
    {
        $client = $this->_token->getHttpClient(
            [
                'callbackUrl' => $this->_redirectUri,
                'siteUrl' => self::OAUTH_URI,
                'consumerKey' => $this->_clientId,
                'consumerSecret' => $this->_clientSecret
            ]
        );

        $client->setUri($url);
        
        switch ($method) {
            case 'GET':
                $client->setMethod(ZendClient::GET);
                $client->setParameterGet($params);
                break;
            case 'POST':
                $client->setMethod(ZendClient::POST);
                $client->setParameterPost($params);
                break;
            case 'DELETE':
                $client->setMethod(ZendClient::DELETE);
                break;
            default:
                throw new LocalizedException(
                    __('Required HTTP method is not supported.')
                );
        }
        
        $response = $client->request();
        $decodedResponse = json_decode($response->getBody());

        if ($response->isError()) {
            $status = $response->getStatus();
            if (($status == 400 || $status == 401 || $status == 429)) {
                if (isset($decodedResponse->error->message)) {
                    $message = $decodedResponse->error->message;
                } else {
                    $message =__('Unspecified OAuth error occurred.');
                }

                throw new LocalizedException($message);
            } else {
                $message = sprintf(
                    __('HTTP error %d occurred while issuing request.'),
                    $status
                );

                throw new LocalizedException($message);
            }
        }
        return $decodedResponse;
    }

    /**
     * check status
     * @return boolean
     */
    protected function _isEnabled()
    {
        return $this->_helper->getTwitterStatus();
    }

    /**
     * get client id
     * @return string
     */
    protected function _getClientId()
    {
        return $this->_helper->getConsumerKey();
    }

    /**
     * get client secret key
     * @return string
     */
    protected function _getClientSecret()
    {
        return $this->_helper->getConsumerSecret();
    }
}
