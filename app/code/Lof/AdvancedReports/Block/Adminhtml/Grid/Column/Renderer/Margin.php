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
class Margin extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $costAmount = 0;
        $costAmount1 = 0;
        $order = $objectManager->get('\Magento\Sales\Api\Data\OrderInterfaceFactory')->create()->loadByIncrementId($OrderId);
        $costAmount = 0;
        foreach ($order->getAllVisibleItems() as $item) {
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
            $previousOrdercost = $product->getPreviousOrderCostPrice();
            if($previousOrdercost && $previousOrdercost !=null){
                $cost = $item->getBaseCost() ;
                if($cost == "0" ){
                    $item->setBaseCost($previousOrdercost)->save();
                    $costa = $item->getBaseCost();
                    $costAmount = ($item->getPrice() * $item->getQtyOrdered()) - ($costa * $item->getQtyOrdered());
                    //$costAmount = $costa * $item->getQtyOrdered();
                    $costAmount1 +=$costAmount; 
                }else{
                    $costa = $cost;
                    //$costAmount = $costa * $item->getQtyOrdered();
                    $costAmount = ($item->getPrice() * $item->getQtyOrdered()) - ($costa * $item->getQtyOrdered());
                    $costAmount1 +=$costAmount;
                }
            }
        }   
        if($row->getData('total_invoiced_amount')== "0"){
            $totalInvoiceAmount = $row->getData('total_subtotal_amount');

        }else{
            $totalInvoiceAmount = $row->getData('total_invoiced_amount');

        }
       
        //$profit = $totalInvoiceAmount - $costAmount1; 
         if($OrderId == "ML-000011955"){
            #echo $costAmount1;exit;
        }
        $percent = $row->getData($this->getColumn()->getIndex());
      
        $gross = $totalInvoiceAmount-$costAmount;
        if($totalInvoiceAmount > 0 ){
            $margin = ($costAmount1 * 100) /$row->getData('total_subtotal_amount');
            $marginPercentage= $margin;
            //$marginFinal = $priceHelper->currency($marginPercentage, true, false);
            return number_format($marginPercentage).'%';

        }else{
            return "0.00";
        }


        /*$max_width = 50;
        $margin_width = ($marginPercentage/100)*(int)$max_width;
        if($percent == null){
        return '<span class="report-profit-margin-box" style="width: '.$margin_width.'px"></span>';
        }else{
        return '<span class="report-profit-margin-box" style="width: '.$margin_width.'px"></span>'.$marginFinal.'%';
        }*/

    }
}
