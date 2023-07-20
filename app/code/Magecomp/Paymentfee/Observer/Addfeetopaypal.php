<?php
namespace Magecomp\Paymentfee\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;
use \Magento\Checkout\Model\Session;
class Addfeetopaypal implements ObserverInterface
{

    public $checkout;

    public function __construct(Session $checkout)
    {
        $this->checkout = $checkout;
    }


    public function execute(Observer $observer)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $om->get('Psr\Log\LoggerInterface');
        $storeManager->info('Magecomp Test Log - add fee to paypal.php');
        
        $cart = $observer->getEvent()->getCart();
        
        $quote = $this->checkout->getQuote();
        $address = $quote->getIsVirtual() ?
        $quote->getBillingAddress() : $quote->getShippingAddress();
        
        $title =  $address->getMcPaymentfeeDescription();
        
        if ($fee = $address->getBaseMcPaymentfeeAmount()) {
            $cart->addCustomItem($title, 1, $fee);
        }
        return $this;
    }
}
