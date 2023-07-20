<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Instagram;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\Generic;
use Magento\Store\Model\Store;
use Magento\Framework\Url;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Webkul\SocialSignup\Helper\Instagram;
use Magento\Framework\Exception\LocalizedException;

/**
 * Connect class of instagram
 */
class Connect extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var helperInstagram
     */
    protected $_helperInstagram;

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
     * helper
     * @var Webkul\SocialSignup\Helper\Data
     */
    protected $_helper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Generic $session
     * @param Store $store
     * @param Instagram $helperInstagram
     * @param Attribute $eavAttribute
     * @param Magento\Framework\UrlInterface $urlinterface
     * @param Magento\Customer\Model\Session $customerSession
     * @param Magento\Framework\Controller\ResultInterface $result
     */
    public function __construct(
        Generic $session,
        Context $context,
        Store $store,
        Instagram $helperInstagram,
        Attribute $eavAttribute,
        InstagramClient $instagramClient,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\SocialSignup\Helper\Data $helper,
        PageFactory $resultPageFactory
    ) {
    
        $this->_customerSession = $customerSession;
        $this->_helperInstagram = $helperInstagram;
        $this->_eavAttribute = $eavAttribute;
        $this->_store = $store;
        $this->_helper = $helper;
        $this->_scopeConfig = $scopeConfig;
        $this->coreSession = $coreSession;
        $this->_session = $session;
        $this->_instagramClient = $instagramClient;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * get userinformation from api
     */
    public function execute()
    {
        try {
            $this->_instagramClient->setParameters();
            $isSecure = $this->_store->isCurrentlySecure();
            $mainwProtocol = $this->_session->getIsSecure();
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
                    $redirectUrl = $this->_url->getUrl('socialsignup/instagram/redirect/');
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

    /**
     * login customer
     */
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

        if (!$state || $state != $this->_session->getInstagramCsrf()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Csrf code did not find for instagram')
            );
        }

        if ($errorCode) {
            // Instagram API read light - abort
            if ($errorCode === 'access_denied') {
                unset($this->referer);
                $this->flag = "noaccess";
                $this->_helper->closeWindow($this);
            }
            return;
        }
        if ($code) {
            $attributegId = $this->_eavAttribute->getIdByCode('customer', 'socialsignup_instatoken');
            $attributegtoken = $this->_eavAttribute->getIdByCode('customer', 'socialsignup_instaid');
            if ($attributegId == false || $attributegtoken == false) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Attribute socialsignup_instatoken or socialsignup_instaid not exist')
                );
            }
            // Instagram API green light - proceed
            $userInfo = $this->_instagramClient->api('users/self');
            $token = $this->_instagramClient->getAccessToken();

            $customersByInstagramId = $this->_helperInstagram->getCustomersByInstagramId($userInfo->data->id);
            
            if ($customersByInstagramId) {
                $this->_connectCustomerByInstagramId($customersByInstagramId, $userInfo, $token);
                return;
            }

            $customersByEmail = $this->_helperInstagram
                ->getCustomersByEmail($userInfo->data->username.'@instagram-user.com');

            if ($customersByEmail->getSize()) {
                // Email account already exists - attach, login
                foreach ($customersByEmail as $customerInfo) {
                    $customer = $customerInfo;
                }
                $this->_helperInstagram->connectByInstagramId(
                    $customer,
                    $userInfo->data->id,
                    $token
                );
                if (!$isCheckoutPageReq) {
                    $this->messageManager->addSuccess(
                        __(
                            'We have discovered you already have an account at our store.
                            Your %1 account is now connected to your store account.',
                            __(
                                'Instagram'
                            )
                        )
                    );
                }
                $this->coreSession->setSuccessMsg(__(
                    'We have discovered you already have an account at our store.
                    Your %1 account is now connected to your store account.',
                    __(
                        'Instagram'
                    )
                ));
                return;
            }

            // New connection - create, attach, login
            if (empty($userInfo->data->full_name)) {
                throw new LocalizedException(
                    __('Sorry, could not retrieve your %1 last name. Please try again.', __('Instagram'))
                );
            }
            if ($this->_helper->getCustomerAttributes()) {
                $name = explode(' ', $userInfo->data->full_name, 2);

                if (count($name) > 1) {
                    $firstName = $name[0];
                    $lastName = $name[1];
                } else {
                    $firstName = $name[0];
                    $lastName = $name[0];
                }
                $customerData = [
                    'firstname' => $firstName,
                    'lastname'  => $lastName,
                    'email'     => $userInfo->data->username.'@instagram-user.com',
                    'confirmation'  => null,
                    'is_active' => 1,
                    'socialsignup_instaid' => $userInfo->data->id,
                    'socialsignup_instatoken'    => $token,
                    'label'     => __('instagram')
                ];
                $this->_helper->setInSession($customerData);
                return 'socialsignup/index/index';
            } else {
                try {
                    $this->_helperInstagram->connectByCreatingAccount(
                        $userInfo->data->username.'@instagram-user.com',
                        $userInfo->data->full_name,
                        $userInfo->data->id,
                        $token
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
            if (!$isCheckoutPageReq) {
                $this->messageManager->addNotice(
                    __(
                        'Since instagram doesn\'t support third-party access to your email address,
                        we were unable to send you your store account credentials.
                        To be able to login using store account credentials you will need to update
                        your email address and password using  Edit Account Information.'
                    )
                );
            }
            $this->coreSession->setSuccessMsg(__(
                'Since instagram doesn\'t support third-party access to your email address,
                we were unable to send you your store account credentials.
                To be able to login using store account credentials you will need to update
                your email address and password using  Edit Account Information.'
            ));
        }
    }

    public function _connectCustomerByInstagramId($customersByInstagramId, $userInfo, $token)
    {
        $isCheckoutPageReq = 0;
        $isCheckoutPageReq = $this->_helper->getCoreSession()->getIsSocialSignupCheckoutPageReq();
        if ($this->_customerSession->isLoggedIn()) {
            // Logged in user
            if ($customersByInstagramId->count()) {
                // Instagram account already connected to other account - deny
                if (!$isCheckoutPageReq) {
                    $this->messageManager
                    ->addNotice(
                        __('Your %1 account is already connected to one of our store accounts.', __('Instagram'))
                    );
                }
                $this->coreSession->setSuccessMsg(__('Your %1 account is already connected to one of our store accounts.', __('Instagram')));
                return;
            }

            // Connect from account dashboard - attach
            $customer = $this->_customerSession->getCustomer();

            $this->_helperInstagram->connectByInstagramId(
                $customer,
                $userInfo->data->id,
                $token
            );
            if (!$isCheckoutPageReq) {
                $this->messageManager->addSuccess(
                    __(
                        'Your %1 account is now connected to your store account.
                    You can now login using our %1 Connect button or using store
                    account credentials you will receive to your email address.',
                        __(
                            'Instagram'
                        )
                    )
                );
            }
            $this->coreSession->setSuccessMsg(__(
                'Your %1 account is now connected to your store account.
            You can now login using our %1 Connect button or using store
            account credentials you will receive to your email address.',
                __(
                    'Instagram'
                )
            ));
            return;
        }
        if ($customersByInstagramId->count()) {
            // Existing connected user - login
            foreach ($customersByInstagramId as $customerInfo) {
                $customer = $customerInfo;
            }
            $this->_helperInstagram->loginByCustomer($customer);
            if (!$isCheckoutPageReq) {
                $this->messageManager
                    ->addSuccess(
                        __('You have successfully logged in using your %1 account.', __('Instagram'))
                    );
            }
            $this->coreSession->setSuccessMsg(__('You have successfully logged in using your %1 account.', __('Instagram')));
            return;
        }
    }
}
