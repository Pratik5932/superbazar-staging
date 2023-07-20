<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Helper;

use Magento\Framework\Controller\ResultInterface;
use Webkul\SocialSignup\Controller\Google\GoogleClient;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\ZendClient;

/**
 * Social Signup Google helper
 */
class Google extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var Webkul\SocialSignup\Controller\Google\GoogleClient
     */
    protected $_googleClient;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * @param Store                                      $store
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param GoogleClient                               $googleClient
     * @param Data                                       $dataHelper
     * @param \Magento\Customer\Model\Customer           $customerModel
     */
    public function __construct(
        Store $store,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        GoogleClient $googleClient,
        Data $dataHelper,
        \Magento\Customer\Model\Customer $customerModel,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->_dataHelper = $dataHelper;
        $this->_store = $store;
        $this->_googleClient = $googleClient;
        $this->_customerSession = $customerSession;
        $this->_customerModel = $customerModel;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * connect the customer by google id
     * @param  \Magento\Customer\Model\Customer $customer object of customer
     * @param  integer                           $googleId google id
     * @param  string                           $token    google access token
     */
    public function connectByGoogleId(
        $customeData,
        $googleId,
        $token
    ) {
        $customerId = '';
        foreach ($customeData as $key => $value) {
            $value->setSocialsignupGid($googleId);
            $value->setSocialsignupGtoken($token);
            $customerId = $value->save()->getId();
        }
        $this->_customerSession->loginById($customerId);
    }

    /**
     * login customer by creating account
     * @param  string $email     email id of customer
     * @param  string $firstName first name of customer
     * @param  string $lastName  last name of customer
     * @param  integer $googleId  google di of customer
     * @param  string $token     access token of customer
     */
    public function connectByCreatingAccount(
        $email,
        $firstName,
        $lastName,
        $googleId,
        $token
    ) {
        $customer = $this->_customerModel;
        
        $customer->setEmail($email)
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setSocialsignupGid($googleId)
                ->setSocialsignupGtoken($token)
                ->save();

        $customer->setConfirmation(null);
        $customer->save();
        try {
            $customer->sendNewAccountEmail();
        } catch (\Exception $e) {
            $this->logger->info('Helper Twitter connectByCreatingAccount '.$e->getMessage());
        }
        $this->_customerSession->loginById($customer->getId());
    }

    /**
     * loging by customer
     * @param  Magento\Customer\Model\Customer $customer customer object
     */
    public function loginByCustomer(\Magento\Customer\Model\Customer $customer)
    {
        if ($customer->getConfirmation()) {
            $customer->setConfirmation(null);
            $customer->save();
        }

        $this->_customerSession->loginById($customer->getId());
    }

    /**
     * sign in customer by goole id
     * @param  integer $googleId google id
     * @return object           collection of customer
     */
    public function getCustomersByGoogleId($googleId)
    {
        $customer = $this->_customerModel;

        $collection = $customer->getCollection()
            ->addAttributeToFilter('socialsignup_gid', $googleId)
            ->setPageSize(1);
        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter(
                'website_id',
                $this->_storeManager->getStore()->getWebsiteId()
            );
        }

        if ($this->_customerSession->isLoggedIn()) {
            if ($this->_customerSession->isLoggedIn()) {
                $collection->addFieldToFilter(
                    'entity_id',
                    ['neq' => $this->_customerSession->getCustomerId()]
                );
            }
        }

        return $collection;
    }
    
    /**
     * get customer collection by email
     * @param  string $email email of customer
     * @return object        collection of customer
     */
    public function getCustomersByEmail($email)
    {
        $customer = $this->_customerModel;

        $collection = $customer->getCollection()
                ->addFieldToFilter('email', $email)
                ->setPageSize(1);
        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter(
                'website_id',
                $this->_storeManager->getStore()->getWebsiteId()
            );
        }
        if ($this->_customerSession->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id',
                ['neq' =>  $this->_customerSession->getCustomerId()]
            );
        }
        return $collection;
    }
}
