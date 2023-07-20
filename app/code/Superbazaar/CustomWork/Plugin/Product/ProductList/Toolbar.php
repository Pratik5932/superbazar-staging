<?php

namespace Superbazaar\CustomWork\Plugin\Product\ProductList;

use Magento\Catalog\Block\Product\ProductList\Toolbar as Productdata;

class Toolbar
{
    public function aroundGetLastNum(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject, \Closure $proceed)
    {

        $collection = $subject->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + $collection->count();
        // print_r($subject);
        // $currentOrder = $subject->getCurrentOrder();
        // if ($currentOrder) {
        //     if ($currentOrder == "newest_product") {
        //         $direction = $subject->getCurrentDirection();
        //         $collection->getSelect()->order('created_at ' . $direction);
        //     }
        //     return $proceed($collection);
        // }
    }
}