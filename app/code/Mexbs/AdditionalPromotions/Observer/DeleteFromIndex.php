<?php
namespace Mexbs\AdditionalPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class DeleteFromIndex implements ObserverInterface
{
    protected $productRuleProcessor;

    public function __construct(\Mexbs\AdditionalPromotions\Model\Indexer\Product\ProductRuleIndexer $productRuleIndexer)
    {
        $this->productRuleProcessor = $productRuleIndexer;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->productRuleProcessor->deleteFromIndexByProductIds($product->getId());
    }
}