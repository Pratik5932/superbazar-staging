<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Facebook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Webkul\SocialSignup\Api\FacebooksignupRepositoryInterface;
use Magento\Framework\Url\DecoderInterface;
use Magento\Customer\Model\Url;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;

/*
login class of facebook
 */
class Login extends Action
{
    const REFERER_QUERY_PARAM_NAME = 'referer';
    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';
    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';

    const XML_PATH_CONFIRM_EMAIL_TEMPLATE       = 'customer/create_account/email_confirmation_template';
    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE     = 'customer/create_account/email_confirmed_template';
    const FB_LOG_SUCC_MSG = 'You have successfully logged in using your facebook account';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var PageFactory
     */
    private $customerSession;

    /**
     * Webkul\SocialSignup\Api\FacebooksignupRepositoryInterface
     */
    private $facebooksignupRepository;

    /**
     * \Magento\Customer\Model\ResourceModel\Customer
     */
    private $customerResourceModel;

    /**
     * \Magento\Customer\Model\Customer
     */
    private $customerModel;

    /**
     * Webkul\SocialSignup\Model\facebooksignupFactory
     */
    private $facebooksignupFactory;

    /**
     * @var EncoderInterface
     */
    private $urlDecoder;

    /**
     * Magento\Customer\Model\Url;
     */
    private $customerUrlModel;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var TransportBuilder
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;

    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var $isCheckoutPageReq
     */
    private $isCheckoutPageReq;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Magento\Customer\Model\Session                    $customerSession
     * @param Magento\Customer\Model\ResourceModel\Customer     $customerResourceModel
     * @param Magento\Customer\Model\Customer                   $customerModel
     * @param Webkul\SocialSignup\Model\Facebooksignup          $facebooksignupFactory
     * @param Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param FacebooksignupRepositoryInterface                 $facebooksignupRepository
     * @param DecoderInterface                                  $urlDecoder
     * @param Url                                               $customerUrlModel
     * @param ScopeConfigInterface                              $scopeConfig
     * @param TransportBuilder                                  $transportBuilder
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel,
        \Magento\Customer\Model\Customer $customerModel,
        \Webkul\SocialSignup\Model\Facebooksignup $facebooksignupFactory,
        FacebooksignupRepositoryInterface $facebooksignupRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DecoderInterface $urlDecoder,
        Url $customerUrlModel,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        SessionManagerInterface $coreSession,
        \Magento\Framework\HTTP\Client\Curl $curl,
        CookieManagerInterface $cookieManager,
        PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\SocialSignup\Helper\Data $helperData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->customerUrlModel = $customerUrlModel;
        $this->urlDecoder = $urlDecoder;
        $this->facebooksignupFactory = $facebooksignupFactory;
        $this->customerResourceModel = $customerResourceModel;
        $this->customerModel = $customerModel;
        $this->transportBuilder = $transportBuilder;
        $this->facebooksignupRepository = $facebooksignupRepository;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->curl = $curl;
        $this->coreSession = $coreSession;
        $this->cookieManager = $cookieManager;
        $this->logger = $logger;
        $this->_helperData = $helperData;
        $this->isCheckoutPageReq = 0;
        parent::__construct($context);
    }

    public function execute()
    {
        $isCheckoutPageReq = 0;
        $post =$this->getRequest()->getParams();
        if (isset($post['is_checkoutPageReq']) && $post['is_checkoutPageReq'] == 1) {
            $isCheckoutPageReq = 1;
        }
        $this->isCheckoutPageReq = $isCheckoutPageReq;
        $facebookUser = null;
        $customerId = 0;
        $mageCustomer = 0;
        $helper = $this->_helperData;
        try {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $fbAppId=$helper->getFbAppId();
            $fbAppSecretKey=$helper->getFbSecretKey();
            $cookie = $this->getFacebookCookie($fbAppId, $fbAppSecretKey);
            if (!empty($cookie['access_token'])) {
                $appsecretProof= hash_hmac('sha256', $cookie['access_token'], $fbAppSecretKey);
                $facebookUser  = json_decode(
                    $this->getFbData('https://graph.facebook.com/v3.2/me?appsecret_proof='.$appsecretProof.'&access_token=' . $cookie['access_token'].'&debug=all&fields=id%2Cname%2Cemail%2Cfirst_name%2Clast_name%2Clocale&format=json&method=get&pretty=0&suppress_http_code=1')
                );
            }
            if ($facebookUser!='null') {
                $facebookUser = (array)$facebookUser;
                $session = $this->customerSession;
                if (isset($facebookUser['email']) && !$facebookUser['email']) {
                    $session->addError(
                        __(
                            'There is some privacy with this Facebook Account,
                        so please check your account or signup with another account.'
                        )
                    );
                    $this->coreSession->setErrorMsg(
                        __(
                            'There is some privacy with this Facebook Account,
                        so please check your account or signup with another account.'
                        )
                    );
                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    return $resultRedirect->setPath('customer/account/login');
                } else {
                    if (isset($facebookUser['id']) && $facebookUser['id']) {
                        $collection = $this->facebooksignupRepository->getByFbId($facebookUser['id']);
                        foreach ($collection as $data) {
                            $customerId = $data['customer_id'];
                            if ($customerId) {
                                $existCustomerChk = $this->customerModel->load($customerId);
                                if (!$existCustomerChk->getId()) {
                                    $this->facebooksignupRepository->getById($data['entity_id'])->delete();
                                    $customerId = 0;
                                }
                            }
                        }
                        if ($customerId) {
                            if (!$isCheckoutPageReq) {
                                $this->messageManager->addSuccess(
                                    __(
                                        'You have successfully loged in using your facebook account '
                                    )
                                );
                            }

                            $this->coreSession->setSuccessMsg(__('You have successfully loged in using your %1 account.', __('Facebook')));
                            $session->loginById($customerId);
                        } else {
                            $customerCollection = $this->customerModel
                                    ->getCollection()
                                    ->addFieldToFilter('email', ['eq'=>$facebookUser['email']]);
                            foreach ($customerCollection as $customerData) {
                                $mageCustomer = $customerData->getId();
                            }

                            if ($mageCustomer) {
                                $setcollection = $this->facebooksignupFactory
                                                ->setCustomerId($mageCustomer)
                                                ->setFbId($facebookUser['id']);
                                $setcollection->save();
                                if (!$isCheckoutPageReq) {
                                    $this->messageManager->addSuccess(
                                        __(
                                            'You have successfully logged in using your facebook account'
                                        )
                                    );
                                }
                                $this->coreSession->setSuccessMsg(__('You have successfully loged in using your %1 account.', __('Facebook')));
                                $session->loginById($mageCustomer);
                            } else {
                                if ($this->_helperData->getCustomerAttributes()) {
                                    $customerData = [
                                        'fb_id'     => $facebookUser['id'],
                                        'firstname' => $facebookUser['first_name'],
                                        'lastname'  => $facebookUser['last_name'],
                                        'email'     => $facebookUser['email'],
                                        'confirmation'  => null,
                                        'is_active' => 1,
                                        'label'     => __('facebook')
                                    ];
                                    $this->_helperData->setInSession($customerData);
                                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                                    return $resultRedirect->setPath('socialsignup/index/index');
                                } else {
                                    $this->_registerCustomer($facebookUser, $session);
                                }
                            }
                        }
                    }
                    return $this->_loginPostRedirect($session);
                }
            } else {
                return $resultRedirect->setPath('customer/account/login');
            }
        } catch (\Exception $e) {
            $this->logger->info('Controller Facebook Login : '.$e->getMessage());
            $this->messageManager->addError(__('Something went wrong, please check log file.'));
            $this->coreSession->setErrorMsg(
                __('Something went wrong, please check log file.')
            );
            return $resultRedirect->setPath('customer/account/login');
        }
    }

    /**
     * register a customer with facebook details
     * @param  array $data     facebook related data
     * @param  object &$session session data
     */
    private function _registerCustomer($data, &$session)
    {
        try {
            $password = hash('sha256', time() . $data['id']);
            $customer = $this->customerModel->setId(null);
            $customer->setData('firstname', $data['first_name']);
            $customer->setData('lastname', $data['last_name']);
            $customer->setData('email', $data['email']);
            $customer->setData('password', $password);
            $customer->setData('is_active', 1);
            $customer->setData('confirmation', null);
            $customer->setConfirmation(null);
            $customer->getGroupId();
            $customer->save();

            $this->customerModel->load($customer->getId())->setConfirmation(null)->save();
            $customer->setConfirmation(null);
            $session->setCustomerAsLoggedIn($customer);
            $customerId = $session->getCustomerId();

            $type = 'registered';

            $types = [
                // welcome email, when confirmation is disabled
                'registered'   => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
                // welcome email, when confirmation is enabled
                'confirmed'    => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
                // email with confirmation link
                'confirmation' => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
            ];

            $this->_sendEmailTemplate(
                $customer,
                $types[$type],
                self::XML_PATH_REGISTER_EMAIL_IDENTITY,
                ['customer' => $customer, 'back_url' => ''],
                0
            );
            $setcollection = $this->facebooksignupFactory
                                ->setCustomerId($customerId)
                                ->setFbId($data['id']);
            $setcollection->save();
        } catch (\Exception $e) {
            $this->logger->info('Controller Facebook _registerCustomer : '.$e->getMessage());
        }
    }

    /**
     * send mail to registered customer
     * @param  object $customer       customer details
     * @param  const $template       template
     * @param  const $sender         sender type
     * @param  integer $storeId        store id
     * @return object
     */
    protected function _sendEmailTemplate(
        $customer,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null
    ) {
        try {
            $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId))
            ->addTo($customer->getEmail(), $customer->getName())
            ->getTransport();
            $transport->sendMessage();
            return $customer;
        } catch (\Exception $e) {
            $this->logger->info('Controller Facebook _sendEmailTemplate : '.$e->getMessage());
        }
    }

    /**
     * redirct customer to a page
     * @param  object &$session object of session
     * @return string           redirect url
     */
    private function _loginPostRedirect(&$session)
    {
        try {
            if ($referer = $this->getRequest()->getParam(self::REFERER_QUERY_PARAM_NAME)) {
                $referer = $this->urlDecoder->decode($referer);
                if ((strpos($referer, $this->_storeManager->getStore()->getBaseUrl()) === 0)
                        || (
                            strpos(
                                $referer,
                                $this->_storeManager->getStore()->getBaseUrl(
                                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA,
                                    true
                                )
                            ) === 0)) {
                                $session->setBeforeAuthUrl($referer);
                } else {
                    $session->setBeforeAuthUrl($this->customerUrlModel->getDashboardUrl());
                }
            } else {
                $session->setBeforeAuthUrl($this->customerUrlModel->getDashboardUrl());
            }
            if (!$this->isCheckoutPageReq && (
                $this->messageManager->getMessages()->getLastAddedMessage() !== null &&
                !($this->messageManager->getMessages()->getLastAddedMessage()->getText() === self::FB_LOG_SUCC_MSG))
            ) {
                $this->messageManager->addSuccess(
                    __(
                        'You have successfully logged in using your facebook account'
                    )
                );
            }

            $this->coreSession->setSuccessMsg(__('You have successfully loged in using your %1 account.', __('Facebook')));

            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $path = $session->getBeforeAuthUrl(true);
            if ($this->isCheckoutPageReq) {
                $path = "checkout/index/index";
            }
            return $resultRedirect->setPath($path);
        } catch (\Exception $e) {
            $this->logger->info('Controller Facebook _loginPostRedirect : '.$e->getMessage());
        }
    }

    /**
     * get facebook cookie
     * @param  int $appId     faceboook id
     * @param  string $appSecret facebook secret key
     * @return array
     */
    private function getFacebookCookie($appId, $appSecret)
    {
        try {
            $cookieData =  $this->cookieManager->getCookie('fbsr_' . $appId);
            if ($cookieData != '') {
                return $this->getNewFacebookCookie($appId, $appSecret);
            } else {
                return $this->getOldFacebookCookie($appId, $appSecret);
            }
        } catch (\Exception $e) {
            $this->logger->info('Controller Facebook getFacebookCookie : '.$e->getMessage());
        }
    }

    /**
     * get old facebook cookie
     * @param  int $appId     faceboook id
     * @param  string $appSecret facebook secret key
     */
    private function getOldFacebookCookie($appId, $appSecret)
    {
        $args = [];
        try {
            $cookieData =  $this->cookieManager->getCookie('fbsr_' . $appId);
            parse_str(trim($cookieData, '\\"'), $args);
            ksort($args);
            $payload = '';
            foreach ($args as $key => $value) {
                if ($key != 'sig') {
                    $payload .= $key . '=' . $value;
                }
            }
            $encyptedData = hash('sha256', $payload . $appSecret);
            if (!isset($args['sig']) || $encyptedData != $args['sig']) {
                return [];
            }
        } catch (\Exception $e) {
            $this->logger->info('Controller Facebook getOldFacebookCookie : '.$e->getMessage());
        }

        return $args;
    }

    /**
     * get new facebook cookie
     * @param  int $appId     faceboook id
     * @param  string $appSecret facebook secret key
     */
    private function getNewFacebookCookie($appId, $appSecret)
    {
        $signedRequest = '';
        try {
            $cookieData =  $this->cookieManager->getCookie('fbsr_' . $appId);
            $signedRequest = $this->parseSignedRequest($cookieData, $appSecret);
            $signedRequest['uid'] = $signedRequest['user_id'];
            if ($signedRequest!='') {
                $accessTokenResponse = $this->getFbData(
                    "https://graph.facebook.com/oauth/access_token?client_id=$appId&redirect_uri=&client_secret=$appSecret&code=$signedRequest[code]"
                );
                // parse_str($accessTokenResponse);
                $response = json_decode($accessTokenResponse, true);
                $signedRequest['access_token'] = $response['access_token'];
                $signedRequest['expires'] = time() + $response['expires_in'];
            }
        } catch (\Exception $e) {
            $this->logger->info('Controller Facebook getNewFacebookCookie : '.$e->getMessage());
        }
        return $signedRequest;
    }

    /**
     * parse the signed request
     * @param  array $signedRequest contain access token & expire date
     * @param  int $secret         secret key
     * @return array
     */
    private function parseSignedRequest($signedRequest, $secret)
    {
        try {
            list($encodedSig, $payload) = explode('.', $signedRequest, 2);
            // decode the data
            $sig = $this->base64UrlDecode($encodedSig);
            $data = json_decode($this->base64UrlDecode($payload), true);

            if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
                return null;
            }
            // check sig
            $expectedSig = hash_hmac('sha256', $payload, $secret, $raw = true);
            if ($sig !== $expectedSig) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logger->info('Controller Facebook parseSignedRequest : '.$e->getMessage());
        }
    }

    /**
     * decode the sign
     * @param  string $input
     */
    private function base64UrlDecode($input)
    {
        try {
            return base64_decode(strtr($input, '-_', '+/'));
        } catch (\Exception $e) {
            $this->logger->info('Controller Facebook base64UrlDecode : '.$e->getMessage());
        }
    }

    /**
     * get facebook data
     * @param  string $url facebook authentication url
     * @return array
     */
    private function getFbData($url)
    {
        try {
            $this->curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            $this->curl->get($url);
            $response = $this->curl->getBody();
            return $response;
        } catch (\Exception $e) {
            $this->logger->info('Controller Facebook getFbData : '.$e->getMessage());
        }
    }
}
