<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Linkedin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\Generic;
use Magento\Store\Model\Store;
use Magento\Framework\Url;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Webkul\SocialSignup\Helper\Linkedin;
use Magento\Framework\Exception\LocalizedException;

class Connect extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var helperLinkedin
     */
    protected $_helperLinkedin;
    /**
     * @var eavAttribute
     */
    protected $_eavAttribute;
    /**
     * @var store
     */
    protected $_store;
    /**
     * @var scopeConfig
     */
    protected $_scopeConfig;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Generic $session,
        Context $context,
        Store $store,
        Linkedin $helperLinkedin,
        Attribute $eavAttribute,
        LinkedinClient $linkedinClient,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Webkul\SocialSignup\Helper\Data $helper,
        PageFactory $resultPageFactory
    ) {
    
        $this->customerSession = $customerSession;
        $this->_helperLinkedin = $helperLinkedin;
        $this->_eavAttribute = $eavAttribute;
        $this->_store = $store;
        $this->_scopeConfig = $scopeConfig;
        $this->_session = $session;
        $this->_helper = $helper;
        $this->coreSession = $coreSession;
        $this->_linkedinClient = $linkedinClient;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $this->_linkedinClient->setParameters();
            $isSecure = $this->_store->isCurrentlySecure();
            $mainwProtocol = $this->_session->getIsSecure();
            $redirectPath = $this->_connectCallback();
            if ($redirectPath) {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath($redirectPath);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->coreSession->setErrorMsg(
                $e->getMessage()
            );
        }

        if (!empty($this->referer)) {
            if (empty($this->flag)) {
                if (!$isSecure) {
                    $redirectUrl = $this->_url->getUrl('socialsignup/linkedin/redirect/');
                    $redirectUrl = str_replace("https://", "http://", $redirectUrl);
                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    return $resultRedirect->setPath($redirectUrl);
                } else {
                    $this->_helper->_loginFinalize($this);
                }
            } else {
                $this->_helper->closeWindow($this);
            }
        } else {
            $this->_helper->redirect404($this);
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
            return;
        }
        $this->referer = $this->_url->getCurrentUrl();

        if ($errorCode) {
            if ($errorCode === 'access_denied') {
                unset($this->referer);
                $this->flag = "noaccess";
                $this->_helper->closeWindow($this);
            }
            return;
        }
        if ($code) {
            $attributegId = $this->_eavAttribute->getIdByCode('customer', 'socialsignup_lid');
            $attributegtoken = $this->_eavAttribute->getIdByCode('customer', 'socialsignup_ltoken');
            if ($attributegId == false || $attributegtoken == false) {
                throw new  \Magento\Framework\Exception\LocalizedException(
                    __('Attribute `socialsignup_lid` or `socialsignup_ltoken` not exist')
                );
            }
            $token = $this->_linkedinClient->getAccessToken();

            $userInfo = $this->_linkedinClient->api('/v2/me');
            if ($userInfo['id']) {
                $userInfoHandle = $this->_linkedinClient
                ->api('/v2/emailAddress?q=members&projection=(elements*(handle~))');
                $userInfo['emailAddress'] = $userInfoHandle['elements'][0]['handle~']['emailAddress'];
            }
            
            $customersByLinkedinId = $this->_helperLinkedin
                ->getCustomersByLinkedinId($userInfo['id']);

            $this->_connectWithCurrentCustomer($customersByLinkedinId, $userInfo, $token);

            if ($customersByLinkedinId->count()) {
                foreach ($customersByLinkedinId as $key => $customerInfo) {
                    $customer = $customerInfo;
                }
                $this->_helperLinkedin->loginByCustomer($customer);
                if (!$isCheckoutPageReq) {
                    $this->messageManager
                        ->addSuccess(
                            __('You have successfully logged in using your %1 account.', __('LinkedIn'))
                        );
                }
                $this->coreSession->setSuccessMsg(__('You have successfully logged in using your %1 account.', __('LinkedIn')));
                return;
            }

            $customersByEmail = $this->_helperLinkedin
                ->getCustomersByEmail($userInfo['emailAddress']);

            if ($customersByEmail->count()) {
                $this->_helperLinkedin->connectByLinkedinId(
                    $customersByEmail,
                    $userInfo['id'],
                    $token
                );
                if (!$isCheckoutPageReq) {
                    $this->messageManager->addSuccess(
                        __(
                            'We have discovered you already have an account at our store.
                            Your %1 account is now connected to your store account.',
                            __(
                                'LinkedIn'
                            )
                        )
                    );
                }
                $this->coreSession->setSuccessMsg(__(
                    'We have discovered you already have an account at our store.
                     Your %1 account is now connected to your store account.',
                    __(
                        'LinkedIn'
                    )
                ));
                return;
            }

            if (empty($userInfo['localizedFirstName'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Sorry, could not retrieve your %1 first name. Please try again.', __('LinkedIn'))
                );
            }

            if (empty($userInfo['localizedLastName'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Sorry, could not retrieve your %1 last name. Please try again.', __('LinkedIn'))
                );
            }
            if ($this->_helper->getCustomerAttributes()) {
                $customerData = [
                    'firstname' => $userInfo['localizedFirstName'],
                    'lastname'  => $userInfo['localizedLastName'],
                    'email'     => $userInfo['emailAddress'],
                    'confirmation'  => null,
                    'is_active' => 1,
                    'socialsignup_lid' => $userInfo['id'],
                    'socialsignup_ltoken'    => $token,
                    'label'     => __('linkedIn')
                ];
                $this->_helper->setInSession($customerData);
                return 'socialsignup/index/index';
            } else {
                $this->_helperLinkedin->connectByCreatingAccount(
                    $userInfo['emailAddress'],
                    $userInfo['localizedFirstName'],
                    $userInfo['localizedLastName'],
                    $userInfo['id'],
                    $token
                );
            }
            
            if (!$isCheckoutPageReq) {
                $this->messageManager->addSuccess(
                    __(
                        'Your %1 account is now connected to your new user account at our store.
                        Now you can login using our %1 Connect button or using store account credentials
                        you will receive to your email address.',
                        __(
                            'LinkedIn'
                        )
                    )
                );
            }
            $this->coreSession->setSuccessMsg(
                __(
                    'Your %1 account is now connected to your new user account at our store.
                    Now you can login using our %1 Connect button or using store account credentials
                    you will receive to your email address.',
                    __(
                        'LinkedIn'
                    )
                )
            );
        }
    }

    private function _connectWithCurrentCustomer($customersByLinkedinId, $userInfo, $token)
    {
        $isCheckoutPageReq = 0;
        $isCheckoutPageReq = $this->_helper->getCoreSession()->getIsSocialSignupCheckoutPageReq();
        if ($this->customerSession->isLoggedIn()) {
            if ($customersByLinkedinId->count()) {
                if (!$isCheckoutPageReq) {
                    $this->messageManager
                        ->addNotice(
                            __('Your %1 account is already connected to one of our store accounts.', __('LinkedIn'))
                        );
                }
                $this->coreSession->setSuccessMsg(
                    __('Your %1 account is already connected to one of our store accounts.', __('LinkedIn'))
                );
                return;
            }

            $customer = $this->customerSession->getCustomer();

            $this->_helperLinkedin->connectByLinkedinId(
                $customer,
                $userInfo['id'],
                $token
            );
            if (!$isCheckoutPageReq) {
                $this->messageManager->addSuccess(
                    __(
                        'Your %1 account is now connected to your store account.
                        You can now login using our %1 Connect button or using store
                        account credentials you will receive to your email address.',
                        __(
                            'Linkedin'
                        )
                    )
                );
            }
            $this->coreSession->setSuccessMsg(
                __(
                    'Your %1 account is now connected to your store account.
                    You can now login using our %1 Connect button or using store
                    account credentials you will receive to your email address.',
                    __(
                        'Linkedin'
                    )
                )
            );

            return;
        }
    }
}
