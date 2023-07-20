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
namespace Webkul\MpStripe\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\RequestInterface;
use Webkul\MpStripe\Logger\StripeLogger;
use Webkul\MpStripe\Model\Source\IntegrationType;

class PaymentMethod extends AbstractMethod
{
    const METHOD_CODE = 'mpstripe';

    /**
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_isInitializeNeeded = false;

    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var \Webkul\MpStripe\Helper\Data
     */

    private $helper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $mpHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * $newvar variable to check if seller shipping used.
     *
     * @var string
     */
    private $newvar;

    /**
     * $counterVal variable (count in final cart)
     */
    private $counterVal;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * $_commission admin commission.
     *
     * @var decimal
     */
    protected $_commission = 0;

    /**
     * $customerStripeId
     *
     * @var boolean
     */
    private $customerStripeId = false;

    /**
     * @var object
     */
    protected $urlBuilder;

    /**
     * $checkoutSession.
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    private $request;

    /**
     * $adminShipTaxAmt
     *
     * @var int
     */
    private $adminShipTaxAmt = 0;

    /**
     * @var \Webkul\MpStripe\Logger\StripeLogger
     */
    protected $logger;

    private $stripeCustomer;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Webkul\MpStripe\Model\StripeCustomerFactory $stripeCustomer
     * @param StripeLogger $stripeLogger
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param RequestInterface $request
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\Marketplace\Model\OrdersFactory $mpordersFactory
     * @param \Magento\Sales\Model\Order\Payment\Transaction $orderTrans
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Shipping\Model\ConfigFactory $configFactory
     * @param \Magento\Framework\Session\SessionManager $coreSession
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Webkul\MpStripe\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Webkul\MpStripe\Model\StripeCustomerFactory $stripeCustomer,
        StripeLogger $stripeLogger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        RequestInterface $request,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\Marketplace\Model\OrdersFactory $mpordersFactory,
        \Magento\Sales\Model\Order\Payment\Transaction $orderTrans,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Shipping\Model\ConfigFactory $configFactory,
        \Magento\Framework\Session\SessionManager $coreSession,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->jsonHelper = $jsonHelper;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
        $this->priceCurrency = $priceCurrency;
        $this->request = $request;
        $this->stripeCustomer = $stripeCustomer;
        $this->logger = $stripeLogger;
        $this->messageManager = $messageManager;
        $this->saleslistFactory = $saleslistFactory;
        $this->productFactory = $productFactory;
        $this->mpordersFactory = $mpordersFactory;
        $this->orderTrans = $orderTrans;
        $this->orderFactory = $orderFactory;
        $this->addressFactory = $addressFactory;
        $this->configFactory = $configFactory;
        $this->coreSession = $coreSession;
        $this->mpProductFactory = $mpProductFactory;
        $this->quoteFactory = $quoteFactory;
        $status = $this->initializeStripe($this->helper->getConfigValue('api_key'));
    }

    /*
     * set api key for payment  >> sandbox api key or live api key
     */
    public function initializeStripe($stripeKey = false)
    {
        if ($stripeKey) {
            $this->helper->setUpDefaultDetails();
        } else {
            return false;
        }
    }

    /**
     * Authorizes specified amount.
     *
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $token = $this->getInfoInstance()->getAdditionalInformation('token');

        $paymentSource = $this->getInfoInstance()->getAdditionalInformation('paymentSource');
        $cardNumber = $this->getInfoInstance()->getAdditionalInformation('cardNumber');
        $this->customerStripeId = $this->getInfoInstance()->getAdditionalInformation('stripeCustomerId');
        $saveCardForCustomer = $this->getInfoInstance()->getAdditionalInformation('saveCardForCustomer');
        $paymentDetailsArray = [];
        $email = $this->getInfoInstance()->getAdditionalInformation('email');
        $address = $this->getInfoInstance()->getAdditionalInformation('address');
        $order = $payment->getOrder();
        $finalCart = $this->helper->getFinalCart($order);
        $this->coreSession->setFinalCart($finalCart);
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

                /*
                 * if new card details
                 */
                if ($paymentSource == 'token' && $saveCardForCustomer) {
                    $stripeCustomerId = $this
                                        ->createStripeCustomer(
                                            $token,
                                            $cardNumber,
                                            $email,
                                            $address,
                                            $order,
                                            true
                                        );
                    $paymentDetailsArray[$sellerId]['payment_array'] = [
                        'amount' => $this->helper
                                    ->calculateAmount(
                                        $invoicePrice + $shippingAmount + $taxAmount - $discount,
                                        strtolower($order->getOrderCurrencyCode())
                                    ),
                        'currency' => strtolower($order->getOrderCurrencyCode()),
                        'customer' => $stripeCustomerId,
                        'application_fee' => $applicationFee,
                        'description' => sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),
                        'destination' => $stripeUserId,
                        'order_id' => $order->getIncrementId(),
                        'receipt_email' => $order->getCustomerEmail()
                    ];
                    $paymentDetailsArray[$sellerId]['cart'] = $cart;
                } elseif ($paymentSource == 'token' && !$saveCardForCustomer) {
                    $stripeCustomerId = $this
                                        ->createStripeCustomer(
                                            $token,
                                            $cardNumber,
                                            $email,
                                            $address,
                                            $order,
                                            false
                                        );
                    /*
                     * if new card details but don't need to save the card
                     */
                    $paymentDetailsArray[$sellerId]['payment_array'] = [
                            'amount' => $this->helper
                                        ->calculateAmount($invoicePrice + $shippingAmount +
                                        $taxAmount - $discount, strtolower($order->getOrderCurrencyCode())),
                            'currency' => strtolower($order->getOrderCurrencyCode()),
                            'customer' => $stripeCustomerId,
                            'application_fee' => $applicationFee,
                            'description' => sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),
                            'destination' => $stripeUserId,
                            'order_id' => $order->getIncrementId(),
                            'receipt_email' => $order->getCustomerEmail()
                        ];
                    $paymentDetailsArray[$sellerId]['cart'] = $cart;
                } else {
                    /*
                     * alredy saved card used
                     */
                    $stripeCustomerId = $this
                                        ->createStripeCustomer(
                                            $token,
                                            $cardNumber,
                                            $email,
                                            $address,
                                            $order,
                                            false
                                        );
                    $paymentDetailsArray[$sellerId]['payment_array'] = [
                            'amount' => $this->helper
                                        ->calculateAmount($invoicePrice + $shippingAmount + $taxAmount -
                                        $discount, strtolower($order->getOrderCurrencyCode())),
                            'currency' => strtolower($order->getOrderCurrencyCode()),
                            'customer' => $stripeCustomerId,
                            'application_fee' => $applicationFee,
                            'description' => sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),
                            'destination' => $stripeUserId,
                            'order_id' => $order->getIncrementId(),
                            'receipt_email' => $order->getCustomerEmail()
                        ];
                    $paymentDetailsArray[$sellerId]['cart'] = $cart;
                }
            }
            $this->checkoutSession->setStripePaymentDetails($paymentDetailsArray);
        }
    }

    /**
     * refund refund online transactions.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float                                $amount
     *
     * @return this current class object
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $order = $payment->getOrder();
            $orderData = $payment->getOrder()->getData();
            $orderId = $orderData['entity_id'];
            $refundData = $this->request->getParams();
            $transactionId = $payment->getParentTransactionId();
            $buyerId = $order->getCustomerId();
            $buyerEmail = $order->getCustomerEmail();
            $isAdminProduct = true;
            $calculatedTax = 0;
            $appliedCouponAmount = 0;

            // refund calculation check
            $adjustmentPositive = $refundData['creditmemo']['adjustment_positive'];
            $adjustmentNegative = $refundData['creditmemo']['adjustment_negative'];
            
            if ($adjustmentNegative >= $adjustmentPositive) {
                $adjustmentNegative = $adjustmentNegative - $adjustmentPositive;
            } else {
                $adjustmentNegative = $adjustmentNegative - $adjustmentPositive;
                $this->checkSellerAmountAvailable($amount, $orderId, $adjustmentNegative);
            }
            
            $creditmemoItemsIds = [];
            $creditmemoItemsQty = [];
            $creditmemoItemsPrice = [];
            if (!isset($refundData['creditmemo']['items'])) {
                $refundData['creditmemo']['items'] = [];
            }
            foreach ($refundData['creditmemo']['items'] as $key => $value) {
                $productId = '';
                $sellerProducts = $this->saleslistFactory->create()->getCollection()
                ->addFieldToFilter('order_item_id', $key)
                ->addFieldToFilter('order_id', $orderId);
                foreach ($sellerProducts as $sellerProduct) {
                    $productId = $sellerProduct['mageproduct_id'];
                    /**
                     * check if admin product or not
                     */
                    if ($sellerProduct->getSellerId()) {
                        $isAdminProduct = false;
                    }
                    $appliedCouponAmount = $sellerProduct["applied_coupon_amount"] + $appliedCouponAmount;
                }
                if ($productId) {
                    $itemFactory = $this->productFactory->create();
                    $item = $this->loadProductId($itemFactory, $productId);
                    $creditmemoItemsIds[$key] = $productId;
                    $creditmemoItemsQty[$key] = $value['qty'];
                    $creditmemoItemsPrice[$key] = $this->getItemPrice(
                        $orderId,
                        $item->getId()
                    ) * $value['qty'];
                }
            }

            arsort($creditmemoItemsPrice);

            $adminAmountToRefund = 0;

            $sellerArr = [];

            if (!empty($this->customerSession->getMpCreditmemoCommissionRate())) {
                $creditmemoCommissionRateArr = $this->customerSession->getMpCreditmemoCommissionRate();
                $this->customerSession->setMpCreditmemoCommissionRate([]);
            } else {
                $creditmemoCommissionRateArr = [];
            }

            foreach ($creditmemoItemsPrice as $key => $item) {
                $sellerProducts = $this->saleslistFactory->create()->getCollection()
                                        ->addFieldToFilter('order_item_id', $key)
                                        ->addFieldToFilter('order_id', $orderId);
                foreach ($sellerProducts as $sellerProduct) {
                    if ($sellerProduct["order_item_id"]!=$key) {
                        continue;
                    }

                    $refundedQty = $creditmemoItemsQty[$key];
                    $refundedPrice = $creditmemoItemsPrice[$key];
                    $productId = $creditmemoItemsIds[$key];

                    if ($adjustmentNegative * 1) {
                        if ($adjustmentNegative >= $sellerProduct['total_amount']) {
                            $adjustmentNegative = $adjustmentNegative - $sellerProduct['total_amount'];
                            $updatedPrice = $sellerProduct['total_amount'];
                            $refundedPrice = 0;
                        } else {
                            $refundedPrice = $refundedPrice - $adjustmentNegative;
                            $updatedPrice = $sellerProduct['total_amount'] - $refundedPrice;
                            $adjustmentNegative = 0;
                        }
                    } else {
                        $updatedPrice = $sellerProduct['total_amount'] - $refundedPrice;
                    }
                    if (!($sellerProduct['total_amount'] * 1)) {
                        $sellerProduct['total_amount'] = 1;
                    }
                    if ($sellerProduct['total_commission'] * 1) {
                        $commissionPercentage = (
                        $sellerProduct['total_commission'] * 100
                            ) / $sellerProduct['total_amount'];
                    } else {
                        $commissionPercentage = 0;
                    }
                    $updatedCommission = ($refundedPrice * $commissionPercentage) / 100;
                    $updatedSellerAmount = $refundedPrice - $updatedCommission;

                    $taxAmount = $this->calculateRefundTaxAmount($refundedQty, $sellerProduct);

                    if (!$this->mpHelper->getConfigTaxManage()
                    ) {
                        $taxAmount = 0;
                    }
                    $updatedSellerAmount = $updatedSellerAmount + $taxAmount;
                    if (!isset($sellerArr[$sellerProduct['seller_id']]['seller_refund'])) {
                        $sellerArr[$sellerProduct['seller_id']]['seller_refund'] = 0;
                    }

                    if (!isset($sellerArr[$sellerProduct['seller_id']]['updated_commission'])) {
                        $sellerArr[$sellerProduct['seller_id']]['updated_commission'] = 0;
                    }

                    $sellerArr[$sellerProduct['seller_id']]['seller_refund'] =
                    $sellerArr[$sellerProduct['seller_id']]['seller_refund'] + $updatedSellerAmount;
                    $sellerArr[$sellerProduct['seller_id']]['updated_commission'] =
                    $sellerArr[$sellerProduct['seller_id']]['updated_commission'] + $updatedCommission;
                    $adminAmountToRefund = $adminAmountToRefund + $updatedCommission;
                }
            }

            $adminShipping = 0;
            $i = 0;
            $shippingCharges = 0;
            $totalSellerRefund = 0;
            foreach ($sellerArr as $sellerId => $value) {
                $shippingCharges = 0;
                $codCharges = 0;
                $trackingcoll = $this->mpordersFactory->create()->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('seller_id', $sellerId);
                foreach ($trackingcoll as $tracking) {
                    $tranferId = $tracking->getStripePaymentIntentTransferId();
                    $codCharges = $tracking->getCodCharges();
                    $shippingCharges = $tracking->getShippingCharges();
                    $refundCreditMemoShippingAmount = 0;
                    if (isset($refundData['creditmemo']['shipping_amount'])) {
                        $refundCreditMemoShippingAmount = $refundData['creditmemo']['shipping_amount'];
                    }
                    if ($shippingCharges * 1) {
                        if ($shippingCharges > $refundCreditMemoShippingAmount) {
                            $shippingCharges = $refundCreditMemoShippingAmount;
                            $refundData['creditmemo']['shipping_amount'] = 0;
                        } else {
                            $refundData['creditmemo']['shipping_amount'] =
                            $refundCreditMemoShippingAmount - $shippingCharges;
                        }
                    }
                }

                if (($value['seller_refund'] + $shippingCharges) * 1 > 0) {
                        $totalSellerRefund = $value['seller_refund'] + $shippingCharges;
                        $totalSellerRefund = round($totalSellerRefund, 2);
                }
            }

            /*
            * Calculate Admin Shipping and admin shipping tax amount for admin panel
            */

            if ($shippingCharges == 0) {
                if (!isset($orderData['shipping_refunded']) || !$orderData['shipping_refunded']) {
                    $orderData['shipping_refunded'] = 0;
                }

                $adminShipping = $this->calculateRefundAdminShipping($orderData, $adminShipping, $refundData);
            }

            $adminAmountToRefund = $adminAmountToRefund + $adminShipping;
            $amount=$totalSellerRefund+$adminAmountToRefund;
            $amount = $amount - $appliedCouponAmount;
            //amount in cents
            $amount = $amount*100;
            $transactionId = $payment->getRefundTransactionId();
            $transaction = $this->orderTrans->load($payment->getRefundTransactionId());
            $onlineTxnId = $transaction->getTxnId();
            $charge = [];
            
            try {
                $refundCharge = [];

                if ($isAdminProduct) {
                    $refundCharge = [
                        'charge' => $onlineTxnId,
                        'amount' => $amount,
                        'refund_application_fee' => true,
                    ];
                } else {
                    $refundCharge = [
                        'charge' => $onlineTxnId,
                        'amount' => $amount ,
                        'reverse_transfer' => true,
                        'refund_application_fee' => true,
                    ];
                }

                if (isset($refundCharge['amount']) && $refundCharge['amount']>0) {
                    $chargeData = \Stripe\Charge::retrieve($onlineTxnId);
                    if (!$chargeData['application_fee'] || !$chargeData['destination']) {
                        unset($refundCharge['reverse_transfer']);
                        unset($refundCharge['refund_application_fee']);
                    }
                    $charge = \Stripe\Refund::create([
                        $refundCharge
                    ]);
                    
                    if (isset($tranferId)) {
                        \Stripe\Transfer::createReversal(
                            $tranferId,
                            ['amount' => $amount]
                        );
                    }
                    
                    $charge = (array) $charge;
                    foreach ($charge as $key => $value) {
                        if (strpos($key, 'values') !== false) {
                            $charge = $value;
                            $charge['amount'] = $charge['amount'] / 100;
                            // $charge['amount_refunded'] = $charge['amount_refunded'] / 100;
                            $charge['metadata'] = $this->jsonHelper->jsonEncode((array) $charge['metadata']);
                        }
                    }
                    // $charge['outcome'] = $this->jsonHelper->jsonEncode((array) $charge['outcome']);
                    // $charge['refunds'] = $this->jsonHelper->jsonEncode((array) $charge['refunds']);
                    // $charge['source'] = $this->jsonHelper->jsonEncode((array) $charge['source']);
                    // $charge['fraud_details'] = $this->jsonHelper->jsonEncode((array) $charge['fraud_details']);

                    $encodeCharge = [];
                    foreach ($charge as $key => $value) {
                        $encodeCharge[$key] = $this->jsonHelper->jsonEncode($charge[$key]);
                    }

                    $payment
                    ->setTransactionId($charge['id'].'-'.\Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND)
                    ->setParentTransactionId($transactionId)
                    ->setIsTransactionClosed(1)
                    ->setShouldCloseParentTransaction(1)
                    ->setTransactionAdditionalInfo(
                        \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                        $encodeCharge
                    );
                } else {
                    throw new LocalizedException(
                        __(
                            'There is nothing to refund.'
                        )
                    );
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                throw new LocalizedException(
                    __($e->getMessage())
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            throw new LocalizedException(
                __(
                    'payment refund error, please contact admin'
                )
            );
        }
        return $this;
    }

    /**
     * Calculates admin shipping for refund
     *
     * @param array $orderData
     * @param int $adminShipping
     * @param array $refundData
     * @return int $adminShipping
     */
    private function calculateRefundAdminShipping($orderData, $adminShipping, $refundData)
    {
        if (($orderData['shipping_amount'] * 1) >= ($orderData['shipping_refunded'] * 1)
            ) {
            $adminShipping = 0;
            if (isset($refundData['creditmemo']['shipping_amount'])) {
                $adminShipping = $refundData['creditmemo']['shipping_amount'];
            }
            $orderShippingTaxAmount = $orderData['shipping_tax_amount'];
            $orderShippingTaxRefunded = $orderData['shipping_tax_refunded'];

            if (($orderShippingTaxAmount * 1) >= ($orderShippingTaxRefunded * 1)) {
                if ($orderShippingTaxAmount > 0) {
                    $shipTaxPercentage = ($orderShippingTaxAmount * 100) /
                    $orderData['shipping_amount'];
                    $shippingAmount = 0;
                    if (isset($refundData['creditmemo']['shipping_amount'])) {
                        $shippingAmount = $refundData['creditmemo']['shipping_amount'];
                    }
                    $adminShipping = $adminShipping + (
                        $shippingAmount * $shipTaxPercentage / 100
                    );
                }
            }
        }
        return $adminShipping;
    }

    /**
     * Undocumented function
     *
     * @param int $refundedQty
     * @param array $sellerProduct
     * @return int $taxAmount
     */
    public function calculateRefundTaxAmount($refundedQty, $sellerProduct)
    {
        if ($refundedQty) {
            if ((int)$sellerProduct['magequantity'] != '' ||
                (int)$sellerProduct['magequantity'] != 0
                ) {
                    $taxAmount = (
                    $sellerProduct['total_tax'] / $sellerProduct['magequantity']
                                ) * $refundedQty;
            } else {
                $taxAmount = 0;
            }
        } else {
            $taxAmount = 0;
        }
        return $taxAmount;
    }

    /**
     * Undocumented function
     *
     * @param object $item
     * @param int $productId
     * @return $item
     */
    public function loadProductId($item, $productId)
    {
        $this->logger->critical("productId - ".$productId);
        return $item->load($productId);
    }

    /**
     * check if seller amount available as mentioned in refund
     *
     * @param int $amount
     * @param int $orderId
     * @param array $refundData
     * throws LocalizedException exception
     */
    private function checkSellerAmountAvailable($amount, $orderId, $adjustmentNegative)
    {
        $calculateCommission = $this->saleslistFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('order_id', $orderId);
                       
        foreach ($calculateCommission as $commissionRate) {
            $actualSellerAmount = $commissionRate["actual_seller_amount"];
        }

        $refundAmount = $actualSellerAmount-$adjustmentNegative;

        if ($actualSellerAmount < $amount) {
            throw new LocalizedException(
                __(
                    'Refund amount by seller($'.$refundAmount
                    .') is more than seller commission($'.$actualSellerAmount.')'
                )
            );
        }
    }

    /*
     * Calculation of the refund item ordered price
     */
    protected function getItemPrice($orderId, $productId)
    {
        $order = $this->orderFactory->create()->load($orderId);
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductId() == $productId) {
                return $item->getPrice();
            }
        }
        return 0;
    }

    /**
     * createStripeCustomer create customer on stripe.
     *
     * @param string $token stripe card token
     *
     * @return string stripe customer id
     */
    public function createStripeCustomer($token, $cardNumber, $email, $address, $order, $saveCard = false)
    {
        $shippingAddress = $this->getShippingAddress($address, $order);
        $billingAddress = $this->getBillingAddress($address, $order);
        if (!$this->customerStripeId || $this->customerStripeId == 'on') {
            try {
                /**
                 * $customer stripe customer object stores stripe customer info.
                 */
                $customer = \Stripe\Customer::create(

                    [
                      'source' => $token,
                      'description' => $email,
                      'email' => $email,
                      'shipping' => $shippingAddress
                    ]
                );
                $this->logger->critical('customer'.$this->jsonHelper->jsonEncode($customer));
                if ($customer->id) {
                    if ($saveCard) {
                        $cardObject = $this->getCardData($token);
                        $fingerPrint = $cardObject->card->fingerprint;
                        $expMonth = $cardObject->card->exp_month;
                        $expYear = $cardObject->card->exp_year;
                        $cardData['fingerprint'] = $fingerPrint;
                        $cardData['expiry_month'] = $expMonth;
                        $cardData['expiry_year'] = $expYear;
                        $isDuplicateCard = $this->checkDuplicacyOfFingurePrint(
                            $fingerPrint,
                            $this->mpHelper->getCustomerId()
                        );
                        /*
                         * save stripe card info in data base
                         */
                        $this->helper->saveStripeCustomer($customer->id, $cardNumber, $cardData, $isDuplicateCard);
                    }
                    $this->customerStripeId = $customer->id;

                    return $customer->id;
                }

                return false;
            } catch (\Stripe\Error $e) {
                $this->logger->critical($e);
                throw new LocalizedException(

                    __(
                        'There was an error capturing the transaction: %1',
                        $e->getMessage()
                    )
                );
            } catch (\Exception $e) {
                $this->logger->critical($e);
                throw new LocalizedException(

                    __(
                        'There was an error capturing the transaction: %1',
                        $e->getMessage()
                    )
                );
            }
        } else {
            return $this->customerStripeId;
        }
    }

    /**
     * getCardData get the unique fingureprint from the card object
     *
     * @param [String] $token
     * @return String
     */
    public function getCardData($token = null)
    {
        try {
            $tokenData = \Stripe\Token::retrieve($token);
            return $tokenData;
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    'There was an error capturing the transaction: %1',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * getShippingAddress get the shipping detail
     *
     * @param [Array] $stripeAddress
     * @param [\Magento\Sales\Model\Order] $order
     * @return Array
     */
    public function getShippingAddress($stripeAddress, $order)
    {
        $shippingAddress = [];
        if ($order->getIsVirtual() == 0) {
            if (empty($stripeAddress)) {
                $street = $order->getShippingAddress()->getStreet();
                if (count($street) == 2) {
                    $address['line1']  = $street[0];
                    $address['line2']  = $street[1];
                } elseif (count($street) == 1) {
                    $address['line1']  = $street[0];
                    $address['line2']  = "";
                } elseif (count($street) == 3) {
                    $address['line1']  = $street[0].' '.$street[1];
                    $address['line2']  = $street[2];
                }
                $address['city'] = $order->getShippingAddress()->getCity();
                $address['country'] = $order->getShippingAddress()->getCountryId();
                $address['postal_code'] = $order->getShippingAddress()->getPostcode();
                $address['state'] = $order->getShippingAddress()->getRegion();
                $shippingAddress['address']= $address;
                $shippingAddress['name']= $order->getShippingAddress()
                                          ->getFirstname()." ".$order->getShippingAddress()->getLastName();
                $shippingAddress['phone'] = $order->getShippingAddress()->getTelephone();
            } else {
                    $address['line1']  = $stripeAddress['shipping_address_line1'];
                    $address['line2']  = "";
                    $address['city'] = $stripeAddress['shipping_address_city'];
                    $address['country'] = $stripeAddress['shipping_address_country'];
                    $address['postal_code'] = $stripeAddress['shipping_address_zip'];
                    $address['state'] = "";
                    $shippingAddress['address']= $address;
                    $shippingAddress['name'] = $stripeAddress['billing_name'];
                    $shippingAddress['phone']= "";
            }
        }
        return $shippingAddress;
    }

    /**
     * getBillingAddress get the billing detail
     *
     * @param [Array] $stripeAddress
     * @param [\Magento\Sales\Model\Order] $order
     * @return Array
     */
    public function getBillingAddress($stripeAddress, $order)
    {
        $billingAddress = [];
        if (empty($stripeAddress)) {
            $street = $order->getBillingAddress()->getStreet();
            if (count($street) == 2) {
                $address['line1']  = $street[0];
                $address['line2']  = $street[1];
            } elseif (count($street) == 1) {
                $address['line1']  = $street[0];
                $address['line2']  = "";
            }
            $address['city'] = $order->getBillingAddress()->getCity();
            $address['country'] = $order->getBillingAddress()->getCountryId();
            $address['postal_code'] = $order->getBillingAddress()->getPostcode();
            $address['state'] = $order->getBillingAddress()->getRegion();
            $billingAddress['address']= $address;
            $billingAddress['name']= $order->getBillingAddress()
                                     ->getFirstname()." ".$order->getBillingAddress()->getLastName();
            $billingAddress['phone'] = $order->getBillingAddress()->getTelephone();
        } else {
                $address['line1']  = $stripeAddress['billing_address_line1'];
                $address['line2']  = "";
                $address['city'] = $stripeAddress['billing_address_city'];
                $address['country'] = $stripeAddress['billing_address_country'];
                $address['postal_code'] = $stripeAddress['billing_address_zip'];
                $address['state'] = "";
                $billingAddress['address']= $address;
                $billingAddress['name'] = $stripeAddress['billing_name'];
                $billingAddress['phone']= "";
        }
        return $billingAddress;
    }

    /**
     * checkDuplicacyOfFingurePrint check the uniqueness of fingureprint for particular customer in database
     *
     * @param [String] $fingerPrint
     * @param [String] $customerId
     * @return Boolean
     */
    public function checkDuplicacyOfFingurePrint($fingerPrint = null, $customerId = null)
    {
        try {
            $model = $this->stripeCustomer->create();
            $collection = $model->getCollection()
                            ->addFieldToFilter('fingerprint', ['eq' => $fingerPrint])
                            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
            if ($collection->getSize()) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    'There was an error capturing the transaction: %1',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Do not validate payment form using server methods.
     *
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * Assign corresponding data.
     *
     * @param \Magento\Framework\DataObject|mixed $data
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        return $this;
    }

    /**
     * Define if debugging is enabled.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     *
     * @api
     */
    public function getDebugFlag()
    {
        if ($this->getConfigData('debug') == 'sandbox') {
            return true;
        } else {
            return false;
        }
    }
}
