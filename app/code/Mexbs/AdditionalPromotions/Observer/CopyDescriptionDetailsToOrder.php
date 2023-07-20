<?php
namespace Mexbs\AdditionalPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class CopyDescriptionDetailsToOrder implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if($quote
            && $quote->getShippingAddress()
            && $quote->getShippingAddress()->getDiscountDetails()){
            $observer->getEvent()->getOrder()->setDiscountDetails($quote->getShippingAddress()->getDiscountDetails());
        }

        return $this;
    }
}
