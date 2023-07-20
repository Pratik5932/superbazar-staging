<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Instagram;

use Magento\Framework\View\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\Store;
use \Magento\Framework\Url;
use Magento\Framework\HTTP\ZendClient;
use Webkul\SocialSignup\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\Generic;

/**
 * InstagramClient class of Instagram
 */
class InstagramClient
{
    const REDIRECT_URI_ROUTE = 'socialsignup/instagram/connect';
    const REDIRECT_URI_REQUEST = 'socialsignup/instagram/request';

    const OAUTH2_SERVICE_URI = 'https://api.instagram.com/v1/';
    const OAUTH2_AUTH_URI = 'https://api.instagram.com/oauth/authorize';
    const OAUTH2_TOKEN_URI = 'https://api.instagram.com/oauth/access_token';

    protected $_clientId = null;
    protected $_clientSecret = null;
    protected $_redirectUri = null;
    protected $_state = '';
    protected $_scope = ['basic'];
    protected $_protocol = "http";

    protected $_token = null;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * @var Url
     */
    protected $_url;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;
    
    /**
     * helper
     * @var Webkul\SocialSignup\Helper\Data
     */
    protected $_helper;

    /**
     * @var Magento\Framework\App\Action\Context
     */
    protected $_contextController;

    /**
     * @param Data    $helper
     * @param Generic $session
     * @param Store   $store
     * @param Context $context
     * @param Url     $url
     * @param Action  $actionController
     */
    public function __construct(
        Data $helper,
        Generic $session,
        Store $store,
        Context $context,
        Url $url,
        Context $contextController
    ) {
        $this->_context = $context;
        $this->_session = $session;
        $this->_helper = $helper;
        $this->_store = $store;
        $this->_url = $url;
        $this->_contextController = $contextController;
    }

    /**
     * set parameters
     * @param array $params contain params value
     */
    public function setParameters($params = [])
    {
        if (($this->isEnabled = $this->_isEnabled())) {
            $this->_clientId = $this->_getClientId();
            $this->_clientSecret = $this->_getClientSecret();

            $isSecure = $this->_store->isCurrentlySecure();
            if ($isSecure) {
                $this->_protocol = "https";
            }

            $this->_redirectUri = $this->_url->sessionUrlVar(
                $this->_url->getUrl(self::REDIRECT_URI_ROUTE, ['_secure'=>true])
            );

            if (!empty($params['scope'])) {
                $this->_scope = $params['scope'];
            }

            if (!empty($params['state'])) {
                $this->_state = $params['state'];
            }
        }
    }

    /**
     * check status of google
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
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * get secret key
     * @return [type] [description]
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
    * get Scope
    * @return array
    */
    public function getScope()
    {
        return $this->_scope;
    }

    /**
     * get State
     * @return string
     */
    public function getState()
    {
        return $this->_state;
    }

    /**
     * set state
     */
    public function setState($state)
    {
        $this->_state = $state;
    }

    /**
     * set access _token
     */
    public function setAccessToken($token)
    {
        $this->_token = json_decode($token);
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

        return json_encode($this->_token);
    }

    /**
     * create request url
     * @return string
     */
    public function createRequestUrl()
    {
        $url =
            self::OAUTH2_AUTH_URI.'?'.
            http_build_query(
                [
                    'response_type'=> 'code',
                    'client_id' => $this->_clientId,
                    'redirect_uri' => $this->_redirectUri,
                    'state' => $this->_state,
                    'scope' => implode(',', $this->_scope)
                ]
            );

        return $url;
    }

    /**
     * create authentication url
     * @return string
     */
    public function createAuthUrl()
    {
        return $this->_url->getUrl('socialsignup/instagram/request', ["mainw_protocol" => $this->_protocol]);
    }

    /**
     * get the response from the api
     * @param  string $endpoint endpoint api
     * @param  string $method   method of endpoint
     * @param  array  $params
     * @return object
     */
    public function api($endpoint, $method = 'GET', $params = [])
    {
        if (empty($this->_token)) {
            $this->fetchAccessToken();
        }
        $authMethod = '?access_token=' . $this->_token->access_token;
        $url = self::OAUTH2_SERVICE_URI.$endpoint.$authMethod;

        $method = strtoupper($method);
        
        $response = $this->_httpRequest($url, $method, $params);
        return $response;
    }

    /**
     * fetch access token
     */
    protected function fetchAccessToken()
    {
        if (empty($this->_contextController->getRequest()->getParam('code'))) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve access code.')
            );
        }

        $response = $this->_httpRequest(
            self::OAUTH2_TOKEN_URI,
            'POST',
            [
                'code' => $this->_contextController->getRequest()->getParam('code'),
                'redirect_uri' => $this->_redirectUri,
                'client_id' => $this->_clientId,
                'client_secret' => $this->_clientSecret,
                'grant_type' => 'authorization_code'
            ]
        );
        $this->_token = $response;
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
        $client = new ZendClient($url, ['timeout' => 60]);

        switch ($method) {
            case 'GET':
                $client->setParameterGet($params);
                break;
            case 'POST':
                $client->setParameterPost($params);
                break;
            case 'DELETE':
                $client->setParameterGet($params);
                break;
            default:
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Required HTTP method is not supported.')
                );
        }

        $response = $client->request($method);
        $decodedResponse = json_decode($response->getBody());

        if ($response->isError()) {
            $status = $response->getStatus();
            if (($status == 400 || $status == 401)) {
                if (isset($decodedResponse->error->message)) {
                    $message = $decodedResponse->error->message;
                } else {
                    $message = __('Unspecified OAuth error occurred.');
                }

                throw new \Magento\Framework\Exception\LocalizedException($message);
            } else {
                $message = sprintf(
                    __('HTTP error %d occurred while issuing request.'),
                    $status
                );

                throw new \Magento\Framework\Exception\LocalizedException($message);
            }
        }

        return $decodedResponse;
    }

    /**
     * check status of instagram
     * @return boolean
     */
    protected function _isEnabled()
    {
        return $this->_helper->getInstaStatus();
    }

    /**
     * get client id
     * @return string
     */
    protected function _getClientId()
    {
        return $this->_helper->getInstaClientId();
    }

    /**
     * get client secret key
     * @return string
     */
    protected function _getClientSecret()
    {
        return $this->_helper->getInstaSecretKey();
    }
}
