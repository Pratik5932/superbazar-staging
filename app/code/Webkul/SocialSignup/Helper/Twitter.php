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
use Webkul\SocialSignup\Controller\Twitter\TwitterClient;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\ZendClient;

/**
 * Social Signup Twitter helper
 */
class Twitter extends \Magento\Framework\App\Helper\AbstractHelper
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
     * @var Webkul\SocialSignup\ControllerTtwitter\TwitterClient
     */
    protected $_twitterClient;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * @param Store                                      $store
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param TwitterClient                              $twitterClient
     * @param Data                                       $dataHelper
     * @param \Magento\Customer\Model\Customer           $customerModel
     */
    public function __construct(
        Store $store,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        TwitterClient $twitterClient,
        Data $dataHelper,
        \Magento\Customer\Model\Customer $customerModel,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->_dataHelper = $dataHelper;
        $this->_store = $store;
        $this->_twitterClient = $twitterClient;
        $this->_customerSession = $customerSession;
        $this->_customerModel = $customerModel;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * connect the customer by twitter id
     * @param  \Magento\Customer\Model\Customer $customer object of customer
     * @param  integer                           $twitterId twitter id
     * @param  string                           $token    twitter access token
     */
    public function connectByTwitterId(
        \Magento\Customer\Model\Customer $customer,
        $twitterId,
        $token
    ) {
    
        $customer->setSocialsignupTid($twitterId)
                ->setSocialsignupTtoken($token)
                ->save();
        
        $this->_customerSession->loginById($customer->getId());
    }

    /**
     * login customer by creating account
     * @param  string $email     email id of customer
     * @param  string $name  full name of customer
     * @param  integer $twitterId  twitter id of customer
     * @param  string $token     access token of customer
     */
    public function connectByCreatingAccount(
        $email,
        $name,
        $twitterId,
        $token
    ) {
        
        $customer = $this->_customerModel;
        
        $name = explode(' ', $name, 2);
        
        if (count($name) > 1) {
            $firstName = $name[0];
            $lastName = $name[1];
        } else {
            $firstName = $name[0];
            $lastName = $name[0];
        }
        
        $customer->setEmail($email)
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setSocialsignupTid($twitterId)
                ->setSocialsignupTtoken($token)
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
      * sign in customer by twitter id
      * @param  integer $twitterId twitter id
      * @return object           collection of customer
      */
    public function getCustomersByTwitterId($twitterId)
    {
        $customer = $this->_customerModel;

        $collection = $customer->getCollection()
            ->addAttributeToFilter('socialsignup_tid', $twitterId)
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
                ['neq' => $this->_customerSession->getCustomerId()]
            );
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
                ['neq' => $this->_customerSession->getCustomerId()]
            );
        }
        return $collection;
    }
}
