<?php
namespace Magecomp\Paymentfee\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;
class Paymentfeetoorder implements ObserverInterface
{

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        $address = $quote->isVirtual() ?
            $quote->getBillingAddress() : $quote->getShippingAddress();

        $paymentFeeFields = [
            'mc_paymentfee_amount',
            'mc_paymentfee_tax_amount',
            'base_mc_paymentfee_amount',
            'base_mc_paymentfee_tax_amount',
            'mc_paymentfee_description',
        ];
        foreach ($paymentFeeFields as $fieldName) {
            if ($fieldValue = $address->getData($fieldName)) {
                $order->setData($fieldName, $fieldValue);
            }
        }
        return $this;
    }
}
