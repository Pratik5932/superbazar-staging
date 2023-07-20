<?php
/**
* Webkul Software.
*
*
*
* @category  Webkul
* @package   Webkul_MobikulCore
* @author    Webkul <support@webkul.com>
* @copyright Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html ASL Licence
* @link      https://store.webkul.com/license.html
*/

namespace Webkul\MobikulCore\Model\ConfigurableProduct;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;

class ConfigurableAttributeData extends \Magento\ConfigurableProduct\Model\ConfigurableAttributeData
{

    public function getAttributesData(Product $product, array $options = [])
    {
        $attributes    = [];
        $defaultValues = [];
        foreach ($product->getTypeInstance()->getConfigurableAttributes($product) as $attribute) {
            $attributeOptionsData = $this->getAttributeOptionsData($attribute, $options);
            $attributesNewOptions    = [];
            foreach($attributeOptionsData as $newData){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $formatePrice = $objectManager->get("Webkul\MobikulCore\Helper\Data");
                if(isset($newData['products'][0])){
                    $productCollection = $objectManager->create('Magento\Catalog\Model\Product')->load($newData['products'][0]);
                    $productPriceById = $productCollection->getPrice();
                  #  mail("er.bharatmali@gmail.com","tets", $newData['products'][0]."->".$productPriceById);
                    $orgprice = $productCollection->getPrice();
                    $specialprice = $productCollection->getSpecialPrice();
                    $specialfromdate = $productCollection->getSpecialFromDate();
                    $specialtodate = $productCollection->getSpecialToDate();
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

                }
                
                if($specialprice - $product->getFinalPrice() == 0.00){
                    $newData['label'] = $newData['label'];

                }else{
                    $newData['label'] = $newData['label']." + ".$formatePrice->getCurrencyConvertedFormattedPrice($specialprice - $product->getFinalPrice() );


                }
                $newData['id'] = $newData['id'];
                $newData['products'][0] = $newData['products'][0];

                $attributesNewOptions[]=$newData;
            }
            #mail("er.bharatmali@gmail.com","tets", print_r( $attributesNewOptions, true ));

            if ($attributesNewOptions) {

                # mail("er.bharatmali@gmail.com","tets", print_r( $attributeOptionsData, true ));

                $swatchType       = "";
                $objectManager    = \Magento\Framework\App\ObjectManager::getInstance();
                $productAttribute = $attribute->getProductAttribute();
                if ($this->isJson($productAttribute->getAdditionalData())) {
                    $swatchInputType = $objectManager->create(
                        \Magento\Framework\Json\Helper\Data::class
                    )->jsonDecode($productAttribute->getAdditionalData());
                    if (isset($swatchInputType["swatch_input_type"]) && $swatchInputType["swatch_input_type"] != "") {
                        $swatchType  = $swatchInputType["swatch_input_type"];
                    }
                }
                $updateProductPreviewImage     = false;
                if ((bool)$productAttribute->getUpdateProductPreviewImage()) {
                    $updateProductPreviewImage = (bool)$productAttribute->getUpdateProductPreviewImage();
                }
                $attributeId                   = $productAttribute->getId();
                $attributes[$attributeId]      = [
                    "id"                        => $attributeId,
                    "code"                      => $productAttribute->getAttributeCode(),
                    "label"                     => $productAttribute->getStoreLabel($product->getStoreId()),
                    "options"                   => $attributesNewOptions,
                    "position"                  => $attribute->getPosition(),
                    "swatchType"                => $swatchType,
                    "updateProductPreviewImage" => $updateProductPreviewImage
                ];
                $defaultValues[$attributeId] = $this->getAttributeConfigValue($attributeId, $product);
            }
        }
        return [
            "attributes"    => $attributes,
            "defaultValues" => $defaultValues,
        ];
    }

    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
