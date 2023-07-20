<?php
namespace Superbazaar\General\Plugin\Ui\DataProvider\Product;

class ProductDataProvider
{
    /**
    * @param \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject
    * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
    * @return mixed
    */
    public function afterGetCollection(
        \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject,
        $collection
    ) {
        $columns = $collection->getSelect()->getPart(\Zend_Db_Select::COLUMNS);
        /*   if (!$collection->isLoaded() && !$this->checkJoin($columns)) {
        $collection->joinTable(
        'cataloginventory_stock_status',
        'product_id=entity_id',
        ["stock_status" => "stock_status"],
        null ,
        'left'
        )->addAttributeToSelect('stock_status');
        }*/
        if (!$collection->isLoaded()) {
           // $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
          /*  $productStatusAttributeId = $objectManager
            ->create('Magento\Eav\Model\Config')
            ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, \Magento\Catalog\Api\Data\ProductInterface::STATUS)
            ->getAttributeId();
            ['product_entity' => $collection->getTable('catalog_product_entity')];
            $collection->getSelect()->joinLeft('catalog_product_entity_int',
                "catalog_product_entity_int.entity_id =15 AND catalog_product_entity_int.attribute_id = $productStatusAttributeId",
                ["product_status" => "value"],
                null ,
                'left');*/


        }
        return $collection;
    }

    /**
    * @param array $columns
    * @return bool
    */
    private function checkJoin($columns)
    {
        foreach ($columns as $column) {
            if(is_array($column)) {
                if(in_array('stock_status', $column)) {
                    return true;
                }
            }
        }

        return false;
    }
}
?>