<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mppaypalexpresscheckout\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order\Payment;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManager;
use Webkul\Mppaypalexpresscheckout\Model\ResourceModel\Mppaypalexpresscheckout\CollectionFactory;

class PaymentMethod extends AbstractMethod
{
    const CODE = 'mppaypalexpresscheckout';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var Transaction\BuilderInterface
     */
    protected $_transactionBuilder;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Helper\Data
     */
    private $helper;

    /**
     * @var CollectionFactory
     */
    private $expressCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository
     */
    private $transactionRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Webkul\Marketplace\Helper\Payment
     */
    private $mpPaymentHelper;

    /**
     * Order Id
     *
     * @var string
     */
    private $orderId = '';

    /**
     * Transaction Id
     *
     * @var string
     */
    private $transactionId = '';

    /**
     * Order Currency Code
     *
     * @var string
     */
    private $orderCurrencyCode = '';

    /**
     * Base Currency Code
     *
     * @var string
     */
    private $baseCurrencyCode = '';

    /**
     * @param \Magento\Framework\Model\Context                                    $context
     * @param \Magento\Framework\Registry                                         $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory                   $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory                        $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                                        $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                  $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger                                $logger
     * @param \Magento\Framework\UrlInterface                                     $urlBuilder
     * @param Transaction\BuilderInterface                                        $transactionBuilder
     * @param RequestInterface                                                    $request
     * @param \Webkul\Mppaypalexpresscheckout\Helper\Data                         $helper
     * @param CollectionFactory                                                   $expressCollectionFactory
     * @param \Magento\Sales\Model\Order\Payment\Repository                       $transactionRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface                         $orderRepository
     * @param \Webkul\Marketplace\Helper\Payment                                  $mpPaymentHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource             $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb                       $resourceCollection
     * @param array                                                               $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\UrlInterface $urlBuilder,
        Payment\Transaction\BuilderInterface $transactionBuilder,
        RequestInterface $request,
        \Webkul\Mppaypalexpresscheckout\Helper\Data $helper,
        CollectionFactory $expressCollectionFactory,
        \Magento\Sales\Model\Order\Payment\Transaction\Repository $transactionRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Webkul\Marketplace\Helper\Payment $mpPaymentHelper,
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
        $this->_urlBuilder = $urlBuilder;
        $this->_transactionBuilder = $transactionBuilder;
        $this->_request = $request;
        $this->helper = $helper;
        $this->expressCollectionFactory = $expressCollectionFactory;
        $this->transactionRepository = $transactionRepository;
        $this->orderRepository = $orderRepository;
        $this->mpPaymentHelper = $mpPaymentHelper;
    }

    /**
     * Refunds specified amount.
     *
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function refund(
        \Magento\Payment\Model\InfoInterface $payment,
        $amount
    ) {
        try {
            $order = $payment->getOrder();

            $this->orderCurrencyCode = $order->getOrderCurrencyCode();
            $this->baseCurrencyCode = $order->getBaseCurrencyCode();

            $orderData = $payment->getOrder()->getData();

            $this->orderId = $orderData['entity_id'];
            $refundData = $this->_request->getParams();

            $this->transactionId = $payment->getParentTransactionId();
            $transactionData = $this->transactionRepository->getByTransactionId(
                $this->transactionId,
                $payment->getEntityId(),
                $order->getId()
            );
            $additionalInfo = $transactionData->getAdditionalInformation();

            if (array_key_exists("raw_details_info", $additionalInfo)) {
                $additionalInfo = $additionalInfo["raw_details_info"];
            }

            if ($transactionData && $additionalInfo) {
                $transArr = $this->helper->getSellerTransactionDetails(
                    $additionalInfo
                );
                if (!empty($transArr)) {
                    // refund calculation check
                    $adjustmentNegative = $this->mpPaymentHelper->getAdjustmentNegative(
                        $refundData['creditmemo']['adjustment_negative'],
                        $refundData['creditmemo']['adjustment_positive']
                    );

                    if (!isset($refundData['creditmemo']['items'])) {
                        $refundData['creditmemo']['items'] = [];
                    }
                    
                    $creditmemoItemsData = $this->mpPaymentHelper->getCreditmemoItemData(
                        $refundData,
                        $this->orderId
                    );
                    $sellerAndAdminData = $this->mpPaymentHelper->getAdminAmountAndSellerData(
                        $creditmemoItemsData,
                        $adjustmentNegative,
                        $this->orderId
                    );
                   
                    $sellerArr = $sellerAndAdminData['sellerArr'];
                    $adminAmountToRefund = $sellerAndAdminData['adminAmountToRefund'];
                    
                    $receiverData = $this->manageReceiverData(
                        $sellerArr,
                        $refundData,
                        $transArr,
                        $payment
                    );
                    
                    /*
                    * Calculate Admin Shipping and admin shipping tax amount for admin panel
                    */
                    $adminShipping = $this->mpPaymentHelper->getAdminShippingAmount(
                        $receiverData['shippingCharges'],
                        $orderData,
                        $receiverData['refundData']
                    );
                    $adminAmountToRefund += $adminShipping;

