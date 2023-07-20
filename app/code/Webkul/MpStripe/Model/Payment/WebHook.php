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
namespace Webkul\MpStripe\Model\Payment;

use Webkul\MpStripe\Api\WebhookInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersModel;

class WebHook implements WebhookInterface
{
    /**
     * @var \Webkul\MpStripe\Helper\Data
     */
    protected $helper;

    protected $sellerInvoiceData = [];
    
    /**
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param JsonHelper $jsonHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Framework\Filesystem\Driver\File $driver
     * @param \Webkul\Marketplace\Helper\Orders $ordersHelper
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param MpOrdersModel $mpOrdersModel
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerModel
     * @param \Webkul\MpStripe\Logger\StripeLogger $logger
     */
    public function __construct(
        \Webkul\MpStripe\Helper\Data $helper,
        JsonHelper $jsonHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\Filesystem\Driver\File $driver,
        \Webkul\Marketplace\Helper\Orders $ordersHelper,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\DB\Transaction $transaction,
        MpOrdersModel $mpOrdersModel,
        PriceCurrencyInterface $priceCurrency,
        \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerModel,
        \Webkul\MpStripe\Logger\StripeLogger $logger
    ) {
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->quoteRepository = $quoteRepository;
        $this->invoiceSender = $invoiceSender;
        $this->transactionBuilder = $transactionBuilder;
        $this->ordersHelper = $ordersHelper;
        $this->driver = $driver;
        $this->mpOrdersModel = $mpOrdersModel;
        $this->invoiceRepository  = $invoiceRepository;
        $this->priceCurrency = $priceCurrency;
        $this->jsonHelper = $jsonHelper;
        $this->transaction = $transaction;
        $this->stripeSellerModel = $stripeSellerModel;
        $this->logger = $logger;
    }

    /**
     * handle payment success
     */
    public function executeWebhook()
    {
        $data = $this->driver->fileGetContents('php://input');
        $this->logger->critical('Stripe message '.$data);

        $stripeResponse = $this->jsonHelper->jsonDecode($data);
        $webhookType = $stripeResponse['type'];
        try {
            switch ($webhookType) {
                case "charge.succeeded":
                    $orderId = $stripeResponse['data']['object']['transfer_group'];
                    $order = $this->orderFactory->create()->load($orderId);
                    if ($order->canInvoice()) {
                        $finalCart = $this->helper->getFinalCart($order);
                        $paymentDetails = $this->helper->getCheckoutFinalData($finalCart, $order);
                        $paymentIntent = $stripeResponse['data']['object']['payment_intent'];
                        // if ($order->getStripePaymentIntent() == null) {
                        $this->addPaymentIntentToOrder($orderId, $paymentIntent);
    
                        $this->helper->setUpDefaultDetails();
                        $transfers = \Stripe\Transfer::all(['transfer_group' => $orderId]);
                        $this->manageChargeSuccess($paymentDetails, $order, $orderId, $transfers, $stripeResponse);
                        
                    } else {
                        http_response_code(200);
                    }
                    break;
                case "payment_intent.payment_failed":
                    $paymentIntent = $stripeResponse['data']['object']['id'];
                    $order = $this->orderFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('stripe_payment_intent', $paymentIntent)->getFirstItem();
                    $orderState = Order::STATE_PENDING_PAYMENT;
                    $order->setState($orderState)->setStatus(Order::STATE_PENDING_PAYMENT);
                    $order->save();
                    break;
            }
        } catch (\Exception $e) {
            $this->logger->critical('webhook'.$e->getMessage());
        }
        http_response_code(200);
    }

    public function manageChargeSuccess($paymentDetails, $order, $orderId, $transfers, $stripeResponse)
    {
        foreach ($paymentDetails as $paymentDetail) {
                            
            $cart = $paymentDetail['cart'];

            /*
            * check if seller charge or admin charge
            */
            if ($cart['seller'] || ($cart['products'] !== null)) {

                $invoice = $this->createInvoice($order, $cart, $stripeResponse['data']['object']);
                $this->manageSellerFee($invoice, $order, $cart, $orderId, $transfers);
                
            } else {
                $invoiceId = $this->createShippingInvoice($order, $cart, $stripeResponse['data']['object']);
            }
            if (count($this->sellerInvoiceData) > 0) {
                $payment = $order->getPayment();
                $payment->setAdditionalInformation(
                    'stripeitem__invoice__data',
                    $this->jsonHelper->jsonEncode($this->sellerInvoiceData)
                );
                $payment->save();
            }
        }
    }

