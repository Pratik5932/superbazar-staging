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
namespace Webkul\Mppaypalexpresscheckout\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * Mppaypalexpresscheckout Index ReturnAction Controller.
 */
class ReturnAction extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * Internal cache of checkout models
     *
     * @var array
     */
    private $checkoutTypes = [];

    /**
     * Checkout mode type
     *
     * @var string
     */
    private $checkoutType = 'Webkul\Mppaypalexpresscheckout\Model\Express\Checkout';

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Model\Express\Checkout
     */
    private $checkout;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $paypalSession;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote = false;

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Helper\Data
     */
    private $helper;

    /**
     * Config method type
     *
     * @var string
     */
    private $configMethod = "mppaypalexpresscheckout";

    /**
     * Config mode type
     *
     * @var string
     */
    private $configType = 'Webkul\Mppaypalexpresscheckout\Model\Config';

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Model\Express\Checkout\Factory
     */
    private $checkoutFactory;

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Model\Config
     */
    private $config;

    /**
     * @var \Webkul\Marketplace\Helper\Payment
     */
    private $mpPaymentHelper;

    /**
     * @param \Magento\Framework\App\Action\Context                          $context
     * @param \Magento\Checkout\Model\Session                                $checkoutSession
     * @param \Magento\Framework\Session\Generic                             $paypalSession
     * @param \Webkul\Mppaypalexpresscheckout\Helper\Data                    $helper
     * @param \Webkul\Mppaypalexpresscheckout\Model\Express\Checkout\Factory $checkoutFactory
     * @param \Webkul\Marketplace\Helper\Payment                             $mpPaymentHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\Generic $paypalSession,
        \Webkul\Mppaypalexpresscheckout\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\Mppaypalexpresscheckout\Model\Config $config,
        \Webkul\Mppaypalexpresscheckout\Model\Express\Checkout\Factory $checkoutFactory,
        \Webkul\Marketplace\Helper\Payment $mpPaymentHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->paypalSession = $paypalSession;
        $this->helper = $helper;
        $this->checkoutFactory = $checkoutFactory;
        $this->mpPaymentHelper = $mpPaymentHelper;
        $this->logger = $logger;
        parent::__construct($context);
        $this->config = $config;
    }

    public function execute()
    {
        /**
         * @var \Magento\Framework\Controller\Result\Redirect $resultRedirect
        */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $params = $this->getRequest()->getParams();

        try {
            if (isset($params['PayerID']) && isset($params['token'])) {
                $resultGetExpress = $this->getExpressCheckout($params);
                if ($resultGetExpress
                    && is_array($resultGetExpress)
                    && isset($resultGetExpress['error'])
                    && $resultGetExpress['error']
                ) {
                    $this->messageManager->addError(
                        $resultGetExpress['message']
                    );
                    return $resultRedirect->setPath('checkout/cart');
                } else {
                    $resultDoExpress = $this->doExpressCheckout($params);
                    $this->logger->critical(json_encode($resultDoExpress, true));
                    if ($resultDoExpress) {
                        $this->_initCheckout();
                        $this->_initToken($params['token']);
                        $this->checkout->place($this->_initToken());

                        // prepare session to success or cancellation page
                        $this->checkoutSession->clearHelperData();

                        // last successful quote
                        $quoteId = $this->_getQuote()->getId();
                        $this->checkoutSession->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

                        // an order may be created
                        $order = $this->checkout->getOrder();

                        if ($order) {
                            $this->checkoutSession->setLastOrderId($order->getId())
                                ->setLastRealOrderId($order->getIncrementId())
                                ->setLastOrderStatus($order->getStatus());

                            $lastTransId = (
                                $order->getPayment()
                                ) ? $order->getPayment()->getLastTransId() : 0;
                            $transactionId = $this->getTransactionId(
                                $lastTransId,
                                $resultDoExpress,
                                $order
                            );
                        }
                        $this->_initToken(false); // no need in token anymore
                        return $resultRedirect->setPath('checkout/onepage/success');
                    } else {
                        $this->messageManager->addError(
                            __("you have already paid for this order")
                        );
                        return $resultRedirect->setPath('checkout/cart');
                    }
                }
            } else {
                return $resultRedirect->setPath('checkout/cart');
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_index_returnAction execute : ".$e->getMessage());
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t place the order.')
            );
            return $resultRedirect->setPath('checkout/cart');
        }
    }

    private function getTransactionId(
        $lastTransId,
        $resultDoExpress,
        $order
    ) {
    
        if (isset($lastTransId)
            || !$lastTransId
            || $lastTransId==""
            || $lastTransId!==$resultDoExpress['TOKEN']
        ) {
            $transactionId = $this->mpPaymentHelper->saveTransaction(
                $order,
                $resultDoExpress,
                $resultDoExpress['TOKEN']
            );
        }
    }

    private function getExpressCheckout($data)
    {
        try {
            $nvps = [
                "USER" => $this->helper->getConfigValue('api_username'),
                "PWD" => $this->helper->getConfigValue('api_password'),
                "SIGNATURE" => $this->helper->getConfigValue('api_signature'),
                "METHOD" => "GetExpressCheckoutDetails",
                "VERSION" => "85.0",
                "TOKEN" => $data['token']
            ];
            $this->helper->logDataInLogger("GetExpressCheckoutDetails Request : ".json_encode($nvps));
            $getEC = $this->helper->sendRequestGetResponse($nvps);
            $this->helper->logDataInLogger("GetExpressCheckoutDetails Response : ".json_encode($getEC));
            if (isset($getEC['ACK']) && $getEC['ACK']=="Success") {
                $checkoutMethod = $this->_getQuote()->getCheckoutMethod();

                if ($checkoutMethod=="guest" && !$this->_getQuote()->getCustomerId()) {
                    if ($guestShippingAddress = $this->_getQuote()->getShippingAddress()
                        && $this->_getQuote()->getShippingAddress()->getEmail()
                    ) {
                        $this->_getQuote()->getBillingAddress()->addData($guestShippingAddress->getData());
                    } elseif ($guestBillingAddress = $this->_getQuote()->getBillingAddress()
                        && $this->_getQuote()->getBillingAddress()->getEmail()
                    ) {
                        $this->_getQuote()->getBillingAddress()->addData($guestBillingAddress->getData());
                    }
                }

                if (isset($getEC['CHECKOUTSTATUS'])) {
                    if ($getEC['CHECKOUTSTATUS']=="PaymentActionFailed") {
                        return [
                            'error' => true,
                            'message' => "PayPal declined the transaction, please pay again"
                        ];
                    } else {
                        return [
                            'error' => false
                        ];
                    }
                }
            } else {
                return [
                    'error' => true,
                    'message' => __("something went wrong!!!")
                ];
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_index_returnAction getExpressCheckout : ".$e->getMessage());
            return [
                'error' => true,
                'message' => __("something went wrong!!!")
            ];
        }
    }

    private function _initCheckout()
    {
        try {
            $quote = $this->_getQuote();
            if (!$quote->hasItems() || $quote->getHasError()) {
                $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t initialize Express Checkout.'));
            }
            if (!isset($this->checkoutTypes[$this->checkoutType])) {
                $parameters = [
                    'params' => [
                        'quote' => $quote,
                        'config' => $this->config,
                    ],
                ];
                $this->checkoutTypes[$this->checkoutType] = $this->checkoutFactory
                    ->create($this->checkoutType, $parameters);
            }
            $this->checkout = $this->checkoutTypes[$this->checkoutType];
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_index_returnAction _initCheckout : ".$e->getMessage());
        }
    }

    /**
     * Search for proper checkout token in request or session or (un)set specified one
     * Combined getter/setter
     *
     * @param  string|null $setToken
     * @return $this|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _initToken($setToken = null)
    {
        try {
            if (null !== $setToken) {
                if (false === $setToken) {
                    // security measure for avoid unsetting token twice
                    if (!$this->_getSession()->getMpExpressCheckoutToken()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('PayPal Express Checkout Token does not exist.')
                        );
                    }
                    $this->_getSession()->unsMpExpressCheckoutToken();
                } else {
                    $this->_getSession()->setMpExpressCheckoutToken($setToken);
                }
                return $this;
            }
            $setToken = $this->getRequest()->getParam('token');
            if ($setToken) {
                if ($setToken !== $this->_getSession()->getMpExpressCheckoutToken()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('A wrong PayPal Express Checkout Token is specified.')
                    );
                }
            } else {
                $setToken = $this->_getSession()->getMpExpressCheckoutToken();
            }
            return $setToken;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_index_returnAction _initToken : ".$e->getMessage());
        }
    }

    /**
     * PayPal session instance getter
     *
     * @return \Magento\Framework\Session\Generic
     */
    private function _getSession()
    {
        try {
            return $this->paypalSession;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_index_returnAction _getSession : ".$e->getMessage());
        }
    }

    /**
     * Return checkout quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    private function _getQuote()
    {
        try {
            if (!$this->quote) {
                $this->quote = $this->checkoutSession->getQuote();
            }
            return $this->quote;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_index_returnAction _getQuote : ".$e->getMessage());
        }
    }

    private function doExpressCheckout($data)
    {
        try {
            $nvps = [];
            $finalShipping = $this->helper->prepareFinalShipping($this->_getQuote());

            $nvps = [
                "USER" => $this->helper->getConfigValue('api_username'),
                "PWD" => $this->helper->getConfigValue('api_password'),
                "SIGNATURE" => $this->helper->getConfigValue('api_signature'),
                "METHOD" => "DoExpressCheckoutPayment",
                "VERSION" => "85.0",
                "PAYMENTACTION" => 'Sale'
            ];

            $cartdata = $this->helper->getCartDetail($this->_getQuote());
            $i=0;
            $s=5;
            $amt=0;
            $adminship = true;
            foreach ($cartdata as $key => $values) {
                
                $price=0;
                $itemc = 0;
                foreach ($values as $info) {
                    $price += floatval(
                        round($info['amt']/$info['qty'], 2) * $info['qty']
                    );
                }
                $amt = $price;

                if ($price==0) {
                    continue;
                }
                foreach ($values as $info) {
                    $nvps["L_PAYMENTREQUEST_".$i."_ITEMCATEGORY".$itemc] = "physical";
                    $nvps["L_PAYMENTREQUEST_".$i."_NAME".$itemc] = $info['name'];
                    $nvps["L_PAYMENTREQUEST_".$i."_NUMBER".$itemc] = $info['number'];
                    $nvps["L_PAYMENTREQUEST_".$i."_QTY".$itemc] = $info['qty'];
                    $nvps["L_PAYMENTREQUEST_".$i."_AMT".$itemc] = round($info['amt']/$info['qty'], 2);
                    $itemc++;
                }
                $paypalid = $this->helper->getSellerPaypalEmail($key);
                if (isset($finalShipping[$key]) && !empty($finalShipping[$key])) {
                        $adminship = false;
                        $amt += floatval(
                            round($finalShipping[$key]['amount'], 2)
                        );
                        $nvps["PAYMENTREQUEST_".$i."_SHIPPINGAMT"] = round($finalShipping[$key]['amount'], 2);
                }
                $nvps["PAYMENTREQUEST_".$i."_CURRENCYCODE"] = $this->_getQuote()->getBaseCurrencyCode();
                $nvps["PAYMENTREQUEST_".$i."_AMT"] = $amt;
                $nvps["PAYMENTREQUEST_".$i."_SELLERPAYPALACCOUNTID"] = $paypalid;
                $nvps["PAYMENTREQUEST_".$i."_ITEMAMT"] = $price;
                $nvps["PAYMENTREQUEST_".$i."_PAYMENTACTION"] = "Sale";
                $nvps["PAYMENTREQUEST_".$i."_PAYMENTREQUESTID"] = 'CAR6544-PAYMENT'.$i;
                $i++;
            }

            if  ($adminship) {
                $key = 0;
                $price = 0;
                $itemc = 0;
                if (isset($finalShipping[$key])
                    && !empty($finalShipping[$key])
                    && $finalShipping[$key]['amount']>0
                ) {
                    $qty=1;
                    $nvps["L_PAYMENTREQUEST_".$i."_ITEMCATEGORY".$itemc] = "physical";
                    $nvps["L_PAYMENTREQUEST_".$i."_NAME".$itemc] = 'shipping';
                    $nvps["L_PAYMENTREQUEST_".$i."_NUMBER".$itemc] = $finalShipping[$key]['method'];
                    $nvps["L_PAYMENTREQUEST_".$i."_QTY".$itemc] = $qty;
                    $nvps["L_PAYMENTREQUEST_".$i."_AMT".$itemc] = round($finalShipping[$key]['amount'], 2);

                    $price += floatval(
                        round($finalShipping[$key]['amount'], 2)
                    );
                }
                
                if ($price != 0) {
                    $paypalid = $this->helper->getSellerPaypalEmail($key);
                    
                    $nvps["PAYMENTREQUEST_".$i."_CURRENCYCODE"] = $this->_getQuote()->getBaseCurrencyCode();
                    $nvps["PAYMENTREQUEST_".$i."_AMT"] = $price;
                    $nvps["PAYMENTREQUEST_".$i."_SELLERPAYPALACCOUNTID"] = $paypalid;
                    $nvps["PAYMENTREQUEST_".$i."_ITEMAMT"] = $price;
                    $nvps["PAYMENTREQUEST_".$i."_PAYMENTACTION"] = "Sale";
                    $nvps["PAYMENTREQUEST_".$i."_PAYMENTREQUESTID"] = 'CAR6544-PAYMENT'.$i;
                }
            }
            $nvps["TOKEN"] = $data['token'];
            $nvps["PAYERID"] = $data['PayerID'];
            $this->helper->logDataInLogger("DoExpressCheckoutPayment Request : ".json_encode($nvps));
            $doEC = $this->helper->sendRequestGetResponse($nvps);
            $this->helper->logDataInLogger("DoExpressCheckoutPayment Response : ".json_encode($doEC));
            if (isset($doEC['ACK']) && ($doEC['ACK']=="Success" || $doEC['ACK']=="PartialSuccess")) {
                return $doEC;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_index_returnAction doExpressCheckout : ".$e->getMessage());
        }
    }
}
