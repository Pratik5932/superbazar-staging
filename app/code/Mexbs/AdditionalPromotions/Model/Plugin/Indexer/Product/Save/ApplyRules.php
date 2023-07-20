<?php
namespace Mexbs\AdditionalPromotions\Model\Plugin\Indexer\Product\Save;

class ApplyRules
{
    protected $productRuleProcessor;

    public function __construct(\Mexbs\AdditionalPromotions\Model\Indexer\Product\ProductRuleProcessor $productRuleProcessor)
    {
        $this->productRuleProcessor = $productRuleProcessor;
    }

    public function aroundSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        callable $proceed,
        \Magento\Framework\Model\AbstractModel $product
    ) {
        $productResource = $proceed($product);
        if (!$product->getIsMassupdate()) {
            $this->productRuleProcessor->reindexRow($product->getId());
        }
        return $productResource;
    }
}