    /**
     * add payment intent id to order
     */
    public function addPaymentIntentToOrder($orderId, $paymentIntent)
    {
        $order = $this->orderFactory->create()->load($orderId);
        $order->setStripePaymentIntent($paymentIntent);
        $order->save();
    }

    /**
     * createInvoice function to create invoice seller wise.
     *
     * @param object $order
     * @param array  $cart   seller wise payment data
     * @param array  $charge stripe charge data of pauyment
     */
    public function createInvoice($order, $cart = [], $stripeResponse = [])
    {
        try {
            $orderId = $order->getId();
            $sellerId = $cart['seller'];

            /**
             * $transactionId create transaction for the current stripeResponse.
             *
             * @var int
             */
            $transactionId = $this->createTransaction($order, $stripeResponse);
            /**
             * $itemsarray get item data for invoice.
             *
             * @var array
             */
            $itemsarray = $this->helper->_getItemQtys($order, explode(',', $cart['products']));

            if (!empty($itemsarray)) {
                $invoice =
                $this->invoiceService
                ->prepareInvoice(
                    $order,
                    $itemsarray['data']
                );
                $invoice->setTransactionId($transactionId);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->setShippingAmount($cart['shippingprice']);
                $invoice->setBaseShippingInclTax($cart['shippingprice']);
                $invoice->setBaseShippingAmount($cart['shippingprice']);
                $invoice->setDiscountAmount($cart['discount']);
                $invoice->setTaxAmount($cart['taxamount']);
                $invoice->setSubtotal($cart['invoiceprice']);
                $invoice->setBaseSubtotal($cart['invoiceprice']);
                $invoice->setGrandTotal(
                    $this->priceCurrency->round(
                        $cart['invoiceprice'] + $cart['shippingprice'] + $cart['taxamount'] - $cart['discount']
                    )
                );
                $invoice->setBaseGrandTotal(
                    $this->priceCurrency->round(
                        $cart['invoiceprice'] + $cart['shippingprice'] + $cart['taxamount'] - $cart['discount']
                    )
                );
                $invoice->register();
                $invoice->save();
                $invoice->getOrder()->setIsInProcess(true);
                
                $transactionSave =
                $this->transaction
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();
                $this->invoiceSender->send($invoice);
                //send notification code
                $order->addStatusHistoryComment(
                    __(
                        'Notified customer about invoice #%1.',
                        $invoice->getId()
                    )
                )
                    ->setIsCustomerNotified(true)
                    ->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                    ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
                    ->save();
                return $invoice;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());

            return false;
        }
    }

