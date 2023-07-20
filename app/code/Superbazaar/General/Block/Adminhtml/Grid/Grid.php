<?php
/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Superbazaar\General\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

class Grid extends \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid
{
   

    /**
    * Prepare columns
    *
    * @return $this
    */
    protected function _prepareColumns()
    {
        $this->addColumn('status', [
            'header'   => __('Status'),
            'index'    => 'status',
            'type'     => 'options',
            'sortable' => false,
            'options'  => [
                '' => __(''),
                0 => __('Disabled'),
                1 => __('Enabled'),
            ],
        ]);
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'index' => 'entity_id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Product'),
                'renderer' => \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Product::class,
                'index' => 'name'
            ]
        );
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'column_css_class' => 'price',
                'type' => 'currency',
                'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
                'rate' => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
                'index' => 'price',
                'renderer' => \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price::class
            ]
        );
        $this->addColumn(
            'remain_qty',
            [
                'filter' => false,
                'sortable' => false,
                'header' => __('Quantity 49797'),
                'renderer' => \Superbazaar\General\Block\Adminhtml\Grid\Renderer\Remain::class,
                //'values' => 3,
                'name' => 'remain_qty',
                'inline_css' => 'remain_qty',
                'type' => 'text',
                'validate_class' => 'validate-number',
            ]
        );
        $this->addColumn(
            'previous_order_cost_price',
            [
                'filter' => false,
                'sortable' => false,
                'header' => __('Previous order cost price'),
                'renderer' => \Superbazaar\General\Block\Adminhtml\Grid\Renderer\Pordercost::class,
                //'values' => 3,
                'name' => 'previous_order_cost_price',
                'inline_css' => 'remain_qty',
                'validate_class' => 'validate-number',
                'index' => 'previous_order_cost_price'
            ]
        );
        $this->addColumn(
            'new_order_cost_price',
            [
                'filter' => false,
                'sortable' => false,
                'header' => __('New order cost price'),
                'renderer' => \Superbazaar\General\Block\Adminhtml\Grid\Renderer\Nordercost::class,
                //'values' => 3,
                'name' => 'new_order_cost_price',
                'inline_css' => 'remain_qty',
                'validate_class' => 'validate-number',
                'index' => 'new_order_cost_price'
            ]
        );



        $this->addColumn(
            'in_products',
            [
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'index' => 'entity_id',
                'sortable' => false,
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );

        $this->addColumn(
            'qty',
            [
                'filter' => false,
                'sortable' => false,
                'header' => __('Quantity'),
                'renderer' => \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Qty::class,
                'name' => 'qty',
                'inline_css' => 'qty',
                'type' => 'input',
                'validate_class' => 'validate-number',
                'index' => 'qty'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
    * Get grid url
    *
    * @return string
    */
    
}
