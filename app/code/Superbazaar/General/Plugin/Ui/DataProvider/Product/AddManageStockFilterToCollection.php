<?php
namespace Superbazaar\General\Plugin\Ui\DataProvider\Product;
 
class AddManageStockFilterToCollection implements \Magento\Ui\DataProvider\AddFilterToCollectionInterface
{
    public function addFilter(\Magento\Framework\Data\Collection $collection, $field, $condition = null)
    {
        if (isset($condition['eq'])) {
            echo "sdfsd";exit; 
            $collection->addFieldToFilter($field, $condition);
        }
    }
}