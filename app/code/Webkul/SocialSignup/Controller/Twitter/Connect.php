<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Twitter;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\Generic;
use Magento\Store\Model\Store;
use Magento\Framework\Url;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Webkul\SocialSignup\Helper\Twitter;
use Magento\Framework\Exception\LocalizedException;

/**
 * Connect class of Twitter
 */
class Connect extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var Store
     */
    protected $_store;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var Url
     */
    protected $_url;

    /**
     * @var Attribute
     */
    protected $_eavAttribute;

    /**
     * @var Google
     */
    protected $_helperTwitter;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param Generic                                            $session
     * @param Context                                            $context
     * @param Store                                              $store
     * @param \Webkul\SocialSignup\Helper\Data                   $helper
     * @param Twitter                                            $helperTwitter
     * @param Attribute                                          $eavAttribute
     * @param \Magento\Framework\UrlInterface                    $urlinterface
     * @param TwitterClient                                      $twitterClient
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param \Magento\Framework\Controller\ResultInterface      $result
     * @param PageFactory                                        $resultPageFactory
     */
    public function __construct(
        Generic $session,
        Context $context,
        Store $store,
        Twitter $helperTwitter,
        Attribute $eavAttribute,
        TwitterClient $twitterClient,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\SocialSignup\Helper\Data $helper,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\RequestInterface $httpRequest
    ) {
    
        $this->_customerSession = $customerSession;
        $this->_helperTwitter = $helperTwitter;
        $this->_eavAttribute = $eavAttribute;
        $this->_store = $store;
        $this->_scopeConfig = $scopeConfig;
        $this->coreSession = $coreSession;
        $this->_session = $session;
        $this->_helper = $helper;
        $this->_twitterClient = $twitterClient;
        $this->_resultPageFactory = $resultPageFactory;
        $this->request = $httpRequest;
        parent::__construct($context);
    }

    /**
     * login customer
     */
    public function execute()
    {
        
        $helper = $this->_helper;
        try {
            $isSecure = $this->_store->isCurrentlySecure();
            $mainwProtocol = $this->_session->getIsSecure();
            $redirectPath = $this->_connectCallback();
            if ($redirectPath) {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath($redirectPath);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        if (!empty($this->referer)) {
            if (empty($this->flag)) {
                if (!$isSecure) {
                    $redirectUrl = $this->_url->getUrl('socialsignup/twitter/redirect/');
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
            $helper->redirect404($this);
        }
    }

    /**
     * get the infromation from end points
     */
    protected function _connectCallback()
    {
        $isCheckoutPageReq = 0;
        $isCheckoutPageReq = $this->_helper->getCoreSession()->getIsSocialSignupCheckoutPageReq();
        $domain =$this->request->getServer('SERVER_NAME');
        $attributegId = $this->_eavAttribute->getIdByCode('customer', 'socialsignup_tid');
        $attributegtoken = $this->_eavAttribute->getIdByCode('customer', 'socialsignup_ttoken');
        if ($attributegId == false || $attributegtoken == false) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Attribute socialsignup_tid or socialsignup_ttoken not exist')
            );
        }

        if (!($params = $this->getRequest()->getParams())
            ||
            !(
                $requestToken = $this->_session->getTwitterRequestToken()
            )
            ) {
            // Direct route access - deny
            return;
        }
        $this->referer = $this->_url->getCurrentUrl();
        
        if (isset($params['denied'])) {
            unset($this->referer);
            $this->flag = "noaccess";
            $this->_helper->closeWindow($this);
            return;
        }

        $this->_twitterClient->setParameters();

        $token = $this->_twitterClient->getAccessToken();

        $userInfo = $this->_twitterClient->api(
            '/account/verify_credentials.json',
            'GET',
            [
                'include_email' => 'true',
                'include_entities' => 'false',
                'skip_status' => 'true'
            ]
        );

        if (!isset($userInfo->emaill)) {
            $userInfo->email = $userInfo->screen_name.'@'.$domain;
        }
        $customersByTwitterId = $this->_helperTwitter
            ->getCustomersByTwitterId($userInfo->id);

        $this->_connectWithLoggedInCustomer($customersByTwitterId, $userInfo, $token);

        if ($customersByTwitterId->count()) {
            // Existing connected user - login
            foreach ($customersByTwitterId as $key => $customerInfo) {
                $customer = $customerInfo;
            }
            $this->_helperTwitter->loginByCustomer($customer);
            if (!$isCheckoutPageReq) {
                $this->messageManager
                    ->addSuccess(
                        __('You have successfully logged in using your %1 account.', __('Twitter'))
                    );
            }
            $this->coreSession->setSuccessMsg(__('You have successfully logged in using your %1 account.', __('Twitter')));
            return;
        }

        $customersByEmail = $this->_helperTwitter
            ->getCustomersByEmail($userInfo->email);

        if ($customersByEmail->count()) {
            // Email account already exists - attach, login
            foreach ($customersByEmail as $key => $customerInfo) {
                $customer = $customerInfo;
            }
            $this->_helperTwitter->connectByTwitterId(
                $customer,
                $userInfo->id,
                $token
            );
            if (!$isCheckoutPageReq) {
                $this->messageManager->addSuccess(
                    __(
                        'We have discovered you already have an account at our store.
                        Your %1 account is now connected to your store account.',
                        __(
                            'Twitter'
                        )
                    )
                );
            }
            $this->coreSession->setSuccessMsg(__(
                'We have discovered you already have an account at our store.
                Your %1 account is now connected to your store account.',
                __(
                    'Twitter'
                )
            ));
            return;
        }

        // New connection - create, attach, login
        if (empty($userInfo->name)) {
            throw new LocalizedException(
                __('Sorry, could not retrieve your %1 last name. Please try again.', __('Twitter'))
            );
        }
        if (empty($userInfo->email)) {
            $userInfo->email = $userInfo->screen_name.'@'.$domain;
        }
        if ($this->_helper->getCustomerAttributes()) {
            $name = explode(' ', $userInfo->name, 2);
            
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
                'email'     => $userInfo->email,
                'confirmation'  => null,
                'is_active' => 1,
                'socialsignup_tid' => $userInfo->id,
                'socialsignu_ttoken'    => $token,
                'label'     => __('twitter')
            ];
            $this->_helper->setInSession($customerData);
            return 'socialsignup/index/index';
        } else {
            $this->_helperTwitter->connectByCreatingAccount(
                $userInfo->email,
                $userInfo->name,
                $userInfo->id,
                $token
            );
        }
        
        if (!$isCheckoutPageReq) {
            $this->messageManager->addSuccess(
                __(
                    'Your Twitter account is now connected to your new user account at our store.
                    Now you can login using our Twitter Connect button.'
                )
            );
        }
        $this->coreSession->setSuccessMsg(__(
            'Your Twitter account is now connected to your new user account at our store.
            Now you can login using our Twitter Connect button.'
        ));
    }

    private function _connectWithLoggedInCustomer($customersByTwitterId, $userInfo, $token)
    {
        $isCheckoutPageReq = 0;
        $isCheckoutPageReq = $this->_helper->getCoreSession()->getIsSocialSignupCheckoutPageReq();
        if ($this->_customerSession->isLoggedIn()) {
            // Logged in user
            if ($customersByTwitterId->count()) {
                // Twitter account already connected to other account - deny
                if (!$isCheckoutPageReq) {
                    $this->messageManager
                    ->addNotice(
                        __('Your %1 account is already connected to one of our store accounts.', __('Twitter'))
                    );
                }
                $this->coreSession->setSuccessMsg(__('Your %1 account is already connected to one of our store accounts.', __('Twitter')));
                return;
            }

            // Connect from account dashboard - attach
            $customer = $this->_customerSession->getCustomer();

            $this->_helperTwitter->connectByTwitterId(
                $customer,
                $userInfo->id,
                $token
            );
            if (!$isCheckoutPageReq) {
                $this->messageManager->addSuccess(
                    __(
                        'Your %1 account is now connected to your store account.
                        You can now login using our %1 Connect button or using store
                        account credentials you will receive to your email address.',
                        __(
                            'Twitter'
                        )
                    )
                );
            }
            $this->coreSession->setSuccessMsg(__(
                'Your %1 account is now connected to your store account.
                You can now login using our %1 Connect button or using store
                account credentials you will receive to your email address.',
                __(
                    'Twitter'
                )
            ));
            return;
        }
    }
}
