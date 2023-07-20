<?php

namespace Orbs\OutStockLast\Plugin\Widget;

/**
 * Override Layer Class
*/
class ProductList
    {
        public function afterCreateCollection(\Magento\CatalogWidget\Block\Product\ProductsList $subject, $result)
        {
          $orderBy = $result->getSelect()->getPart(\Zend_Db_Select::ORDER);
        $outOfStockOrderBy = array('is_salable DESC');
          
        $result->getSelect()->reset(\Zend_Db_Select::ORDER);
        $result->getSelect()->order($outOfStockOrderBy);
        return $result;
          
        }
    }