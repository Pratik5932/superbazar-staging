<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Google;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\Generic;
use Magento\Store\Model\Store;
use Magento\Framework\Url;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Webkul\SocialSignup\Helper\Google;
use Webkul\SocialSignup\Helper\Data;

/**
 * Connect class of google
 */
class Connect extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    /**
     * @var Store
     */
    private $store;
    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $session;
    /**
     * @var Url
     */
    protected $_url;
    /**
     * @var Attribute
     */
    private $eavAttribute;
    /**
     * @var Google
     */
    private $helperGoogle;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    
    /**
     * @param Generic                                            $session
     * @param Context                                            $context
     * @param Store                                              $store
     * @param Data                                               $helper
     * @param Google                                             $helperGoogle
     * @param Attribute                                          $eavAttribute
     * @param \Magento\Framework\UrlInterface                    $urlinterface
     * @param GoogleClient                                       $googleClient
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param \Magento\Framework\Controller\ResultInterface      $result
     * @param PageFactory                                        $resultPageFactory
     */
    public function __construct(
        Data $helper,
        Generic $session,
        Context $context,
        Store $store,
        Google $helperGoogle,
        Attribute $eavAttribute,
        GoogleClient $googleClient,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->helperGoogle = $helperGoogle;
        $this->eavAttribute = $eavAttribute;
        $this->store = $store;
        $this->_scopeConfig = $scopeConfig;
        $this->session = $session;
        $this->coreSession = $coreSession;
        $this->googleClient = $googleClient;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * generate access token and user information
     */
    public function execute()
    {
        $this->googleClient->setParameters();
        $helper = $this->helper;
        try {
            $isSecure = $this->store->isCurrentlySecure();
            $mainwProtocol = $this->session->getIsSecure();
            $redirectPath = $this->_connectCallback();
            if ($redirectPath) {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath($redirectPath);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->coreSession->setErrorMsg($e->getMessage());
        }

        if (!empty($this->referer)) {
            if (empty($this->flag)) {
                if (!$isSecure) {
                    $redirectUrl = $this->_url->getUrl('socialsignup/google/redirect/');
                    $redirectUrl = str_replace("https://", "http://", $redirectUrl);
                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    return $resultRedirect->setPath($redirectUrl);
                } else {
                    $this->helper->_loginFinalize($this);
                }
            } else {
                $this->helper->closeWindow($this);
            }
        } else {
            $this->helper->redirect404($this);
        }
    }

    protected function _connectCallback()
    {
        $isCheckoutPageReq = 0;
        $isCheckoutPageReq = $this->_helper->getCoreSession()->getIsSocialSignupCheckoutPageReq();
        $errorCode = $this->getRequest()->getParam('error');
        $code = $this->getRequest()->getParam('code');
        $state = $this->getRequest()->getParam('state');
        if (!($errorCode || $code) && !$state) {
            // Direct route access - deny
            return;
        }
        
        $this->referer = $this->_url->getCurrentUrl();

        if (!$state || $state != $this->session->getGoogleCsrf()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to find google Csrf Code')
            );
        }

        if ($errorCode) {
            // Google API read light - abort
            if ($errorCode === 'access_denied') {
                unset($this->referer);
                $this->flag = "noaccess";
                $this->helper->closeWindow($this);
            }
            return;
        }

        if ($code) {
            $attributegId = $this->eavAttribute->getIdByCode('customer', 'socialsignup_gid');
            $attributegtoken = $this->eavAttribute->getIdByCode('customer', 'socialsignup_gtoken');
            if ($attributegId == false || $attributegtoken == false) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Attribute socialsignup_gid or socialsignup_gtoken not exist')
                );
            }
            // Google API green light - proceed
            $userInfo = $this->googleClient->api('/userinfo');

            $token = $this->googleClient->getAccessToken();
            
            $customersByGoogleId = $this->helperGoogle
                ->getCustomersByGoogleId($userInfo->id);

            $this->_connectWithActiveAccount($customersByGoogleId, $userInfo, $token);

            if ($this->_checkAccountByGoogleId($customersByGoogleId)) {
                return;
            }

            $customersByEmail = $this->helperGoogle
                ->getCustomersByEmail($userInfo->email);
  
            if ($customersByEmail->getSize()) {
                // Email account already exists - attach, login
                $this->helperGoogle->connectByGoogleId(
                    $customersByEmail,
                    $userInfo->id,
                    $token
                );
                if (!$isCheckoutPageReq) {
                    $this->messageManager->addSuccess(
                        __(
                            'We have discovered you already have an account at our store.
                            Your %1 account is now connected to your store account.',
                            __(
                                'Google'
                            )
                        )
                    );
                }
                $this->coreSession->setSuccessMsg(__(
                    'We have discovered you already have an account at our store.
                    Your %1 account is now connected to your store account.',
                    __(
                        'Google'
                    )
                ));
                return;
            }

            // New connection - create, attach, login
            if (empty($userInfo->given_name)) {
                if (!$isCheckoutPageReq) {
                    $this->messageManager->addError(
                        __('Sorry, could not retrieve your %1 first name. Please try again.', __('Google'))
                    );
                }
                $this->coreSession->setErrorMsg(__('Sorry, could not retrieve your %1 first name. Please try again.', __('Google')));
            }

            if (empty($userInfo->family_name)) {
                if (!$isCheckoutPageReq) {
                    $this->messageManager->addError(
                        __('Sorry, could not retrieve your %2 last name. Please try again.', __('Google'))
                    );
                }
                $this->coreSession->setErrorMsg(__('Sorry, could not retrieve your %1 last name. Please try again.', __('Google')));
            }
            if ($this->helper->getCustomerAttributes()) {
                $customerData = [
                    'firstname' => $userInfo->given_name,
                    'lastname'  => $userInfo->family_name,
                    'email'     => $userInfo->email,
                    'confirmation'  => null,
                    'is_active' => 1,
                    'socialsignup_gid' => $userInfo->id,
                    'socialsignu_gtoken'    => $token,
                    'label'     => __('google')
                ];
                $this->helper->setInSession($customerData);
                return 'socialsignup/index/index';
            } else {
                $this->helperGoogle->connectByCreatingAccount(
                    $userInfo->email,
                    $userInfo->given_name,
                    $userInfo->family_name,
                    $userInfo->id,
                    $token
                );
            }
            if (!$isCheckoutPageReq) {
                $this->messageManager->addSuccess(
                    __(
                        'Your %1 account is now connected to your new user account at our store.
                        Now you can login using our %1 Connect button or using store account 
                        credentials you will receive to your email address.',
                        __(
                            'Google'
                        )
                    )
                );
            }
            $this->coreSession->setSuccessMsg(__(
                'Your %1 account is now connected to your new user account at our store.
                Now you can login using our %1 Connect button or using store account 
                credentials you will receive to your email address.',
                __(
                    'Google'
                )
            ));
        }
    }

    /**
     * connected with existing account
     * @param  object $customersByGoogleId
     * @param  object $userInfo
     * @param  string $token
     */
    private function _connectWithActiveAccount($customersByGoogleId, $userInfo, $token)
    {
        $isCheckoutPageReq = 0;
        $isCheckoutPageReq = $this->_helper->getCoreSession()->getIsSocialSignupCheckoutPageReq();
        if ($this->customerSession->isLoggedIn()) {
            // Logged in user
            if ($customersByGoogleId->getSize()) {
                // Google account already connected to other account - deny
                if (!$isCheckoutPageReq) {
                    $this->messageManager
                        ->addNotice(
                            __(
                                'Your %1 account is already connected to one of our store accounts.',
                                __(
                                    'Google'
                                )
                            )
                        );
                }
                $this->coreSession->setSuccessMsg(
                    __(
                        'Your %1 account is already connected to one of our store accounts.',
                        __(
                            'Google'
                        )
                    )
                );
                return;
            }

            // Connect from account dashboard - attach
            $customer = $this->customerSession->getCustomer();

            $this->helperGoogle->connectByGoogleId(
                $customer,
                $userInfo->id,
                $token
            );
            if (!$isCheckoutPageReq) {
                $this->messageManager->addSuccess(
                    __(
                        'Your %1 account is now connected to your store account.
                        You can now login using our %1 Connect button or using 
                        store account credentials you will receive to your email address.',
                        __(
                            'Google'
                        )
                    )
                );
            }
            $this->coreSession->setSuccessMsg(
                __(
                    'Your %1 account is now connected to your store account.
                    You can now login using our %1 Connect button or using 
                    store account credentials you will receive to your email address.',
                    __(
                        'Google'
                    )
                )
            );
            return;
        }
    }

    /**
     * check customer account by google id
     * @param  object $customersByGoogleId
     */
    private function _checkAccountByGoogleId($customersByGoogleId)
    {
        $isCheckoutPageReq = 0;
        $isCheckoutPageReq = $this->helper->getCoreSession()->getIsSocialSignupCheckoutPageReq();
        if ($customersByGoogleId->getSize()) {
            // Existing connected user - login
            foreach ($customersByGoogleId as $customerInfo) {
                $customer = $customerInfo;
            }

            $this->helperGoogle->loginByCustomer($customer);

            if (!$isCheckoutPageReq) {
                $this->messageManager
                    ->addSuccess(
                        __('You have successfully logged in using your %1 account.', __('Google'))
                    );
            }
            $this->coreSession->setSuccessMsg(
                __('You have successfully logged in using your %1 account.', __('Google'))
            );
            return true;
        }
        return false;
    }
}
