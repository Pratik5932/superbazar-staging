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
namespace Webkul\MpStripe\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\MpStripe\Logger\StripeLogger;
use Webkul\MpStripe\Model\Source\IntegrationType;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * MpStripe data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper implements ArgumentInterface
{
    const METHOD_CODE = \Webkul\MpStripe\Model\PaymentMethod::METHOD_CODE;

    const MAX_SAVED_CARDS = 5;

    const CARD_IS_ACTIVE = 1;

    const CARD_NOT_ACTIVE = 0;

    const STRIPE_MODULE_NAME = 'Webkul Marketplace Stripe Payment Gateway For Magento 2';
    const STRIPE_MODULE_VERSION = '3.0.0';
    const STRIPE_MODULE_URL = 'https://store.webkul.com/magento2-marketplace-stripe-vendor-payment.html';
    const STRIPE_PARTNER_ID = 'pp_partner_FLJSvfbQDaJTyY';
    const STRIPE_API_VERSOIN = '2019-12-03';

    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * Customer session.
     *
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * $sellerProductCollectionFactory.
     */
    private $sellerProductCollectionFactory;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Saleperpartner\CollectionFactory
     */
    private $saleperpartnerCollectionFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $marketplaceHelperData;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $resolver;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Webkul\MpStripe\Logger\StripeLogger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serialize;

    /**
     * @var \Webkul\MpStripe\Model\StripeCustomer
     */
    private $stripeCustomerModel;

    /**
     * @var \Webkul\MpStripe\Model\StripeSeller
     */
    private $stripeSellerModel;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customerModel;

    /**
     * @var \Magento\Quote\Model\Quote\Item\Option
     */
    private $optionFactory;

    /**
     * @var \Magento\Sales\Model\Order\InvoiceFactory
     */
    private $invoiceModel;
    
    /**
     * $newvar variable to check if seller shipping used.
     *
     * @var string
     */
    private $newvar;

    /**
     * @param Session $customerSession
     * @param \Magento\Framework\App\Helper\Context $context
     * @param FormKeyValidator $formKeyValidator
     * @param DateTime $date
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $sellerProductCollectionFactory
     * @param \Webkul\Marketplace\Model\ResourceModel\Saleperpartner\CollectionFactory $saleperpartnerCollectionFactory
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelperData
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Locale\Resolver $resolver
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param StripeLogger $stripeLogger
     * @param \Webkul\MpStripe\Model\StripeCustomerFactory $stripeCustomerModel
     * @param \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerModel
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $optionFactory
     * @param \Magento\Sales\Model\Order\InvoiceFactory $invoiceModel
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Shipping\Model\ConfigFactory $configFactory
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Session\SessionManager $coreSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $serialize
     */
    public function __construct(
        Session $customerSession,
        \Magento\Framework\App\Helper\Context $context,
        FormKeyValidator $formKeyValidator,
        DateTime $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $sellerProductCollectionFactory,
        \Webkul\Marketplace\Model\ResourceModel\Saleperpartner\CollectionFactory $saleperpartnerCollectionFactory,
        \Webkul\Marketplace\Helper\Data $marketplaceHelperData,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Locale\Resolver $resolver,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        StripeLogger $stripeLogger,
        \Webkul\MpStripe\Model\StripeCustomerFactory $stripeCustomerModel,
        \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerModel,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Magento\Quote\Model\Quote\Item\OptionFactory $optionFactory,
        \Magento\Sales\Model\Order\InvoiceFactory $invoiceModel,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        PriceCurrencyInterface $priceCurrency,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Shipping\Model\ConfigFactory $configFactory,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Session\SessionManager $coreSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Serialize\Serializer\Json $serialize
    ) {
        $this->date = $date;
        $this->customerSession = $customerSession;
        $this->objectManager = $objectManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->sellerProductCollectionFactory = $sellerProductCollectionFactory;
        $this->saleperpartnerCollectionFactory = $saleperpartnerCollectionFactory;
        $this->marketplaceHelperData = $marketplaceHelperData;
        $this->productMetadata = $productMetadata;
        $this->resolver =  $resolver;
        $this->encryptor = $encryptor;
        $this->logger = $stripeLogger;
        $this->serialize = $serialize;
        $this->stripeCustomerModel = $stripeCustomerModel;
        $this->stripeSellerModel = $stripeSellerModel;
        $this->customerModel = $customerModel;
        $this->optionFactory = $optionFactory;
        $this->invoiceModel = $invoiceModel;
        $this->request = $context->getRequest();
        $this->customerRepository = $customerRepository;
        $this->mpHelper = $mpHelper;
        $this->priceCurrency = $priceCurrency;
        $this->addressFactory = $addressFactory;
        $this->configFactory = $configFactory;
        $this->orderFactory = $orderFactory;
        $this->coreSession = $coreSession;
        $this->mpProductFactory = $mpProductFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * getRequestData function return the request data
     *
     * @return array
     */
    public function getRequestData()
    {
        return $this->_request->getParams();
    }

    /**
     * checkInvoiceHaveShipping function getting the invoice data to check shipping amount
     *
     * @param int $id
     * @return array
     */
    public function checkInvoiceHaveShipping($id = null)
    {
        $invoice =
        $this->invoiceModel->create()
        ->load($id);
        return $invoice->getData();
    }
    /**
     * function to get Config Data.
     *
     * @return string
     */
    public function getConfigValue($field = false)
    {
        if ($field) {
            if ($field == 'api_key' || $field == 'api_publish_key' || $field == 'client_secret') {
                return $this->encryptor->decrypt($this->scopeConfig
                  ->getValue(
                      'payment/'.self::METHOD_CODE.'/'.$field,
                      \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                  ));
            } else {
                return $this->scopeConfig
                    ->getValue(
                        'payment/'.self::METHOD_CODE.'/'.$field,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
            }
        } else {
            return;
        }
    }

    /**
     * getIsActive check if payment method active.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->getConfigValue('active');
    }

    /**
     * saveStripeCustomer save stripe customer id for future payment.
     *
     * @return string stripe customer id
     */
    public function saveStripeCustomer($stripeCustomerId, $last4, $cardData = [], $isDuplicateCard = 0)
    {
        if ($this->customerSession->isLoggedIn()) {
            $savedCards = $this->getSavedCards();
            $cardCount = 0;
            if ($savedCards) {
                $cardCount = $savedCards->getSize();
            } else {
                $cardCount = 0;
            }
            if ($cardCount < self::MAX_SAVED_CARDS) {
                $data = [
                    'customer_id' => $this->customerSession->getCustomer()->getId(),
                    'is_active' => 1,
                    'stripe_customer_id' => $stripeCustomerId,
                    'last4' => $last4,
                    'website_id' => $this->storeManager->getStore()->getWebsiteId(),
                    'store_id' => $this->storeManager->getStore()->getId(),
                    'fingerprint' => $cardData['fingerprint'],
                    'expiry_month' => $cardData['expiry_month'],
                    'expiry_year' => $cardData['expiry_year']
                ];
                try {
                    $model =
                    $this->stripeCustomerModel->create();
                    if ($isDuplicateCard) {
                        $model->load($cardData['fingerprint'], 'fingerprint');
                        $model->setExpiryMonth($cardData['expiry_month']);
                        $model->setExpiryYear($cardData['expiry_month']);
                        $model->setStripeCustomerId($stripeCustomerId);
                        $model->save();
                    } else {
                        $model->setData($data);
                        $model->save();
                    }
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
        }
    }

    /**
     * saveStripeSeller connect stripe seller to admin stripe account.
     *
     * @return bool
     */
    public function saveStripeSeller($data = false)
    {
        if ($data) {
            $seller =
            $this->customerModel->create()
                ->load($data['user_id']);
            $email = $seller->getEmail();
            $savedSeller = $this->getStripeSeller($data['user_id']);
            $wholedata = [
                    'seller_id' => $seller->getId(),
                    'is_active' => 1,
                    'stripe_key' => $data['key'],
                    'email' => $email,
                    'website_id' => $this->storeManager->getStore()->getWebsiteId(),
                    'store_id' => $this->storeManager->getStore()->getId(),
                    'is_verified' => $data['isverified'],
                    'user_type' => $data['user_type'],
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                    'stripe_user_id' => $data['stripe_user_id'],
                    'integration_type' => '1',
                ];
            if ($savedSeller) {
                try {
                    $model =
                    $this->stripeSellerModel->create()
                        ->load($savedSeller->getId());
                    $model->setData($wholedata);
                    $model->save();
                } catch (\Exception $e) {
                    return false;
                }
            } else {
                try {
                    $model =
                    $this->stripeSellerModel->create();
                    $model->setData($wholedata);
                    $model->save();
                } catch (\Exception $e) {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * savecustomStripeSeller connect account.
     *
     * @return bool
     */
    public function saveCustomStripeSeller($data = false)
    {
        if ($data) {
            $seller =
            $this->customerModel->create()
                ->load($data['user_id']);
            $email = $seller->getEmail();
            $savedSeller = $this->getStripeSeller($data['user_id']);
            $wholedata = [
                    'seller_id' => $seller->getId(),
                    'is_active' => 1,
                    'email' => $email,
                    'website_id' => $this->storeManager->getStore()->getWebsiteId(),
                    'store_id' => $this->storeManager->getStore()->getId(),
                    'is_verified' => $data['individual']['verification']['status'],
                    'user_type' => $data['type'],
                    'stripe_user_id' => $data['id'],
                    'integration_type' => '2',
                ];
            if ($savedSeller) {
                try {
                    $model =
                    $this->stripeSellerModel->create()
                        ->load($savedSeller->getId());
                    $model->setData($wholedata);
                    $model->save();
                } catch (\Exception $e) {
                    return false;
                }
            } else {
                try {
                    $model =
                    $this->stripeSellerModel->create();
                    $model->setData($wholedata);
                    $model->save();
                } catch (\Exception $e) {
                    return false;
                }
            }
        } else {
            return false;
        }
    }
    /**
     * getSavedCards function to get saved cards of the customer.
     *
     * @return Webkul\MpStripe\Model\StripeCustomer
     */
    public function getSavedCards()
    {
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomer()->getId();
            $collection =
            $this->stripeCustomerModel->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);
            if ($collection->getSize() > 0) {
                return $collection;
            } else {
                return false;
            }
        }
    }

    /**
     * getStripeSeller get stripe seller connect details.
     *
     * @param bool $customerId
     *
     * @return Webkul\MpStripe\Model\StripeSeller or boolean
     */
    public function getStripeSeller($customerId = false)
    {
        if ($customerId) {
            $collection =
            $this->stripeSellerModel->create()
                ->getCollection()
                ->addFieldToFilter('seller_id', ['eq' => $customerId])
                ->getFirstItem();
            if ($collection && $collection->getId()) {
                return $collection;
            } else {
                return false;
            }
        }
    }

    /**
     * getSellerDetail get seller commission details
     *
     * @param  string $sellerId
     * @return array
     */
    public function getSellerDetail($sellerId = '')
    {
        if ($sellerId) {
            $sellerdetails = $this->saleperpartnerCollectionFactory
                ->create()
                ->addFieldToFilter('seller_id', $sellerId);
            if ($sellerdetails->getSize()) {
                foreach ($sellerdetails as $temp) {
                    if ($temp->getCommissionRate() > 0) {
                        return [
                            'id' => $temp->getSellerId(),
                            'commission' => $temp->getCommissionRate(),
                        ];
                    } else {
                        return [
                            'id' => $temp->getSellerId(),
                            'commission' => $this->marketplaceHelperData->getConfigCommissionRate(),
                        ];
                    }
                }
            } else {
                return [
                    'id' => $sellerId,
                    'commission' => $this->marketplaceHelperData->getConfigCommissionRate(),
                ];
            }
        } else {
            return ['id' => 0,'commission' => 0];
        }
    }

    /**
     * [getAssignSellerId function to get assign seller id from order item.
     *
     * @param object $item
     *
     * @return int
     */
    public function getAssignSellerId($item)
    {
        // Get Info Buy Request from quote item,
        $itemOption =
        $this->optionFactory->create()
            ->getCollection()
            ->addFieldToFilter('item_id', $item->getId())
            ->addFieldToFilter('code', 'info_buyRequest');

        foreach ($itemOption as $option) {
            $info = $option->getValue();
        }

        if (preg_match("/^2\.[0-1]\.\d/", $this->productMetadata->getVersion())) {
            $info = $this->serialize->unserialize($info);
        }
        if (preg_match("/^2\.2\.\d/", $this->productMetadata->getVersion())) {
            $info = $this->jsonHelper->jsonDecode($info, true);
        }

        //Get mpassignproduct_id from $info
        $assignId = 0;
        $sellerId = 0;
        if (isset($info['mpassignproduct_id'])) {
            $assignId = $info['mpassignproduct_id'];
            $mpassignModel = $this->objectManager
                ->create(\Webkul\MpAssignProduct\Model\Items::class)
                ->load($assignId);
            $sellerId = $mpassignModel->getSellerId();
        }
        return $sellerId;
    }

    /**
     * getLocaleFromConfiguration get the current set locale code from configuration.
     *
     * @return String
     */
    public function getLocaleFromConfiguration()
    {
        return $this->resolver->getLocale();
    }

    /**
     * getLocaleForStripe return the locale value exixt in stripe api
     * other wise return "auto"
     *
     * @return String
     */
    public function getLocaleForStripe()
    {
        $configLocale = $this->getLocaleFromConfiguration();
        if ($configLocale) {
            $temp = explode('_', $configLocale);
            if (isset($temp['0'])) {
                $configLocale = $temp['0'];
            }
        }
        $stripeLocale = $this->matchCodeSupportedByStripeApi($configLocale);
        return $stripeLocale;
    }

    /**
     * matchCodeSupportedByStripeApi matches the configuration locale to the locale exixt in strip api
     *
     * @param [String] $configLocale
     * @return String
     */
    public function matchCodeSupportedByStripeApi($configLocale)
    {
        $returnLocale = '';
        switch ($configLocale) {
            case "zh":
                $returnLocale = $configLocale;
                break;
            case "da":
                $returnLocale = $configLocale;
                break;
            case "nl":
                $returnLocale = $configLocale;
                break;
            case "en":
                $returnLocale = $configLocale;
                break;
            case "fi":
                $returnLocale = $configLocale;
                break;
            case "fr":
                $returnLocale = $configLocale;
                break;
            case "de":
                $returnLocale = $configLocale;
                break;
            case "it":
                $returnLocale = $configLocale;
                break;
            case "ja":
                $returnLocale = $configLocale;
                break;
            case "no":
                $returnLocale = $configLocale;
                break;
            case "es":
                $returnLocale = $configLocale;
                break;
            case "sv":
                $returnLocale = $configLocale;
                break;
            default:
                $returnLocale = "auto";
                break;
        }
        return $returnLocale;
    }

    /**
     * customerExist function
     *
     * @param string $custID
     * @return boolean
     */
    public function customerExist($custID = null)
    {
        try {
            $this->setUpDefaultDetails();
            $customer = \Stripe\Customer::retrieve($custID);
            if (isset($$customer['id'])) {
                return 1;
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    //check seller connected
    public function isSellerConnected()
    {
        $flag = 0;
        $customerId = $this->customerSession->getCustomer()->getId();
        if ($this->getStripeSeller($customerId)) {
            $flag = 1;
        }
        return $flag;
    }

    /**
     * Converts amount into cents for specific currencies
     *
     * @param $totalAmount
     * @param $currency
     * @return int
     */
    public function calculateAmount($totalAmount, $currency)
    {
        $smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
                                "vnd", "vuv", "xaf", "xof", "xpf"];
        if (in_array($currency, $smallcurrencyarray)) {
            return $totalAmount;
        } else {
            return $totalAmount * 100;
        }
    }

    public function getIntegration()
    {
        $globalIntegration = $this->getConfigValue('integration');
        if ($globalIntegration == IntegrationType::STRIPE_CONNECT) {
            return true;
        } elseif ($globalIntegration == IntegrationType::STRIPE_CONNECT_CUSTOM) {
            return false;
        } else {
            return true;
        }
    }

    public function checkStripeAccount($stripeSellerId)
    {
        $stripeSeller = $this->stripeSellerModel->create()
        ->getCollection()
        ->addFieldToFilter('stripe_user_id', ['eq' => $stripeSellerId])
        ->getFirstItem();
        $sellerIntergration = $stripeSeller->getIntegrationType();
        if ($sellerIntergration == IntegrationType::STRIPE_CONNECT_CUSTOM) {
            return true;
        } else {
            return false;
        }
    }

    public function getStripeIntegrationInfo($sellerId)
    {
        $stripeSeller = $this->stripeSellerModel->create()
        ->getCollection()
        ->addFieldToFilter('seller_id', ['eq' => $sellerId])
        ->getFirstItem();
        $sellerIntergration = $stripeSeller->getIntegrationType();
        if ($sellerIntergration == IntegrationType::STRIPE_CONNECT_CUSTOM) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Order item array data to create invoice.
     *
     * @param $order
     * @param $items
     *
     * @return array
     */
    public function _getItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getProductId(), $items)) {
                $data[$item->getItemId()] = (int)
                    $item->getQtyOrdered() - $item->getQtyInvoiced();

                $_item = $item;

                // for bundle product
                $bundleitems = $this->mergeBundleItems($_item);

                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] = (int)
                                $_bundleitem->getQtyOrdered() - $item->getQtyInvoiced();
                        }
                    }
                }
                $subtotal += $_item->getRowTotal();
                $baseSubtotal += $_item->getBaseRowTotal();
            } else {
                if (!$item->getParentItemId()) {
                    $data[$item->getItemId()] = 0;
                }
            }
        }

        return [
            'data' => $data,
            'subtotal' => $subtotal,
            'baseSubtotal' => $baseSubtotal,
        ];
    }

    public function mergeBundleItems($_item)
    {
        return array_merge(
            [$_item],
            $_item->getChildrenItems()
        );
    }

    /**
     * getIfSellerInCart function to check if seller present in the cart.
     *
     * @param array $paymentDetails
     *
     * @return bool true | false
     */
    public function getIfSellerInCart($paymentDetails = [])
    {
        if (!empty($paymentDetails)) {
            foreach ($paymentDetails as $pd) {
                $cart = $pd['cart'];
                if (isset($cart['seller']) && $cart['seller']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getSellerCart($order = false)
    {
        $finalCart = [];
        if ($order) {
            $cartItems = $order->getAllVisibleItems();
            $cart = [];
            $i = 0;
            $orderShippingTaxAmount = 0;
            $orderShippingAmount = 0;
            $customerAddressId = 0;
            $sellerId = 0;
            $commissionDetail = [];
            if (!empty($order->getShippingAddress())) {
                /**
                 * $shipmeth shipping method code.
                 *
                 * @var string
                 */
                $shipmeth = $order->getShippingAddress()->getShippingMethod();
                /**
                 * $orderShippingTaxAmount get shipping tax amount if any.
                 *
                 * @var decimal
                 */
                $orderShippingTaxAmount = $order->getShippingAddress()
                    ->getData('shipping_tax_amount');

                /**
                 * $orderShippingAmount order shipping amount.
                 *
                 * @var decimal
                 */
                $orderShippingAmount = $order->getShippingAddress()
                    ->getShippingAmount();

                /**
                 * $customerAddressId customer shipping address id.
                 *
                 * @var int
                 */
                $customerAddressId = $order->getShippingAddress()
                    ->getCustomerAddressId();
            } else {
                /**
                 * $shipmeth if shipping address not set them shipping method is none.
                 */
                $shipmeth = '';
                /**
                 * [$customerAddressId billing address id.
                 */
                $customerAddressId = $order->getBillingAddress()
                    ->getCustomerAddressId();
            }
            /**
             * $customerAddressId if no shipping address id found then get billing address id.
             */
            if ($customerAddressId == null) {
                $customerAddressId = $order->getBillingAddress()
                    ->getCustomerAddressId();
            }
            /**
             * $customerAddress load customer address to get address fields object and get all the active
             * carriers.
             *
             * @var Magento\Customer\Model\Address
             */
            $customerAddress = $this->addressFactory->create()->load($customerAddressId);
            $customName = $customerAddress['firstname'].' '.$customerAddress['lastname'];
            $methods = $this->configFactory->create()->getActiveCarriers();
            $options = [];
            $allmethods = [];
            $shipinf = [];
            foreach ($methods as $_code => $_method) {
                array_push($allmethods, $_code);
            }

            /**
             * $shipmeth if marketplace multishipping is set then seller wise shipping amount.
             */
            if ($shipmeth == 'mpmultishipping_mpmultishipping') {
                $this->newvar = 'webkul';
                /**
                 * $shippinginfo get multi shipping details from sesssion.
                 *
                 * @var array
                 */
                $shippinginfo = $this->coreSession->getData(
                    'selected_methods'
                );
                foreach ($shippinginfo as $key => $val) {
                    $shipinf[] = ['seller' => $val['sellerid'],'amount' => $val['price']];
                }
            } else {
                $shipmethod = explode('_', $shipmeth, 2);
                /**
                 * $shippinginfo get shipping info details from session no multi shipping.
                 *
                 * @var array
                 */
                $shippinginfo = $this->coreSession->getShippingInfo();
                /*
                 * create seller wise shipping details array
                 */
                if (in_array($shipmethod[0], $allmethods)) {
                    if (!empty($shippinginfo[$shipmethod[0]])) {
                        $shipinf[] = $this->createSellerWiseShipping($shippinginfo[$shipmethod[0]], $shipinf);
                    }
                }
            }
            /**
            * $marketplaceHelper get marketplace helper.
            *
            * @var Webkul\Marketplace\Helper\Data
            */
            $marketplaceHelper = $this->mpHelper;
            /*
             * dispatch event for advance commission module
             */
            $this->_eventManager->dispatch(
                'mp_advance_commission_rule',
                ['order' => $order]
            );

            /**
            * $advanceCommissionRule advance commssion rule from session if set.
            */
            $advanceCommissionRule = $this->customerSession->getData(
                'advancecommissionrule'
            );

            /*
             * iterate through cart items to create seller wise payment details
             */
            foreach ($cartItems as $item) {
                $invoiceprice = 0;
                $itemId = $item->getProductId();
                $sellerId = 0;
                /**
                 * $assignSellerId variable to store assign seller id.
                 *
                 * @var int
                 */
                $assignSellerId = $this->getAssignSellerId($item);

                $seller = $this->mpProductFactory->create()->getCollection();

                /*
                 * check if assign seller id exists then add filter for seller id else product id
                 */
                if ($assignSellerId) {
                    $sellerId = $assignSellerId;
                } else {
                    $seller->addFieldToFilter('mageproduct_id', $itemId);
                    if ($seller->getSize() > 0) {
                        foreach ($seller as $obj) {
                            $sellerId = $obj->getSellerId();
                        }
                    }
                }

                $tempcoms = 0;
                /*
                 * get advance commission rule
                 */
                if (!$marketplaceHelper->getUseCommissionRule()) {
                    /*
                     * dispatch event for the calculation of advance commission
                     */
                    $this->_eventManager->dispatch(
                        'mp_advance_commission',
                        ['id' => $itemId]
                    );
                    /**
                     * [$advancecommission get Advance commission from the session.
                     *
                     * @var decimal
                     */
                    $advancecommission = $this->customerSession->getData(
                        'commission'
                    );
                    /*
                     * check if advance commission is set then calculate commission
                     */
                    if ($advancecommission != '') {
                        $percent = $advancecommission;
                        $commType = $marketplaceHelper->getCommissionType();
                        if ($commType == 'fixed') {
                            $tempcoms = $percent;
                        } else {
                            $tempcoms = ($item->getRowTotal() * $advancecommission) / 100;
                        }
                        if ($tempcoms > $item->getRowTotal()) {
                            $tempcoms = $item->getRowTotal() * $marketplaceHelper->getConfigCommissionRate() / 100;
                        }

                        $commissionDetail['id'] = $sellerId;
                    }
                } else {
                    /*
                     * check if advance commission is not set then set calculate advance commission
                     */
                    if (!empty($advanceCommissionRule)) {
                        if ($advanceCommissionRule[$item->getId()]['type'] == 'fixed') {
                            $tempcoms = $advanceCommissionRule[$item->getId()]['amount'];
                        } else {
                            $tempcoms =
                                ($item->getRowTotal() * $advanceCommissionRule[$item->getId()]['amount']) / 100;
                        }

                        $commissionDetail['id'] = $sellerId;
                    }
                }

                /*
                 * if there is no advance commission then calculate normal commission
                 */
                if (!$tempcoms) {
                    $commissionDetail = $this->getSellerDetail($sellerId);

                    if ($commissionDetail['id'] !== 0
                        && $commissionDetail['commission'] !== 0
                    ) {
                        $tempcoms = $this->priceCurrency->round(
                            ($item->getRowTotal() * $commissionDetail['commission']) / 100,
                            2
                        );
                    }
                }

                /**
                 * $price price after commission.
                 *
                 * @var decimal
                 */
                $price = $this->priceCurrency->round($item->getRowTotal() - $tempcoms);
                /**
                 * $invoiceprice row total of product.
                 *
                 * @var decimal
                 */
                $invoiceprice = $item->getRowTotal();
                /**
                 * include tax amount if tax management done by seller
                 */
                if ($sellerId && $marketplaceHelper->getConfigTaxManage()) {
                    $invoiceprice = $this->priceCurrency->round($invoiceprice);
                } elseif ($sellerId==0) {
                    /**
                     * else tax managed by admin
                     */
                    $invoiceprice = $this->priceCurrency->round($invoiceprice + $orderShippingTaxAmount);
                }

                $shippingprice = 0;
                if ($this->newvar == 'webkul') {
                    $custid = 0;

                    $custid = $sellerId;

                    foreach ($shipinf as $k => $key) {
                        if ($key['seller'] == $custid) {
                            $price = $price + $key['amount'];
                            $shippingprice = $key['amount'];
                            $shipinf[$k]['amount'] = 0;
                        }
                    }
                }
                /*
                 * create seller wise array for payment
                 */
                if ($orderShippingTaxAmount !== 0
                    && ($commissionDetail['id'] == 0)
                ) {
                    $this->adminShipTaxAmt = 1;
                    $cart[$i]['data'] = [
                        'seller_id' => $commissionDetail['id'],
                        'commission' => $tempcoms,
                        'product_id' => $item->getProductId(),
                        'price' => $this->calculateAmount(
                            $price,
                            strtolower($order->getStore()->getCurrentCurrencyCode())
                        ),
                        'invoice_price' => $invoiceprice,
                        'shipping_price' => $shippingprice,
                        'tax_amount' => $this->priceCurrency->round($item->getTaxAmount() + $orderShippingTaxAmount),
                        'discount_amount' => $item->getDiscountAmount()
                    ];
                } else {
                    $cart[$i]['data'] = [
                        'seller_id' => $commissionDetail['id'],
                        'commission' => $tempcoms,
                        'product_id' => $item->getProductId(),
                        'price' => $this->calculateAmount(
                            $price,
                            strtolower($order->getStore()->getCurrentCurrencyCode())
                        ),
                        'invoice_price' => $invoiceprice,
                        'shipping_price' => $shippingprice,
                        'tax_amount' => $item->getTaxAmount(),
                        'discount_amount' => $item->getDiscountAmount()
                    ];
                }
                ++$i;
            }
            return $cart;
        } else {
            throw new LocalizedException(
                _(
                    'There was an error capturing the transaction: Please contact admin'
                )
            );
        }
    }

    public function createSellerWiseShipping($shippinginfo, $shipinf)
    {
        foreach ($shippinginfo as $key) {
            $this->_newvar = 'webkul';
            foreach ($key['submethod'] as $k => $v) {
                if ($k == $shipmethod[1]) {
                    $shipinf[] = [
                            'seller' => $key['seller_id'],
                            'amount' => $v['cost'],
                        ];
                }
            }
        }
        return $shipinf;
    }

    public function getFinalCart($order = false)
    {
        if ($order) {
            $shippingDiscount = $order->getBaseShippingDiscountAmount();
            $cart = $this->getSellerCart($order);
            usort($cart, function ($a, $b) {
                return $b['data']['seller_id'] - $a['data']['seller_id'];
            });
            $commission = 0;
            /**
             * $orderShippingTaxAmount get shipping tax amount if any.
             *
             * @var decimal
             */
            $orderShippingTaxAmount = 0;
            if ($order->getShippingAddress()) {
                $orderShippingTaxAmount = $order->getShippingAddress()
                ->getData('shipping_tax_amount');
            }
            $finalcart = [];
            $this->counterVal = 0;
            $adminTaxAmount = 0;

            foreach ($cart as $item) {
                $stripeSecretKey = '';
                $accessToken = '';
                $publishKey = '';
                $stripeUserId = '';
                $temp = $item['data'];
                $commission += $temp['commission'];

                if ($temp['seller_id'] != 0) {
                    $mpStripe = $this->getStripeSeller($temp['seller_id']);
                    $mpstripeAccessToken = 0;
                    $mpstripePublishKey = 0;
                    $userId = 0;
                    if ($mpStripe) {
                        $mpstripeAccessToken = $mpStripe['access_token'];
                        $mpstripePublishKey = $mpStripe['stripe_key'];
                        $userId = $mpStripe['stripe_user_id'];
                    }

                    if ($mpstripeAccessToken && $mpstripePublishKey) {
                        $accessToken = $mpstripeAccessToken;
                        $publishKey = $mpstripePublishKey;
                        $stripeUserId = $userId;
                    } elseif ($mpStripe['integration_type'] == IntegrationType::STRIPE_CONNECT_CUSTOM) {
                        $stripeUserId = $userId;
                    } else {
                        $stripeSecretKey = $this->getConfigValue('api_key');
                    }
                } else {
                    $stripeSecretKey = $this->getConfigValue('api_key');
                }
                $adminTaxAmount += $temp['tax_amount'];

                $finalcart = $this->calculateFinalCart(
                    $finalcart,
                    $temp,
                    $stripeSecretKey,
                    $accessToken,
                    $publishKey,
                    $stripeUserId
                );
            }
            $status = 0;
            $index = 0;
            $counter = 0;
            foreach ($finalcart as $cart) {
                if (!isset($cart['seller']) || $cart['seller'] == 0) {
                    $status = 1;
                    $index = $counter;
                }
                ++$counter;
            }
            $adminShippingTax = 0;
            $quoteshipPrice = 0;
            if ($this->newvar != 'webkul') {
                $quoteshipPrice = $order->getShippingAmount();
            }
            /**
             * if admin product
             */
            $finalcart = $this->calculateAdminProduct(
                $finalcart,
                $counter,
                $adminTaxAmount,
                $commission,
                $quoteshipPrice,
                $shippingDiscount,
                $orderShippingTaxAmount,
                $order,
                $status,
                $index
            );
        }
        return $finalcart;
    }

    /**
     * calculates final cart
     *
     * @param array $finalcart
     * @param array $temp
     * @param string $stripeSecretKey
     * @param string $accessToken
     * @param string $publishKey
     * @param string $stripeUserId
     * @return array $finalcart
     */
    private function calculateFinalCart(
        $finalcart,
        $temp,
        $stripeSecretKey,
        $accessToken,
        $publishKey,
        $stripeUserId
    ) {
    
        if ($this->counterVal == 0) {
            $i=$this->counterVal;
            $finalcart[$i]['shippingprice'] = $temp['shipping_price'];
            $finalcart[$i]['invoiceprice'] = $temp['invoice_price'];
            $finalcart[$i]['commission'] = $temp['commission'];
            $finalcart[$i]['price'] = $temp['price'];
            $finalcart[$i]['products'] = $temp['product_id'];
            if ($temp['seller_id']==0 && !$this->mpHelper->getConfigTaxManage()) {
                $finalcart[$i]['taxamount'] = $temp['tax_amount'];
            } elseif ($this->mpHelper->getConfigTaxManage()) {
                $finalcart[$i]['taxamount'] = $temp['tax_amount'];
            } else {
                $finalcart[$i]['taxamount'] = 0;
            }
            $finalcart[$i]['discount'] = $temp['discount_amount'];
            $finalcart[$i]['seller'] = $temp['seller_id'];
            $finalcart[$i]['stripe_secret_key'] = $stripeSecretKey;
            $finalcart[$i]['access_token'] = $accessToken;
            $finalcart[$i]['publish_key'] = $publishKey;
            $finalcart[$i]['stripe_user_id'] = $stripeUserId;
            ++$this->counterVal;
        } else {
            $i=$this->counterVal;
            if ($temp['seller_id'] == $finalcart[$i - 1]['seller']) {
                $finalcart[$i - 1]['price'] =
                $this->priceCurrency->round(
                    $finalcart[$i - 1]['price'] + $temp['price']
                );

                $finalcart[$i - 1]['invoiceprice'] =
                $finalcart[$i - 1]['invoiceprice'] +
                $temp['invoice_price'];
                $finalcart[$i - 1]['commission'] = $finalcart[$i - 1]['commission'] + $temp['commission'];
                $finalcart[$i - 1]['products'] =
                $finalcart[$i - 1]['products'].','.$temp['product_id'];
                $finalcart[$i - 1]['discount'] = $finalcart[$i - 1]['discount'] + $temp['discount_amount'];
                if ($temp['seller_id']==0 && !$this->mpHelper->getConfigTaxManage()) {
                    $finalcart[$i - 1]['taxamount'] =
                    $finalcart[$i - 1]['taxamount'] +
                    $temp['tax_amount'];
                } elseif ($this->mpHelper->getConfigTaxManage()) {
                    $finalcart[$i - 1]['taxamount'] =
                    $finalcart[$i - 1]['taxamount'] +
                    $temp['tax_amount'];
                } else {
                    $finalcart[$i - 1]['taxamount'] = 0;
                }
            } else {
                $finalcart[$i]['shippingprice'] = $temp['shipping_price'];
                $finalcart[$i]['invoiceprice'] = $temp['invoice_price'];
                $finalcart[$i]['commission'] = $temp['commission'];
                $finalcart[$i]['price'] = $temp['price'];
                $finalcart[$i]['products'] = $temp['product_id'];
                $finalcart[$i]['discount'] = $temp['discount_amount'];
                if ($temp['seller_id']==0 && !$this->mpHelper->getConfigTaxManage()) {
                    $finalcart[$i]['taxamount'] = $temp['tax_amount'];
                } elseif ($this->mpHelper->getConfigTaxManage()) {
                    $finalcart[$i]['taxamount'] = $temp['tax_amount'];
                } else {
                    $finalcart[$i]['taxamount'] = 0;
                }
                $finalcart[$i]['seller'] = $temp['seller_id'];
                $finalcart[$i]['stripe_secret_key'] = $stripeSecretKey;
                $finalcart[$i]['access_token'] = $accessToken;
                $finalcart[$i]['publish_key'] = $publishKey;
                $finalcart[$i]['stripe_user_id'] = $stripeUserId;
                ++$this->counterVal;
            }
        }
        return $finalcart;
    }

    /**
     * calculate for admin product
     *
     * @param array $finalcart
     * @param int $counter
     * @param float $adminTaxAmount
     * @param float $commission
     * @param float $quoteshipPrice
     * @param float $shippingDiscount
     * @param float $orderShippingTaxAmount
     * @param array $order
     * @param int $status
     * @return array $finalcart
     */
    private function calculateAdminProduct(
        $finalcart,
        $counter,
        $adminTaxAmount,
        $commission,
        $quoteshipPrice,
        $shippingDiscount,
        $orderShippingTaxAmount,
        $order,
        $status,
        $index
    ) {
    
        if ($status == 1) {
            $finalcart[$index]['price'] =
            $finalcart[$index]['price'] +
            $finalcart[$index]['taxamount'] +
            $quoteshipPrice;
            if (!$this->mpHelper->getConfigTaxManage()) {
                $finalcart[$index]['taxamount'] = $adminTaxAmount;
            }

            $finalcart[$index]['shippingprice'] =
            $finalcart[$index]['shippingprice'] +
            $quoteshipPrice;
        } else {
            /**
             * if commission or admin shipping both are set
             */
            if ($commission != 0 || $quoteshipPrice != 0) {
                /**
                 * if admin shipping is used
                 */
                $finalcart = $this->calculatePrice(
                    $finalcart,
                    $counter,
                    $quoteshipPrice,
                    $shippingDiscount,
                    $orderShippingTaxAmount
                );

                $stripeSecretKey = $this->getConfigValue('api_key');

                $finalcart[$counter]['seller'] = 0;
                $finalcart[$counter]['stripe_secret_key'] = $stripeSecretKey;
                
                $adminTaxAmount = $this->calculateAdminTaxAmount($adminTaxAmount, $order);

                $finalcart[$counter]['taxamount'] = $adminTaxAmount;
                $finalcart[$counter]['access_token'] = null;
                $finalcart[$counter]['publish_key'] = null;
                $finalcart[$counter]['stripe_user_id'] = null;
                $finalcart[$counter]['products'] = null;
                $finalcart[$counter]['discount'] = 0;
            }
        }
        $this->logger->critical("final cart value - ".$this->jsonHelper->jsonEncode($finalcart));
        return $finalcart;
    }

    /**
     * calculate adminTaxAmount
     *
     * @param float $adminTaxAmount
     * @param array $order
     * @return float adminTaxAmount
     */
    private function calculateAdminTaxAmount($adminTaxAmount, $order)
    {
        if ($order->getTaxAmount()<$adminTaxAmount) {
            $adminTaxAmount = $this->priceCurrency->round($adminTaxAmount - $order->getTaxAmount());
        } else {
            $adminTaxAmount = $this->priceCurrency->round($order->getTaxAmount() - $adminTaxAmount);
        }
        if ($this->mpHelper->getConfigTaxManage()) {
                        $adminTaxAmount = 0;
        }
        return $adminTaxAmount;
    }

    public function getCheckoutFinalData($finalCart, $quote)
    {
        $paymentDetailsArray = [];
        if (count($finalCart) > 0) {
            foreach ($finalCart as $cart) {
                /**
                 * check if no shipping and product is set
                 */
                if ($cart['products'] == null && $cart['shippingprice'] == 0) {
                    if (!$this->mpHelper->getConfigTaxManage() && $cart["taxamount"] == 0) {
                        continue;
                    } elseif ($this->mpHelper->getConfigTaxManage()) {
                        continue;
                    }
                }
                
                /**
                 * check if commission is not set
                 */
                if (!isset($cart['commission'])) {
                    $cart['commission'] = 0;
                }

                /**
                 * check if tax is miscalculated
                 */
                if (isset($cart['taxamount']) && $cart['taxamount'] < 0) {
                    $cart['taxamount'] = 0;
                }

                /**
                 * convert to cent by multiplying to 100
                 */
                $applicationFee = $cart['commission'] * 100;
                if ($cart['seller'] == 0) {
                    $applicationFee = 0;
                }
                $sellerStripeSecretKey = $cart['stripe_secret_key'];
                $sellerAccessToken = $cart['access_token'];
                $sellerStripeKey = $cart['publish_key'];
                $stripeUserId = $cart['stripe_user_id'];
                $shippingAmount = 0;
                $sellerId = $cart['seller'];
                $invoicePrice = $cart['invoiceprice'];
                $shippingAmount = $cart['shippingprice'];
                $taxAmount = $cart['taxamount'];
                $discount = $cart['discount'];

                /*
                 * if seller is not connected to admin stripe app
                 */
                if ($cart['seller'] && !$stripeUserId) {
                    $applicationFee = 0;
                }
            if($quote->getMcPaymentfeeAmount()){
                $cart['invoiceprice'] = $cart['invoiceprice']+ $quote->getMcPaymentfeeAmount();
                $invoicePrice = $invoicePrice + $quote->getMcPaymentfeeAmount();

            }
                $paymentDetailsArray[$sellerId]['payment_array'] = [
                        'amount' => $this->calculateAmount($invoicePrice + $shippingAmount + $taxAmount -
                                    $discount, strtolower($quote->getStore()->getCurrentCurrencyCode())),
                        'currency' => strtolower($quote->getStore()->getCurrentCurrencyCode()),
                        // 'customer' => $stripeCustomerId,
                        'application_fee' => $applicationFee,
                        'description' => sprintf('#%s', $quote->getId()),
                        'destination' => $stripeUserId,
                        'order_id' => $quote->getId(),
                    ];
                $paymentDetailsArray[$sellerId]['cart'] = $cart;
            }
        }
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/1_test.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info(print_r($paymentDetailsArray,true));
        return $paymentDetailsArray;
    }
    
    /**
     * calculates price
     *
     * @param array $finalcart
     * @param int $counter
     * @param string $newvar
     * @param float $quoteshipPrice
     * @param float $shippingDiscount
     * @param float $orderShippingTaxAmount
     * @return array $finalcart
     */
    private function calculatePrice(
        $finalcart,
        $counter,
        $quoteshipPrice,
        $shippingDiscount,
        $orderShippingTaxAmount
    ) {
    
        if ($this->newvar == '') {
            $finalcart[$counter]['price'] =
            $this->priceCurrency->round(
                $quoteshipPrice + $orderShippingTaxAmount
            );
            $finalcart[$counter]['shippingprice'] = $quoteshipPrice - $shippingDiscount;
            $finalcart[$counter]['invoiceprice'] = 0;
        } elseif ($orderShippingTaxAmount !== 0) {
            $finalcart[$counter]['price'] = $orderShippingTaxAmount;
            $finalcart[$counter]['shippingprice'] = 0;
            $finalcart[$counter]['invoiceprice'] = 0;
        } else {
            $finalcart[$counter]['price'] = 0;
            $finalcart[$counter]['invoiceprice'] = 0;
            $finalcart[$counter]['shippingprice'] = 0;
        }
        return $finalcart;
    }

    public function setUpDefaultDetails()
    {
        $stripeKey = $this->getConfigValue('api_key');
        \Stripe\Stripe::setApiKey($stripeKey);

        \Stripe\Stripe::setAppInfo(
            self::STRIPE_MODULE_NAME,
            self::STRIPE_MODULE_VERSION,
            self::STRIPE_MODULE_URL,
            self::STRIPE_PARTNER_ID
        );
        \Stripe\Stripe::setApiVersion(self::STRIPE_API_VERSOIN);
    }
}
