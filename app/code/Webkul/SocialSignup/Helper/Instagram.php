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
use Webkul\SocialSignup\Controller\Instagram\InstagramClient;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\ZendClient;

/**
 * Social Signup data helper
 */
class Instagram extends \Magento\Framework\App\Helper\AbstractHelper
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
     * @var Webkul\SocialSignup\Controller\Instagram\InstagramClient
     */
    protected $_instagramClient;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * @param Store                                      $store
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param InstagramClient                            $instagramClient
     * @param Data                                       $dataHelper
     * @param \Magento\Customer\Model\Customer           $customerModel
     */
    public function __construct(
        Store $store,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        InstagramClient $instagramClient,
        Data $dataHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
    
        $this->_dataHelper = $dataHelper;
        $this->_store = $store;
        $this->_instagramClient = $instagramClient;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }

    /**
     * connect the customer by instagram id
     * @param  \Magento\Customer\Model\Customer $customer object of customer
     * @param  integer                           $instaId instragram id
     * @param  string                           $token    instragram access token
     */
    public function connectByInstagramId(
        \Magento\Customer\Model\Customer $customer,
        $instaId,
        $token
    ) {
    
        $customer->setSocialsignupInstaid($instaId)
            ->setSocialsignupInstatoken($token)
            ->save();

        $this->_customerSession->loginById($customer->getId());
    }

    /**
     * login customer by creating account
     * @param  string $email     email id of customer
     * @param  string $firstName first name of customer
     * @param  string $lastName  last name of customer
     * @param  integer $instaId  instagram id of customer
     * @param  string $token     access token of customer
     */
    public function connectByCreatingAccount(
        $email,
        $fullname,
        $instaId,
        $token
    ) {
    
        $customer = $this->customerFactory->create();

        $name = explode(' ', $fullname, 2);
         
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
            ->setSocialsignupLid($instaId)
            ->setSocialsignupLtoken($token)
            ->save();

        $customer->setConfirmation(null);
        $customer->save();

        try {
            $customer->sendNewAccountEmail();
        } catch (\Exception $e) {
            $this->_dataHelper
                    ->getLogger()
                    ->info('Helper Instagram connectByCreatingAccount '.$e->getMessage());
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
     * sign in customer by instragram id
     * @param  integer $instaId instagram id
     * @return object           collection of customer
     */
    public function getCustomersByInstagramId($instaId)
    {
        $customer = $this->customerFactory->create();
        $customerCollection = $this->customerFactory->create()
                    ->getCollection()
                    ->addAttributeToFilter('socialsignup_instaid', $instaId)
                    ->setPageSize(1);

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $customerCollection->addAttributeToFilter(
                'website_id',
                $this->_storeManager->getStore()->getWebsiteId()
            );
        }

        if ($this->_customerSession->isLoggedIn()) {
            $customerCollection->addFieldToFilter(
                'entity_id',
                ['neq' => $this->_customerSession->getCustomerId()]
            );
        }

        return $customerCollection;
    }

    /**
     * get customer collection by email
     * @param  string $email email of customer
     * @return object        collection of customer
     */
    public function getCustomersByEmail($email)
    {
        $customer = $this->customerFactory->create();
        $customerCollection = $this->customerFactory->create()
                ->getCollection()
                ->addFieldToFilter('email', $email)
                ->setPageSize(1);

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $customerCollection->addAttributeToFilter(
                'website_id',
                $this->_storeManager->getStore()->getWebsiteId()
            );
        }

        if ($this->_customerSession->isLoggedIn()) {
            $customerCollection->addFieldToFilter(
                'entity_id',
                ['neq' => $this->_customerSession->getCustomerId()]
            );
        }
        return $customerCollection;
    }
}
