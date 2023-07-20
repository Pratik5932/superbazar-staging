<?php
// http://testbenchonline.com/magento215/polipayments/checkout/failure

namespace Poli\PoliPayments\Controller\Checkout;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Failure extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface {
    /**
    * @var \Magento\Checkout\Model\Session
    */
    protected $_checkoutSession;

    /**
    * @var \Poli\PoliPayments\Model\PoliPayments $polipayments
    */
    protected $_polipayments;

	/* Support CsrfAwareActionInterface */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool {
        return true;
    }

    /**
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Checkout\Model\Session $checkoutSession
    * @param \Poli\PoliPayments\Model\PoliPayments $polipayments
    */
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Checkout\Model\Session $checkoutSession,
    \Poli\PoliPayments\Model\PoliPayments $polipayments
    ) {
        $this->_polipayments = $polipayments;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
    * Start checkout by requesting checkout code and dispatching customer to Coinbase.
    */
    public function execute() {
		$order = $this->_checkoutSession->getLastRealOrder();
		if ($order->getId() && $order->getState() != \Magento\Sales\Model\Order::STATE_CANCELED) {
			$order->registerCancellation( "Cancelled")->save();
		}
        $this->_checkoutSession->restoreQuote();
		$url=$this->_polipayments->_getUrl( "checkout/cart" );
		$this->getResponse()->setRedirect( $url );
	}
}