    /**
     * Create remaining order shipping amount invoice.
     *
     * @param $order
     * @param $transactionId
     * @param $shippingAmount
     */
    protected function createShippingInvoice($order, $cart = [], $stripeResponse = [])
    {
        $shippingDiscount = $order->getBaseShippingDiscountAmount();
        $shippingAmount = $cart['shippingprice'];
        $taxAmount = $cart['taxamount'];
        $subTotal = $shippingAmount+$taxAmount+$shippingDiscount;
        if ($shippingAmount || $taxAmount) {
            try {
                /**
                 * $transactionId create transaction for the current stripeResponse.
                 *
                 * @var int
                 */
                $transactionId = $this->createTransaction($order, $stripeResponse);
                
                $invoice =
                $this->invoiceService
                ->prepareInvoice($order, []);
                $invoice->setTransactionId($transactionId);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->setShippingAmount($shippingAmount);
                $invoice->setBaseShippingInclTax($shippingAmount);
                $invoice->setBaseShippingAmount($shippingAmount);
                $invoice->setSubtotal($subTotal);
                $invoice->setBaseSubtotal($subTotal);
                $invoice->setDiscountAmount($shippingDiscount);
                $invoice->setGrandTotal($shippingAmount + $taxAmount);
                $invoice->setBaseGrandTotal($shippingAmount + $taxAmount);
                $invoice->register();
                $invoice->save();
                $invoice->getOrder()->setIsInProcess(true);
                
                $transactionSave =
                $this->transaction
                ->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();

                $invoiceId = $invoice->getId();

                $this->invoiceSender->send($invoice);
                $order->addStatusHistoryComment(
                    __('Notified customer about invoice #%1.', $invoice->getId())
                )
                    ->setIsCustomerNotified(true)
                    ->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                    ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
                    ->save();

                return $invoiceId;
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());

                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * createTransaction function to create payment transations.
     *
     * @param object $order
     * @param array  $charge
     *
     * @return int
     */
    public function createTransaction($order = null, $stripeResponse = [])
    {
        $orderId = $order->getId();
        $this->helper->setUpDefaultDetails();
        $response = \Stripe\PaymentIntent::retrieve($stripeResponse['payment_intent']);
        try {
            $stripeFinalResponse['id'] = $response['id'];
            if ($response['customer'] != null) {
                $stripeFinalResponse['customer_id'] = $response['customer'];
            }
            $stripeFinalResponse['currency'] = $response['currency'];
            $stripeFinalResponse['amount'] = $response['amount'];
            $stripeFinalResponse['amount_received'] = $response['amount_received'];
            $stripeFinalResponse['capture_method'] = $response['capture_method'];
            $stripeFinalResponse['object'] = $response['object'];
            $stripeFinalResponse['status'] = $response['status'];
            $stripeFinalResponse['charge_id'] = $response['charges']['data'][0]['id'];
            $order = $this->orderFactory->create()->load($orderId);
            //get payment object from order object
            $payment = $order->getPayment();
            if ($payment != null) {
                $payment->setLastTransId($stripeResponse['id']);
                $payment->setTransactionId($stripeResponse['id']);
                $payment->setAdditionalInformation(
                    [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $stripeFinalResponse]
                );
                $formatedPrice = $order->getBaseCurrency()->formatTxt(
                    $order->getGrandTotal()
                );
        
                $message = __('The authorized amount is %1.', $formatedPrice);
                //get the object of builder class
                $trans = $this->transactionBuilder;
                $transaction = $trans->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($stripeResponse['id'])
                ->setAdditionalInformation(
                    [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $stripeFinalResponse]
                )
                ->setFailSafe(true)
                //build method creates the transaction and returns the object
                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
        
                $payment->addTransactionCommentsToOrder(
                    $transaction,
                    $message
                );
                $payment->setParentTransactionId(null);
                $payment->save();
                $order->save();
        
                return $transaction->save()->getTransactionId();
            } else {
                $this->helper->setUpDefaultDetails();
                $response = \Stripe\PaymentIntent::retrieve($stripeResponse['payment_intent']);
                $this->createTransaction($orderId, $response);
            }
        } catch (\Exception $e) {
            $this->helper->setUpDefaultDetails();
            $response = \Stripe\PaymentIntent::retrieve($stripeResponse['payment_intent']);
            $this->createTransaction($orderId, $response);
            $this->logger->info('Create Transction'.$e->getMessage());
            $this->logger->info('Create Transction response'.$response);
        }
    }

    public function manageSellerFee($invoice, $order, $cart, $orderId, $transfers)
    {
        $managecomm = 0;
        if ($invoice->getId()) {
            $this->sellerInvoiceData[$cart['seller']]['order_id'] = $order->getId();
            $this->sellerInvoiceData[$cart['seller']]['invoice_id'] = $invoice->getId();
            /*
            * Pay seller fee after successfull invoice
            */
            if ($managecomm==0) {
                $this->ordersHelper->getCommssionCalculation($order);
                $managecomm++;
            }
            if ($cart['seller']) {
                $this->ordersHelper
                ->paysellerpayment($order, $cart['seller'], $invoice->getTransactionId());
                $trackingcol1 = $this->mpOrdersModel->create()
                ->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    $orderId
                )
                ->addFieldToFilter(
                    'seller_id',
                    $cart['seller']
                );
                $stripeSeller = $this->stripeSellerModel->create()
                    ->getCollection()
                    ->addFieldToFilter('seller_id', ['eq' => $cart['seller']])
                    ->getFirstItem();
                
                foreach ($trackingcol1 as $row) {
                    foreach ($transfers['data'] as $sellerTransfer) {
                        if ($sellerTransfer['destination'] == $stripeSeller->getStripeUserId()) {
                            $row->setStripePaymentIntentTransferId($sellerTransfer['id']);
                            $row->save();
                        }
                    }
                    $row->setOrderStatus('processing');
                    $row->save();
                }
            }
        }
    }
}
