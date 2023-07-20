<?php
/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

/**
* Renderer for Qty field in sales create new order search grid
*
* @author     Magento Core Team <core@magentocommerce.com>
*/
class Nordercost extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    protected function _isInactive($row)
    {
        return $this->typeConfig->isProductSet($row->getTypeId());
    }

    /**
    * Render product qty field
    *
    * @param \Magento\Framework\DataObject $row
    * @return string
    */
    public function render(\Magento\Framework\DataObject $row)
    {
        $id = $row->getData('entity_id');
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        

        $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($id);
        if($product->getNewOrderCostPrice()){
            return $product->getNewOrderCostPrice();

        }else{
            return "--";
        }

    }
}