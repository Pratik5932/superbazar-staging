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
namespace Webkul\Mppaypalexpresscheckout\Model\Express;

use Webkul\Mppaypalexpresscheckout\Model\Config as MpPaypalConfig;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Quote\Model\Quote\Address;
use Magento\Framework\DataObject;

/**
 * Express Checkout Class
 */
class Checkout
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * Config instance
     *
     * @var PaypalConfig
     */
    protected $_config;

    /**
     * Payment method type
     *
     * @var string
     */
    protected $_methodType = MpPaypalConfig::METHOD_CODE;

    /**
     * State helper variable
     *
     * @var string
     */
    protected $_redirectUrl = '';

    /**
     * State helper variable
     *
     * @var string
     */
    protected $_pendingPaymentMessage = '';

    /**
     * State helper variable
     *
     * @var string
     */
    protected $_checkoutRedirectUrl = '';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Customer ID
     *
     * @var int
     */
    protected $_customerId;

    /**
     * Billing agreement that might be created during order placing
     *
     * @var
     */
    protected $_billingAgreement;

    /**
     * Order
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutData;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $quoteManagement;

    /**
     * @param \Magento\Checkout\Helper\Data              $checkoutData
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \Magento\Quote\Model\QuoteManagement       $quoteManagement
     * @param \Magento\Checkout\Model\Session            $checkoutSession
     * @param OrderSender                                $orderSender
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param array                                      $params
     * @throws \Exception
     */
    public function __construct(
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Checkout\Model\Session $checkoutSession,
        OrderSender $orderSender,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        $params = []
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->_checkoutData = $checkoutData;
        $this->_checkoutSession = $checkoutSession;
        $this->orderSender = $orderSender;
        $this->quoteRepository = $quoteRepository;
        $this->_customerSession = isset($params['session'])
            && $params['session'] instanceof \Magento\Customer\Model\Session ? $params['session'] : $customerSession;

        if (isset($params['config']) && $params['config'] instanceof MpPaypalConfig) {
            $this->_config = $params['config'];
        } else {
            throw new \Exception('Config instance is required.');
        }

        if (isset($params['quote']) && $params['quote'] instanceof \Magento\Quote\Model\Quote) {
            $this->_quote = $params['quote'];
        } else {
            throw new \Exception('Quote instance is required.');
        }
    }

    /**
     * Set shipping method to quote, if needed
     *
     * @param  string $shippingMethodCode
     * @return void
     */
    public function updateShippingMethod($shippingMethodCode)
    {
        $shippingAddress = $this->_quote->getShippingAddress();
        if (!$this->_quote->getIsVirtual() && $shippingAddress) {
            if ($shippingMethodCode != $shippingAddress->getShippingMethod()) {
                $this->ignoreAddressValidation();
                $shippingAddress->setShippingMethod($shippingMethodCode)->setCollectShippingRates(true);
                $cartExtension = $this->_quote->getExtensionAttributes();
                if ($cartExtension && $cartExtension->getShippingAssignments()) {
                    $cartExtension->getShippingAssignments()[0]
                        ->getShipping()
                        ->setMethod($shippingMethodCode);
                }
                $this->_quote->collectTotals();
                $this->quoteRepository->save($this->_quote);
            }
        }
    }

    /**
     * Place the order when customer returned from PayPal until this moment all quote data must be valid.
     *
     * @param                                        string      $token
     * @param                                        string|null $shippingMethodCode
     * @return                                       void
     */
    public function place($token, $shippingMethodCode = null)
    {
        if ($shippingMethodCode) {
            $this->updateShippingMethod($shippingMethodCode);
        }

        if ($this->getCheckoutMethod() == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote();
        }

        $this->ignoreAddressValidation();

        $this->_quote->setPaymentMethod('mppaypalexpresscheckout');
        $this->_quote->setInventoryProcessed(false);
        $this->_quote->getPayment()->importData(['method' => 'mppaypalexpresscheckout']);
        $this->_quote->collectTotals();
        $this->_quote->save();

        $order = $this->quoteManagement->submit($this->_quote);

        if (!$order) {
            return;
        }

        switch ($order->getState()) {
            // even after placement paypal can disallow to authorize/capture, but will wait until bank transfers money
            case \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT:
                break;
                // regular placement, when everything is ok
            case \Magento\Sales\Model\Order::STATE_PROCESSING:
            case \Magento\Sales\Model\Order::STATE_COMPLETE:
            case \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW:
                $this->orderSender->send($order);
                $this->_checkoutSession->start();
                break;
            default:
                break;
        }
        $this->_order = $order;
    }

    /**
     * Get checkout method
     *
     * @return string
     */
    public function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER;
        }
        if (!$this->_quote->getCheckoutMethod()) {
            if ($this->_checkoutData->isAllowedGuestCheckout($this->_quote)) {
                $this->_quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $this->_quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $this->_quote->getCheckoutMethod();
    }

    /**
     * Make sure addresses will be saved without validation errors
     *
     * @return void
     */
    private function ignoreAddressValidation()
    {
        $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->_quote->getIsVirtual()) {
            $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$this->_quote->getBillingAddress()->getEmail()
            ) {
                $this->_quote->getBillingAddress()->setSameAsBilling(1);
            }
        }
    }

    /**
     * Get customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @return $this
     */
    private function prepareGuestQuote()
    {
        $quote = $this->_quote;
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        return $this;
    }

    public function getOrder()
    {
        return $this->_order;
    }
}
