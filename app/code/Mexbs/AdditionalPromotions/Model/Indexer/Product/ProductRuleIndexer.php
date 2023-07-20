<?php
namespace Mexbs\AdditionalPromotions\Model\Indexer\Product;

use Mexbs\AdditionalPromotions\Model\Indexer\AbstractIndexer;

class ProductRuleIndexer extends AbstractIndexer
{
    protected function doExecuteList($ids)
    {
        $this->indexBuilder->reindexByProductIdsRuleIds(array_unique($ids));
        $this->getCacheContext()->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);
    }


    protected function doExecuteRow($id)
    {
        $this->indexBuilder->reindexByProductIdsRuleIds([$id]);
    }

    public function getIdentities()
    {
        return [];
    }

    public function deleteFromIndexByProductIds($id){
        $this->indexBuilder->deleteFromIndexByProductIds($id);
    }
}
