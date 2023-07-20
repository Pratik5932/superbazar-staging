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
namespace Webkul\MpStripe\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Exception\LocalizedException;
use Webkul\MpStripe\Logger\StripeLogger;

class CreateIntent extends Action
{

    protected $checkoutSession;

    protected $_jsonResultFactory;

    /**
     * $newvar variable to check if seller shipping used.
     *
     * @var string
     */
    private $newvar;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param StripeLogger $stripeLogger
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Type\Onepage $onePage
     * @param \Magento\Shipping\Model\ConfigFactory $configFactory
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Session\SessionManager $coreSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        PriceCurrencyInterface $priceCurrency,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        StripeLogger $stripeLogger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Type\Onepage $onePage,
        \Magento\Shipping\Model\ConfigFactory $configFactory,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Session\SessionManager $coreSession,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_jsonResultFactory = $jsonResultFactory;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->mpHelper = $mpHelper;
        $this->priceCurrency = $priceCurrency;
        $this->addressFactory = $addressFactory;
        $this->customerSession = $customerSession;
        $this->configFactory = $configFactory;
        $this->orderFactory = $orderFactory;
        $this->logger = $stripeLogger;
        $this->coreSession = $coreSession;
        $this->onePage = $onePage;
        $this->mpProductFactory = $mpProductFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * create stripe data for checkout page
     */
    public function execute()
    {
        $smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
                                    "vnd", "vuv", "xaf", "xof", "xpf"];
        
        $resultJson = $this->_jsonResultFactory->create();
        $resultJson->setHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store', true);
        $resultJson->setHeader('Pragma', 'no-cache', true);

        $this->helper->setUpDefaultDetails();
        $requestData = $this->getRequest()->getParams();
        if (!empty($requestData['paymentIntentMethod'])) {
            $finalCartData = $this->coreSession->getPaymentDetails();
            $ifSellerInCart = $this->helper->getIfSellerInCart($finalCartData);
            $paymentIntent = \Stripe\PaymentIntent::retrieve(
                $requestData['payment_intent_id']
            );
            try {
                $response = [];
                if ($ifSellerInCart) {
                    foreach ($finalCartData as $sellerId => $paymentDetail) {
                        if (!empty($paymentDetail['cart']['stripe_user_id'])) {
                            $response[$sellerId] = $this->createStripeTransferCharge(
                                $paymentDetail,
                                $paymentIntent['charges']['data'][0]
                            );
                        }
                    }
                }
                return $resultJson->setData($response);

            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                throw new LocalizedException(
                    __(
                        'There was an error capturing the transaction, order has been cancelled',
                        $e->getMessage()
                    )
                );
            }

        } else {
            $orderId = $this->onePage->getCheckout()->getLastOrderId();
            $quote = $this->orderFactory->create()->load($orderId);
            // $finalCart = $this->helper->getFinalCart($quote);
            $finalCart = $this->coreSession->getFinalCart();
            $paymentDetails = $this->helper->getCheckoutFinalData($finalCart, $quote);
            $this->coreSession->setPaymentDetails($paymentDetails);
            try {
                $transfer = $this->createStripeTransfer(
                    $paymentDetails
                );
                return $resultJson->setData($transfer);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                throw new LocalizedException(
                    __(
                        'There was an error capturing the transaction, order has been cancelled',
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * createStripeTranfer create stripe transfer.
     *
     * @param array $paymentDetailsArray seller wise details
     * @param bool  $isAdmin
     *
     * @return array or boolean
     */
    public function createStripeTransfer($paymentDetailsArray)
    {
        try {
            $finalArray = [];
            $finalArray["payment_method_types"] = ["card"];
            $finalArray['amount'] = 0;
            foreach ($paymentDetailsArray as $paymentDetails) {
                $finalArray['amount'] += $paymentDetails['payment_array']['amount'];
                $finalArray['currency'] = $paymentDetails['payment_array']['currency'];
                $finalArray['transfer_group'] = $paymentDetails['payment_array']['order_id'];
            }
            // Create a PaymentIntent:
            $intenet = \Stripe\PaymentIntent::create($finalArray);

            return $intenet;
        } catch (\Stripe\Error $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }
    /**
     * createStripeCharge create stripe charge.
     *
     * @param array $paymentDetailsArray seller wise details
     * @param bool  $isAdmin
     *
     * @return array or boolean
     */
    public function createStripeCharge($paymentDetailsArray, $requestData)
    {
        try {
            $payment_intent = \Stripe\Charge::create([
                // 'payment_method_types' => ['card'],
                "source" => "tok_visa",
                'amount' => $paymentDetailsArray['payment_array']['amount'],
                'currency' => $paymentDetailsArray['payment_array']['currency'],
                'application_fee_amount' => $paymentDetailsArray['payment_array']['application_fee'],
                // 'confirmation_method' => 'manual',
                // 'confirm' => true,
                // 'payment_method' => $requestData['paymentIntentMethod'],
                ], ['stripe_account' => $paymentDetailsArray['cart']['stripe_user_id']]);
            return $payment_intent;
        } catch (\Stripe\Error $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }

    /**
     * createStripeTransferCharge create stripe charge.
     *
     * @param array $paymentDetailsArray whole details
     *
     * @return array or boolean
     */
    public function createStripeTransferCharge($paymentDetailsArray, $charge)
    {
        try {
            if (!empty($paymentDetailsArray['cart']['stripe_user_id'])) {
                $transfer = \Stripe\Transfer::create([
                    "amount" => $paymentDetailsArray['payment_array']['amount'],
                    "currency" => $paymentDetailsArray['payment_array']['currency'],
                    "destination" => $paymentDetailsArray['cart']['stripe_user_id'],
                    "source_transaction" => $charge['id'],
                    "transfer_group" => $paymentDetailsArray['payment_array']['order_id'],
                  ]);
                return $transfer;
            }
        } catch (\Stripe\Error $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }
}
