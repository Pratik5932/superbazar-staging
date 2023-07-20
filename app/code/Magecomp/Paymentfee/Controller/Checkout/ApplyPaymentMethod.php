<?php
namespace Magecomp\Paymentfee\Controller\Checkout;

class ApplyPaymentMethod extends \Magento\Framework\App\Action\Action
{

    protected $resultForwardFactory;
    protected $layoutFactory;
    protected $cart;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->layoutFactory = $layoutFactory;
        $this->cart = $cart;

        parent::__construct($context);
    }

    public function execute()
    {
        $paymentMethod = $this->getRequest()->getParam('payment_method');

        $quote = $this->cart->getQuote();
        $quote->getPayment()->setMethod($paymentMethod);

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $quote->save();
    }
}
