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
namespace Webkul\MpStripe\Block;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\HTTP\Client\Curl;
use Webkul\MpStripe\Model\Source\AccountType;
use Webkul\MpStripe\Model\Source\BusinessIndustry;
use Webkul\MpStripe\Model\Source\CurrencyList;

/**
 * MpStripe block.
 *
 * @author Webkul Software
 */
class Connect extends \Magento\Framework\View\Element\Template
{
    const STRIPE_URL = "https://connect.stripe.com/";

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     *
     * @var $curl
     */
    private $curl;

    /**
     * @var Webkul\MpStripe\Model\ResourceModel\StripeSeller\CollectionFactory
     */
    private $stripeSellerFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    private $marketplaceHelper;

    /**
     * $helper.
     *
     * @var \Webkul\MpStripe\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
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
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
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
        \Magento\Framework\Json\Helper\Data $jsonHelper,
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
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * getCustomerDetails return customer details.
     *
     * @return array
     */
    public function getCurrentCustomer()
    {
        return $this->customerSession->getCustomer();
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
     * [getSellerDataBySellerId get currect seller profile data.
     *
     * @return array
     */
    public function getSellerDataBySellerId()
    {
        return $this->marketplaceHelper->getSeller();
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

    /**
     * getStripeSellerFactory get seller wise data.
     *
     * @return \Webkul\MpStripe\Model\ResourceModel\StripeSeller\Collection
     */
    public function getStripeSellerFactory()
    {
        return $this->stripeSellerFactory
            ->create()
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $this->marketplaceHelper->getCustomerId()]
            )
            ->addFieldToFilter(
                'email',
                ['eq' => $this->customerSession->getCustomer()->getEmail()]
            );
    }

    /**
     * getRequestData get Request Data.
     *
     * @return array
     */
    public function getRequestData()
    {
        return $this->_request->getParams();
    }

    /**
     * getStripeTokens get stripe tokens using cUrl.
     *
     * @param array $tokenRequestBody request params
     *
     * @return array respose generated by cUrl request
     */
    public function getStripeTokens($tokenRequestBody)
    {
        $url = self::STRIPE_URL."oauth/token";
        $bodyParams = $tokenRequestBody;

        $this->curl->post($url, $bodyParams);
        $responseBody = $this->curl->getBody();

        return $this->jsonHelper->jsonDecode($responseBody, true);
    }

    /**
     * checkValidAccount check account valid.
     *
     * @return int $flag
     */
    public function checkValidAccount()
    {
        try {
            $flag = 0;//not connected
            $customerId = $this->getCurrentCustomer()->getId();
            $stripeKey = $this->helper->getConfigValue('api_key');
            $stripeCustomerData = $this->helper->getStripeSeller($customerId);
            if ($stripeKey!="" && $customerId!=0
                && isset($stripeCustomerData['stripe_user_id'])
                && $stripeCustomerData['stripe_user_id']!="") {
                $this->helper->setUpDefaultDetails();
                $response = \Stripe\Account::retrieve($stripeCustomerData['stripe_user_id']);
                $flag = 1;//connected
            }
        } catch (\Exception $e) {
            $flag = 2;//admin account udpate
        }
        return $flag;
    }

    /**
     * creates url and checks it
     *
     * @param int $stripeClientId
     * @param string $contactEmail
     * @param string $data
     * @param string $storeUrl
     * @param string $userCountry
     * @param int $userId
     * @param string $customerFname
     * @param string $customerLname
     * @param int $storePhone
     * @param string $shopName
     * @param string $redirectUrl
     *
     * @return int
     */
    public function checkKeys(
        $stripeClientId,
        $contactEmail,
        $data,
        $storeUrl,
        $userCountry,
        $userId,
        $customerFname,
        $customerLname,
        $storePhone,
        $shopName,
        $redirectUrl
    ) {
        $base = self::STRIPE_URL."oauth/authorize?";
        $url = '';
        $url = $base.'client_id='.$stripeClientId
                    .$data
                    .'stripe_user[email]='.$contactEmail.'&stripe_user[url]='.$storeUrl
                    .'&stripe_user[country]='.$userCountry.'&stripe_user[currency]=usd&userid='.$userId
                    .'&stripe_user[first_name]='.$customerFname.'&stripe_user[last_name]='.$customerLname
                    .'&stripe_user[phone_number]='.$storePhone.'&stripe_user[business_name]='.$shopName
                    .'&stripe_user[redirect_uri]='.$redirectUrl;
        $this->curl->get($url);
        $error = strpos($this->curl->getBody(), "No application matches the supplied client identifier");
        return $error;
    }

    public function getIntegration()
    {
        return $this->helper->getIntegration();
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

    public function getBusinessType()
    {
        return $this->accontType->toOptionArray();
    }

    public function getBusinessIndustry()
    {
        return $this->businessIndustry->toOptionArray();
    }

    public function getCurrencyList()
    {
        return $this->currencyList->toOptionArray();
    }
    
    public function getStripeCustomAccount($stripeAccountId)
    {
        $this->helper->setUpDefaultDetails();
        return \Stripe\Account::retrieve($stripeAccountId);
    }

    public function getPreviousMessage()
    {
        return __('Please remove the previous connection and reauthorize using the Stripe system');
    }

    public function getConsentMessage()
    {
        $serviceAgreement = __('Services Agreement');
        $connectedAccountAgreement = __("Connected Account Agreement");
        $remainingMsg = __('certify that the information you have provided is complete and correct');
        return __(
            "By creating account, you agree to our %1, %2, and %3",
            '<a href="https://stripe.com/gb/legal" target="__blank">'.$serviceAgreement.'</a>',
            '<a href="https://stripe.com/gb/connect-account/legal" target="__blank">'.$connectedAccountAgreement.'</a>',
            $remainingMsg
        );
    }
}
