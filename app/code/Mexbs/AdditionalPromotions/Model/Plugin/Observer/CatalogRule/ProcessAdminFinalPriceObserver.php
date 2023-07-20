<?php
namespace Mexbs\AdditionalPromotions\Model\Plugin\Observer\CatalogRule;

class ProcessAdminFinalPriceObserver
{
    public function aroundExecute(
        \Magento\CatalogRule\Observer\ProcessAdminFinalPriceObserver $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    ){
        $product = $observer->getEvent()->getProduct();

        $returnValue = $proceed($observer);

        $finalPriceAfterCatalogRules = $product->getData('final_price');
        $product->setApFinalPriceAfterCatalogRules($finalPriceAfterCatalogRules);

        return $returnValue;
    }
}