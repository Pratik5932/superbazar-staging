<?php
namespace Superbazaar\PostcodeWisePrice\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Webkul\MpHyperLocal\Helper\Data as HelperData;

class CustomPrice implements ObserverInterface
{
    public function __construct(
        HelperData $helperData
    ) 
    {
        $this->helperData = $helperData;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {

        $addressData = $this->helperData->getSavedAddress();

        $item = $observer->getEvent()->getData('quote_item');

        if(!$item->getParentItem()){            
            $item = ( $item->getParentItem() ? $item->getParentItem() : $item );

            $product= $item->getProduct();
            $product->load($product->getId());


            $orgprice = $product->getPrice();
            $specialprice = $product->getSpecialPrice();
            $specialfromdate = $product->getSpecialFromDate();
            $specialtodate = $product->getSpecialToDate();
            $today = time();
            $specialPriceflag = false;
            if (!$specialprice)
                $specialprice = $orgprice;
            if ($specialprice< $orgprice) {
                if ((is_null($specialfromdate) &&is_null($specialtodate)) || ($today >= strtotime($specialfromdate) &&is_null($specialtodate)) || ($today <= strtotime($specialtodate) &&is_null($specialfromdate)) || ($today >= strtotime($specialfromdate) && $today <= strtotime($specialtodate))) {
                    $specialprice = $specialprice;
                    $specialPriceflag =true;
                }
            }
            
           # echo $specialprice;exit;

            $postcodeProdctPrice = $product->getPostcodeProdctPrice();
            if($addressData && is_array($addressData) && isset($addressData['zipcode']) && $postcodeProdctPrice){

                $postcodeProdctPriceArray = json_decode($postcodeProdctPrice,true);

                if($postcodeProdctPriceArray && is_array($postcodeProdctPriceArray) && !empty($postcodeProdctPriceArray)){

                    $zipCode = $addressData['zipcode'];
                    $zipCodePrice = null;
                    foreach($postcodeProdctPriceArray as $value){

                        $postCodesValueArray = [];

                        if(isset($value['postcode']) && $value['postcode']){
                            $postCodesValueArray = array_map('trim', explode(',', $value['postcode']));
                        }

                        if($value && in_array($zipCode, $postCodesValueArray) && isset($value['price'])){
                            $zipCodePrice = $value['price'];
                        }
                    }

                    if($zipCodePrice != null && !$specialPriceflag){

                        $item->setCustomPrice($zipCodePrice);
                        $item->setOriginalCustomPrice($zipCodePrice);
                        $item->getProduct()->setIsSuperMode(true);
                    }else{
                        $item->setCustomPrice($specialprice);
                        $item->setOriginalCustomPrice($specialprice);
                        $item->getProduct()->setIsSuperMode(true);
                    }
                }
            }

        }

    }

}