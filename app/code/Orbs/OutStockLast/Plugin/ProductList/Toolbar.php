<?php
namespace Orbs\OutStockLast\Plugin\ProductList;

class Toolbar
{
    public function afterGetAvailableOrders(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject, $result)
    {
        $options = $subject->getAvailableOrders();

        $outOfStockOption = [
            'value' => 'out_of_stock_last',
            'label' => __('Out of Stock Last')
        ];

        $options['out_of_stock_last'] = $outOfStockOption;

        return $options;
    }

    public function aroundSetCollection(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject, \Closure $proceed, $collection)
    {
        $result = $proceed($collection);

        if ($subject->getCurrentOrder() == 'out_of_stock_last') {
            $collection->getSelect()->joinLeft(
                ['stock_status' => $collection->getResource()->getTable('cataloginventory_stock_status')],
                'e.entity_id = stock_status.product_id',
                ['stock_status']
            )->order('stock_status.stock_status DESC');
        }

        return $result;
    }
}