<?php
/**
* Landofcoder
* 
* NOTICE OF LICENSE
* 
* This source file is subject to the Landofcoder.com license that is
* available through the world-wide-web at this URL:
* http://landofcoder.com/license
* 
* DISCLAIMER
* 
* Do not edit or add to this file if you wish to upgrade this extension to newer
* version in the future.
* 
* @category   Landofcoder
* @package    Lof_AdvancedReports
* @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
* @license    http://www.landofcoder.com/LICENSE-1.0.html
*/
namespace Lof\AdvancedReports\Block\Adminhtml\Grid\Column\Renderer; 

use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
* Adminhtml grid item renderer date
*/
class Cost extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
    * Renders grid column
    *
    * @param   Varien_Object $row
    * @return  string
    */
    public function render(\Magento\Framework\DataObject $row)
    {
        $OrderId = $row->getData('increment_id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        $order = $objectManager->create('\Magento\Sales\Api\Data\OrderInterfaceFactory')->create()->loadByIncrementId($OrderId);
        $costAmount = 0;
        $costAmount1 = 0;
        $asdd= array();

            foreach ($order->getAllVisibleItems() as $item) {
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                $previousOrdercost = $product->getPreviousOrderCostPrice();
                if($previousOrdercost && $previousOrdercost !=null){
                    $cost = $item->getBaseCost() ;
                    if($cost == "0" || $cost == null){
                        $item->setBaseCost($previousOrdercost)->save();
                        $costa = $item->getBaseCost();
                        $costAmount = number_format($costa * $item->getQtyOrdered());
                        $costAmount1 +=$costAmount; 
                    }else{
                        $costa = $cost;
                        $costAmount = number_format($costa * $item->getQtyOrdered());
                        $costAmount1 +=$costAmount;
                    }
                }

            }   

        /* if($OrderId="ML-000002502"){
        echo $costAmount;exit;

        }*/
        //return print_r($asdd);
        return $priceHelper->currency($costAmount1, true, false);;


    }
}
