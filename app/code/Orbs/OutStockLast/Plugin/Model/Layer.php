<?php

namespace Orbs\OutStockLast\Plugin\Model;

/**
 * Override Layer Class
*/
class Layer
    {
        public function getProductCollection(\Magento\Catalog\Model\Layer $subject, $printQuery = false, $logQuery = false)
        {
           $orderBy = $subject->getSelect()->getPart(\Zend_Db_Select::ORDER);
        $outOfStockOrderBy = array('is_salable DESC');
        $subject->getSelect()->reset(\Zend_Db_Select::ORDER);
        $subject->getSelect()->order($outOfStockOrderBy);

        return [$printQuery, $logQuery];
          
        }
    }
