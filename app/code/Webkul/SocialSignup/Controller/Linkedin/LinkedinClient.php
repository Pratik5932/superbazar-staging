<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Linkedin;

use Magento\Framework\View\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\Store;
use \Magento\Framework\Url;
use Magento\Framework\HTTP\ZendClient;
use Webkul\SocialSignup\Helper\Data;
use Magento\Framework\Session\Generic;
use Magento\Framework\Exception\LocalizedException;

/**
 * linkedClient class of linkedin
 */
class LinkedinClient
{
    const REDIRECT_URI_ROUTE = 'socialsignup/linkedin/connect';
    const REDIRECT_URI_REQUEST = 'socialsignup/linkedin/request';

    const OAUTH2_SERVICE_URI = 'https://api.linkedin.com';
    const OAUTH2_AUTH_URI = 'https://www.linkedin.com/oauth/v2/authorization';
    const OAUTH2_TOKEN_URI = 'https://www.linkedin.com/oauth/v2/accessToken';

    protected $_clientId = null;
    protected $_clientSecret = null;
    protected $_redirectUri = null;
    protected $_state = '';
    protected $_scope = ['r_liteprofile', 'r_emailaddress'];
    protected $_fieldSelect = ['id','first-name','last-name','public-profile-url','email-address'];
    protected $_userFormat = 'format=json';

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
     * @var Magento\Framework\HTTP\ZendClient
     */
    protected $_zendClient;

    protected $_response;

    /**
     * @param Data             $helper
     * @param Generic          $session
     * @param Store            $store
     * @param Context          $context
     * @param ZendClient       $zendClient
     * @param Context          $contextController
     * @param Url              $url
     */
    public function __construct(
        Data $helper,
        Generic $session,
        Store $store,
        Context $context,
        ZendClient $zendClient,
        \Magento\Framework\HTTP\Client\Curl $curl,
        Context $contextController,
        Url $url
    ) {
    
        $this->_context = $context;
        $this->_session = $session;
        $this->_helper = $helper;
        $this->_store = $store;
        $this->_zendClient = $zendClient;
        $this->curl = $curl;
        $this->_contextController = $contextController;
        $this->_url = $url;
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
     * set access token
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
                    'scope' => implode(',', $this->_scope),
                    'display' => 'popup'
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
        return $this->_url->getUrl('socialsignup/linkedin/request', ["mainw_protocol" => $this->_protocol]);
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
            $this->fetchAccessToken();
        }
        $select = ':('.implode(',', $this->_fieldSelect).')';
        $select .= '?'.$this->_userFormat;
        // $url = self::OAUTH2_SERVICE_URI.$endpoint.$select;
        $url = self::OAUTH2_SERVICE_URI.$endpoint;

        $method = strtoupper($method);
        $responseData = $this->_token;
        $params = array_merge(
            [
                'oauth2_access_token' => $responseData['access_token']
            ],
            $params
        );

        $response = $this->_httpRequest($url, $method, $params);
        return $response;
    }

    /**
     * fetch access token
     */
    protected function fetchAccessToken()
    {
        if (empty($this->_contextController->getRequest()->getParam('code'))) {
            throw new  \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve access code.')
            );
        }

        $endPointResponse = $this->_httpRequest(
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
        $this->_token = $endPointResponse;
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
        $decodedResponse = '';
        $this->curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $this->curl->setOption(CURLOPT_TIMEOUT, 60);
        
        switch ($method) {
            case 'GET':
                $this->curl->addHeader('Authorization', 'Bearer '.$params['oauth2_access_token']);
                $this->curl->addHeader('Connection', 'Keep-Alive');
                $this->curl->get($url, $params);
                break;
            case 'POST':
                $this->curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
                $this->curl->post($url, $params);
                break;
            case 'DELETE':
                $this->curl->get($url, $params);
                break;
            default:
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Required HTTP method is not supported.')
                );
        }
        try {
            $response = $this->curl->getBody();
            $decodedResponse = json_decode($response, true);
            $status = $this->curl->getStatus();
            if (($status == 400 || $status == 401)) {
                if (isset($decodedResponse->error->message)) {
                    $message = $decodedResponse->error->message;
                } else {
                    $message = __('Unspecified OAuth error occurred.');
                }
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($message)
                    );
            }
        } catch (\Exception $e) {
            $message = $e;
        }

        return $decodedResponse;
    }

    /**
     * check status
     * @return boolean
     */
    protected function _isEnabled()
    {
        return $this->_helper->getLinkedInStatus();
    }

    /**
     * get client id
     * @return string
     */
    protected function _getClientId()
    {
        return $this->_helper->getLinkedinAppId();
    }

    /**
     * get client secret key
     * @return string
     */
    protected function _getClientSecret()
    {
        return $this->_helper->getLinkedinSecret();
    }
}
