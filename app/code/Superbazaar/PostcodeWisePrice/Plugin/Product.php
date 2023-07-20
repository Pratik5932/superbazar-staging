<?php
namespace Superbazaar\PostcodeWisePrice\Plugin;

use Webkul\MpHyperLocal\Helper\Data as HelperData;

class Product
{
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        HelperData $helperData
    ) {
        $this->_productRepository = $productRepository;
        $this->helperData = $helperData;
    }

    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result)
    {

        $addressData = $this->helperData->getSavedAddress();

        $postcodeProdctPrice = $subject->getPostcodeProdctPrice();

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
                    
                    if($value && is_array($value) && in_array($zipCode, $postCodesValueArray) && isset($value['price'])){
                        $zipCodePrice = $value['price'];
                    }
                }

                if($zipCodePrice != null){
                    $result = $zipCodePrice;;
                }
            }
        }

        return $result;
    }
}