                    if (array_key_exists($this->helper->getConfigValue('merchant_id'), $transArr)) {
                        if ($this->baseCurrencyCode == "") {
                            $this->baseCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
                        }
                        $nvp = [
                            "TRANSACTIONID" => $transArr[$this->helper->getConfigValue('merchant_id')],
                            "REFUNDTYPE" => "Partial",
                            "CURRENCYCODE" => $this->baseCurrencyCode,
                            "AMT" => round($adminAmountToRefund, 2),
                            "METHOD" => "RefundTransaction",
                            "VERSION" => "109.0",
                            "USER" => $this->helper->getConfigValue('api_username'),
                            "PWD" => $this->helper->getConfigValue('api_password'),
                            "SIGNATURE" => $this->helper->getConfigValue('api_signature'),
                        ];
                        $response = $this->helper->sendRequestGetResponse($nvp);
                        $this->helper->logDataInLogger("RefundTransaction request : ".json_encode($nvp));
                        $this->createRefundTransaction($payment, $response);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Model_PaymentMethod refund : ".$e->getMessage());
            throw $e;
        }
        return $this;
    }

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode).
     *
     * @see \Magento\Checkout\Controller\Onepage::savePaymentAction()
     * @see Quote\Payment::getCheckoutRedirectUrl()
     *
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        try {
            return $this->_urlBuilder->getUrl(
                'mppaypalexpresscheckout/index/index'
            );
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Model_PaymentMethod getCheckoutRedirectUrl : ".$e->getMessage());
        }
    }

    public function manageReceiverData(
        $sellerArr,
        $refundData,
        $transArr,
        $payment
    ) {
        $shippingCharges = 0;
        try {
            foreach ($sellerArr as $sellerId => $value) {
                $data = $this->mpPaymentHelper->updateShippingRefundData(
                    $this->orderId,
                    $sellerId,
                    $refundData
                );
                $refundData = $data['refund_data'];
                $shippingCharges = $data['shipping_charges'];

                $sellerPaypalId = '';
                $mpExpressCheckout = $this->expressCollectionFactory->create()
                    ->addFieldToFilter('seller_id', $sellerId);

                if ($mpExpressCheckout->getSize()) {
                    foreach ($mpExpressCheckout as $paypaldetail) {
                        $sellerPaypalId = $paypaldetail->getPaypalId();
                    }
                }
                if (($value['seller_refund'] + $shippingCharges) * 1 > 0) {
                    $totalSellerRefund = $value['seller_refund'] + $shippingCharges;
                    $totalSellerRefund = round($totalSellerRefund, 2);
                    if ($sellerPaypalId) {
                        $trPaypalId = $sellerPaypalId;
                    } else {
                        $trPaypalId = $this->helper->getConfigValue('merchant_id');
                    }
                    if (array_key_exists($trPaypalId, $transArr)) {
                        if ($this->baseCurrencyCode == "") {
                            $this->baseCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
                        }
                        $nvp = [
                            "TRANSACTIONID" => $transArr[$trPaypalId],
                            "REFUNDTYPE" => "Partial",
                            "CURRENCYCODE" => $this->baseCurrencyCode,
                            "AMT" => round($totalSellerRefund, 2),
                            "METHOD" => "RefundTransaction",
                            "VERSION" => "109.0",
                            "USER" => $this->helper->getConfigValue('api_username'),
                            "PWD" => $this->helper->getConfigValue('api_password'),
                            "SIGNATURE" => $this->helper->getConfigValue('api_signature')
                        ];
                        if ($trPaypalId!==$this->helper->getConfigValue('merchant_id')) {
                            $nvp['SUBJECT'] = $trPaypalId;
                        }
                        $this->helper->logDataInLogger("RefundTransaction request : ".json_encode($nvp));
                        $response = $this->helper->sendRequestGetResponse($nvp);

                        $this->createRefundTransaction($payment, $response);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Model_PaymentMethod manageReceiverData : ".$e->getMessage());
        }
        return [
            'shippingCharges' => $shippingCharges,
            'refundData' => $refundData
        ];
    }

    public function createRefundTransaction($payment, $response)
    {
        $this->helper->logDataInLogger("RefundTransaction response : ".json_encode($response));
        try {
            if (!empty($response['ACK'])) {
                if ($response['ACK'] == 'Success') {
                    $refundTransId = $response['REFUNDTRANSACTIONID'];
                    $payment->setTransactionId(
                        $refundTransId.'-'.PaymentTransaction::TYPE_REFUND
                    )->setParentTransactionId($this->transactionId)
                        ->setIsTransactionClosed(1)
                        ->setShouldCloseParentTransaction(1);
                    $payment->setAdditionalInformation(
                        [PaymentTransaction::RAW_DETAILS => $response]
                    );
                    $order = $this->orderRepository->get($this->orderId);
                    $transaction = $this->_transactionBuilder->setPayment($payment)
                        ->setOrder($order)
                        ->setTransactionId(
                            $refundTransId.'-'.PaymentTransaction::TYPE_REFUND
                        )
                        ->setAdditionalInformation(
                            [PaymentTransaction::RAW_DETAILS => $response]
                        )
                        ->setFailSafe(true)
                        ->build(PaymentTransaction::TYPE_REFUND);
                    $message = __('Refunded amount for transation id %1', $this->transactionId);
                    $payment->addTransactionCommentsToOrder($transaction, $message);
                    $payment->save();
                    $order->save();
                    $transaction->save()->getId();
                } else {
                    $errorId = $response['L_ERRORCODE0'];
                    $errormsg = __('<br/>ERROR Message: %1 <br/>', urldecode($response['L_LONGMESSAGE0']));
                    throw new \Magento\Framework\Validator\Exception(
                        __('Payment refunding error. %1', $errormsg)
                    );
                }
            } else {
                throw new \Magento\Framework\Validator\Exception(
                    __('Something Went Wrong')
                );
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Model_PaymentMethod createRefundTransaction : ".$e->getMessage());
            throw new \Magento\Framework\Validator\Exception(
                __('Payment refunding error. %1', $e->getMessage())
            );
        }
    }
}
