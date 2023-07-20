<?php
/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Superbazaar\General\Block\Adminhtml\Grid\Renderer;

/**
* Renderer for Qty field in sales create new order search grid
*
* @author     Magento Core Team <core@magentocommerce.com>
*/
class Pordercost extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{

    /**
    * Returns whether this qty field must be inactive
    *
    * @param \Magento\Framework\DataObject $row
    * @return bool
    */
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

        $product = $objectManager->get('\Magento\Catalog\Model\Product')->load($id);
        if($product->getPreviousOrderCostPrice()){
            return $product->getPreviousOrderCostPrice();

        }else{
            return "--";
        }

    }
}