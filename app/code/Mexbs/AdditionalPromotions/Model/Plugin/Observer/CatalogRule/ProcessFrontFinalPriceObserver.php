<?php
namespace Mexbs\AdditionalPromotions\Model\Plugin\Observer\CatalogRule;

class ProcessFrontFinalPriceObserver
{
    public function aroundExecute(
        \Magento\CatalogRule\Observer\ProcessFrontFinalPriceObserver $subject,
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