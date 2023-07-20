<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpStripe\Block\Adminhtml\Manage;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\HTTP\Client\Curl;
use Webkul\MpStripe\Model\Source\AccountType;
use Webkul\MpStripe\Model\Source\BusinessIndustry;
use Webkul\MpStripe\Model\Source\CurrencyList;

class Account extends \Magento\Backend\Block\Template
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\MpStripe\Model\ResourceModel\StripeSeller\CollectionFactory $stripeSellerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param AccountType $accontType
     * @param BusinessIndustry $businessIndustry
     * @param CurrencyList $currencyList
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param Curl $curl
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MpStripe\Model\ResourceModel\StripeSeller\CollectionFactory $stripeSellerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpStripe\Helper\Data $helper,
        AccountType $accontType,
        BusinessIndustry $businessIndustry,
        CurrencyList $currencyList,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        Curl $curl,
        array $data = []
    ) {
        $this->curl = $curl;
        $this->helper = $helper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->stripeSellerFactory = $stripeSellerFactory;
        $this->customerRepository = $customerRepository;
        $this->accontType = $accontType;
        $this->businessIndustry = $businessIndustry;
        $this->currencyList = $currencyList;
        $this->customerSession = $customerSession;
        $this->countryCollectionFactory = $countryCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * marketplaceHelper get marketplace helper.
     *
     * @return \Webkul\Marketplace\Helper\Data
     */
    public function marketplaceHelper()
    {
        return $this->marketplaceHelper;
    }

    /**
     * getCustomerDetails return customer details.
     *
     * @return array
     */
    public function getCurrentCustomer()
    {
        $sellerId = $this->getRequest()->getParam('seller_id');
        return $this->customerRepository->getById($sellerId);
    }

    public function getCountryCollection()
    {
        $collection = $this->countryCollectionFactory->create()->loadByStore();
        return $collection;
    }

    /**
     * Retrieve list of top destinations countries
     *
     * @return array
     */
    protected function getTopDestinations()
    {
        $destinations = (string)$this->_scopeConfig->getValue(
            'general/country/destinations',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }

    /**
     * Retrieve list of countries in array option
     *
     * @return array
     */
    public function getCountries()
    {
        return $options = $this->getCountryCollection()
                ->setForegroundCountries($this->getTopDestinations())
                    ->toOptionArray();
    }

    /**
     * to get business type list
     *
     * @return array
     */
    public function getBusinessType()
    {
        return $this->accontType->toOptionArray();
    }

    /**
     * to get business industries
     *
     * @return array
     */
    public function getBusinessIndustry()
    {
        return $this->businessIndustry->toOptionArray();
    }

    /**
     * to get currency list
     *
     * @return array
     */
    public function getCurrencyList()
    {
        return $this->currencyList->toOptionArray();
    }
    
    /**
     * to get stripe customer account details
     *
     * @param string $stripeAccountId
     * @return array
     */
    public function getStripeCustomAccount($stripeAccountId)
    {
        $stripeKey = $this->helper->getConfigValue('api_key');
        $this->helper->setUpDefaultDetails();
        return \Stripe\Account::retrieve($stripeAccountId);
    }
    
    /**
     * getStripeHelper get stripe helper.
     *
     * @return \Webkul\MpStripe\Helper\Data
     */
    public function getStripeHelper()
    {
        return $this->helper;
    }
}
