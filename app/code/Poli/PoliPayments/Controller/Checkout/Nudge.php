<?php
// http://testbenchonline.com/magento2/index.php/polipayments/checkout/initiate

namespace Poli\PoliPayments\Controller\Checkout;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Nudge extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
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
		list($success, $pay_reference, $currency, $amount, $gateway_status, $reference, $merchant_data, $bank )=$this->_polipayments->process_nudge( );
		if( $success ){
			$this->_polipayments->update_order( $reference, $currency, $gateway_status, $pay_reference, $amount );
		}
    }

    /**
    * Get order object.
    *
    * @return \Magento\Sales\Model\Order
    */
    protected function getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }
}
