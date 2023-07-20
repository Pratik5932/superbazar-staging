<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Google;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\Store;
use \Magento\Framework\Url;
use Magento\Framework\HTTP\ZendClient;
use Webkul\SocialSignup\Helper\Data;

/**
 * googleClient Class
 */
class GoogleClient
{
    const REDIRECT_URI_ROUTE = 'socialsignup/google/connect';
    const REDIRECT_URI_REQUEST = 'socialsignup/google/request';
    
    const OAUTH2_REVOKE_URI = 'https://accounts.google.com/o/oauth2/revoke';
    const OAUTH2_TOKEN_URI = 'https://accounts.google.com/o/oauth2/token';
    const OAUTH2_AUTH_URI = 'https://accounts.google.com/o/oauth2/auth';
    const OAUTH2_SERVICE_URI = 'https://www.googleapis.com/oauth2/v2';

    protected $_isEnabled = null;
    protected $_clientId = null;
    protected $_clientSecret = null;
    protected $_redirectUri = null;
    protected $_state = '';
    protected $_scope = [
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email',
    ];
    protected $_access = 'offline';
    protected $_prompt = 'auto';
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
     * @var Magento\Framework\App\Action\Context
     */
    protected $_contextController;

    /**
     * @var Webkul\SocialSignup\Helper\Data
     */
    protected $_helper;

    /**
     * @param Data             $helper
     * @param Store            $store
     * @param Action           $actionController
     * @param Url              $url
     */
    public function __construct(
        Data $helper,
        Store $store,
        Context $contextController,
        Url $url
    ) {
        $this->_helper = $helper;
        $this->_store = $store;
        $this->_contextController = $contextController;
        $this->_url = $url;
    }

    /**
     * set parameters
     * @param array $params contain params value
     */
    public function setParameters($params = [])
    {
        if (($this->_isEnabled = $this->_isEnabled())) {
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

            if (!empty($params['scope'])) {
                $this->_scope = $params['scope'];
            }

            if (!empty($params['state'])) {
                $this->_state = $params['state'];
            }

            if (!empty($params['access'])) {
                $this->_access = $params['access'];
            }

            if (!empty($params['prompt'])) {
                $this->_prompt = $params['prompt'];
            }
        }
    }

    /**
     * check status of google
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool) $this->_isEnabled;
    }

    /**
     * get client id of google app
     * @return string
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * get secret key of google app
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
     * get access
     */
    public function getAccess()
    {
        return $this->_access;
    }

    /**
     * set access
     */
    public function setAccess($access)
    {
        $this->_access = $access;
    }

    /**
     * get Promt
     */
    public function getPrompt()
    {
        return $this->_prompt;
    }

    /**
     * set prompt
     */
    public function setPrompt($prompt)
    {
        $this->_access = $prompt;
    }

    /**
     * set access token
     */
    public function setAccessToken($token)
    {
        $this->token = json_decode($token);
    }

    /**
     * get access token
     * @return string
     */
    public function getAccessToken()
    {
        if (empty($this->token)) {
            $this->fetchAccessToken();
        } elseif ($this->isAccessTokenExpired()) {
            $this->refreshAccessToken();
        }

        return json_encode($this->token);
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
                    'response_type' => 'code',
                    'redirect_uri' => $this->_redirectUri,
                    'client_id' => $this->_clientId,
                    'scope' => implode(' ', $this->_scope),
                    'state' => $this->_state,
                    'access_type' => $this->_access,
                    'approval_prompt' => $this->_prompt,
                    'display' => 'popup',
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
        return $this->_url->getUrl('socialsignup/google/request', ["mainw_protocol" => $this->_protocol]);
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
        if (empty($this->token)) {
            $this->fetchAccessToken();
        } elseif ($this->isAccessTokenExpired()) {
            $this->refreshAccessToken();
        }
        $url = self::OAUTH2_SERVICE_URI.$endpoint;

        $method = strtoupper($method);

        $params = array_merge(
            [
            'access_token' => $this->token->access_token
            ],
            $params
        );
        $response = $this->_httpRequest($url, $method, $params);

        return $response;
    }

    /**
     * revoke the token
     */
    public function revokeToken()
    {
        if (empty($this->token)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('No access token available.')
            );
        }

        if (empty($this->token->refresh_token)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('No refresh token, nothing to revoke.')
            );
        }

        $this->_httpRequest(
            self::OAUTH2_REVOKE_URI,
            'POST',
            [
               'token' => $this->token->refresh_token
            ]
        );
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

        $response->created = time();

        $this->token = $response;
    }

    /**
     * refreshed the token
     */
    protected function refreshAccessToken()
    {
        if (empty($this->token->refresh_token)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('No refresh token, unable to refresh access token.')
            );
        }

        $response = $this->_httpRequest(
            self::OAUTH2_TOKEN_URI,
            'POST',
            [
                'client_id' => $this->_clientId,
                'client_secret' => $this->_clientSecret,
                'refresh_token' => $this->token->refresh_token,
                'grant_type' => 'refresh_token'
            ]
        );

        $this->token->access_token = $response->access_token;
        $this->token->expires_in = $response->expires_in;
        $this->token->created = time();
    }

    /**
     * check access token expiry
     * @return boolean [description]
     */
    protected function isAccessTokenExpired()
    {
        // If the token is set to expire in the next 30 seconds.
        $expired = ($this->token->created + ($this->token->expires_in - 30)) < time();

        return $expired;
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
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($message)
                    );
            } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($message)
                    );
            }
        }

        return $decodedResponse;
    }

    /**
     * check google sign in enable or not
     * @return boolean
     */
    protected function _isEnabled()
    {
        return $this->_helper->getGoogleStatus();
    }

    /**
     * get Client id
     * @return string
     */
    protected function _getClientId()
    {
        return $this->_helper->getGoogleClientId();
    }

    /**
     * get secret key
     * @return string
     */
    protected function _getClientSecret()
    {
        return $this->_helper->getGoogleSecretKey();
    }
}
