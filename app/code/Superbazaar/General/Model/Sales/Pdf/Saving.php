<?php
namespace Superbazaar\General\Model\Sales\Pdf;

class Saving extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    public function getTotalsForDisplay()
    {
        $order = $this->getOrder();
        $orderItems = $order->getAllItems();
        $totalDiscountprice =0;
        foreach ($order->getAllItems() as $_item) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($_item->getProductId());
            $specialprice = $product->getSpecialPrice();
            $specialPriceFromDate = $product->getSpecialFromDate();
            $specialPriceToDate = $product->getSpecialToDate();
            $today =  time();
            if($specialprice && ($product->getPrice()>$_item->getPrice())){
                $mainprice = $_item->getPrice();
                $discountPrice = $product->getPrice()-$mainprice; 
                $discountPriceCal=  $discountPrice*$_item->getQtyOrdered();
                $totalDiscountprice+= $discountPriceCal+$_item->getDiscountAmount();
                //$discountPriceCalwithotherdiscount = $totalDiscountprice+$_item->getDiscountAmount();

                //$totalDiscountprice= $this->helper('Magento\Framework\Pricing\Helper\Data')->currency($discountPriceCal,true,false);
            }else{
                $totalDiscountprice+= $_item->getDiscountAmount();
            }
            #echo $totalDiscountprice;exit;
        }
        $amount = $objectManager->get('Magento\Framework\Pricing\Helper\Data')->currency($totalDiscountprice,true,false);

        $title = __($this->getTitle());
        if ($this->getTitleSourceField()) {
            $label = $title . ' (' . $this->getTitleDescription() . '):';
        } else {
            $label = $title . ':';
        }

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => $amount, 'label' => $label, 'font_size' => $fontSize];
        return [$total];
    }
}