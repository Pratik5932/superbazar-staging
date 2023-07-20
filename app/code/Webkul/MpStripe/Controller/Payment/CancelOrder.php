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
use Magento\Framework\Exception\LocalizedException;
use Webkul\MpStripe\Logger\StripeLogger;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

class CancelOrder extends Action
{
    protected $_jsonResultFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;

    /**
     * $newvar variable to check if seller shipping used.
     *
     * @var string
     */
    private $newvar;
    private $orderSender;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param StripeLogger $stripeLogger
     * @param \Magento\Checkout\Model\Type\Onepage $onePage
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        StripeLogger $stripeLogger,
        OrderSender $orderSender,
        TransportBuilder $transportBuilder,
        OrderRepositoryInterface $orderRepository,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Checkout\Model\Type\Onepage $onePage,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    ) {
        $this->_jsonResultFactory = $jsonResultFactory;
        $this->orderFactory = $orderFactory;
        $this->logger = $stripeLogger;
        $this->onePage = $onePage;
        $this->transportBuilder = $transportBuilder;
        $this->orderSender = $orderSender;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerModel = $customerModel;
        $this->inlineTranslation = $inlineTranslation;
        parent::__construct($context);
    }

    /**
     * create stripe data for checkout page
     */
    public function execute()
    {
        $resultJson = $this->_jsonResultFactory->create();
        $resultJson->setHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store', true);
        $resultJson->setHeader('Pragma', 'no-cache', true);

        $requestData = $this->getRequest()->getParams();
        $orderId = $this->onePage->getCheckout()->getLastOrderId();
        $order = $this->orderFactory->create()->load($orderId);
        $this->helper->setUpDefaultDetails();
        $paymentIntent = \Stripe\PaymentIntent::retrieve(
            $requestData['payment_intent_id']
        );
        $paymentIntent->cancel();
        $this->declineOrder($order);
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $quote->setIsActive(1)->setReservedOrderId(null);
        $this->quoteRepository->save($quote);
        $this->checkoutSession->setQuoteId($quote->getId());

        $message = $requestData['message'];
        $this->cancelOrderMail($order, $message);

        $this->messageManager->addError(
            __(
                $requestData['message']
            )
        );
        $response = ['status' => true];
        return $resultJson->setData($response);
    }

    /**
     * Register order cancellation. Return money to customer if needed.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string                     $message
     * @param bool                       $voidPayment
     */
    private function declineOrder($order = null)
    {
        if ($order != null) {
            $message = __('Order cancelled because stripe payment failed');
            try {
                $order->cancel();
                $order->registerCancellation($message);
                $history = $order->addStatusHistoryComment($message, false);
                $history->setIsCustomerNotified(true);
                $order->save();
            } catch (\Exception $e) {
                $this->logger->critical("declineOrder : ".$e->getMessage());
            }
        }
    }

    /**
     * send Disapproval mail function
     *
     * @param  $customer
     * @return void
     */
    public function cancelOrderMail($order, $message)
    {
        $template_id = 'marketplace_email_order_cancled_notification_template';
        
        $templateVars = [
            'myvar1' => $order->getIncrementId(),
            'myvar2' => $message
        ];
        $this->sendMail($template_id, $templateVars, $order);
    }

    public function sendMail($templateId, $templateVars, $order)
    {
        try {
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()
            ];

            $adminEmail = $this->recieverEmail();
            $adminName = $this->recieverName();
            $adminToEmail = $this->getAdminEmail();
            $sellerEmail = $this->getSellerEmail($order);
            
            $from = ['email' => $adminEmail, 'name' => $adminName];
            $email = $order->getCustomerEmail();
            // $to = [$email, $adminToEmail, $sellerEmail];
            $to = $email;
            $this->inlineTranslation->suspend();
            if ($sellerEmail != '') {
                $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->addCc($adminToEmail)
                ->addCc($sellerEmail)
                ->getTransport();
                $transport->sendMessage();
            } else {
                $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->addCc($adminToEmail)
                ->getTransport();
                $transport->sendMessage();
            }
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
        }
    }

    /**
     * Get Email function of Admin
     * @return [string]
     */
    public function recieverEmail()
    {
        $adminEmail = $this->scopeConfig->getValue(
            'trans_email/ident_sales/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $adminEmail;
    }

    /**
     * Get Name function of Admin
     * @return [string]
     */
    public function recieverName()
    {
        $name = $this->scopeConfig->getValue(
            'trans_email/ident_sales/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $name;
    }

    public function getAdminEmail()
    {
        $adminEmail = $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $adminEmail;
    }

    public function getSellerEmail($order)
    {
        $cart = $this->helper->getSellerCart($order);
        
        if ($cart[0]['data']['seller_id'] != '') {
            $sellerId = $cart[0]['data']['seller_id'];
            $customer = $this->customerModel->create()->load($sellerId);
            $customerEmail = $customer->getEmail();
            return $customerEmail;
        } else {
            $customerEmail = '';
            return $customerEmail;
        }
    }
}
