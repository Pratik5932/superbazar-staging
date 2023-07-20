<?php
/**
* Webkul Software.
*
* @category  Webkul
* @package   Webkul_MobikulMpHyperLocal
* @author    Webkul
* @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html
*/

namespace Webkul\MobikulMpHyperLocal\Controller\Catalog;

class CompareList extends \Webkul\MobikulApi\Controller\Catalog\CompareList  {

    public function execute()
    {
        try {
            $this->verifyRequest();
            $currency = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
            $cacheString = "COMPARELIST".$this->width.$this->storeId.$this->customerToken;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // Setting currency /////////////////////////////////////////////////////
            $this->store->setCurrentCurrencyCode($currency);
            // Checking is swatch allowed on colletion page /////////////////////////
            $this->returnArray["showSwatchOnCollection"] = (bool)$this->helper->getConfigData("catalog/frontend/show_swatches_in_product_list");
            // Getting compare list data ////////////////////////////////////////////
            if ($this->items === null) {
                $this->compare->setAllowUsedFlat(false);
                $this->items = $this->compareItemCollectionFactory->create();
                $this->items->useProductItem(true)->setStoreId($this->storeId);
                if ($this->customerId != 0) {
                    $this->items->setCustomerId($this->customerId);
                } else {
                    $this->items->setVisitorId($this->customerVisitor->getId());
                }
                $attributes = $this->catalogConfig->getProductAttributes();
                $this->items
                    ->addAttributeToSelect($attributes)
                    ->loadComparableAttributes()
                    ->setVisibility($this->productVisibility->getVisibleInSiteIds());
            }
            // Getting product list /////////////////////////////////////////////////
            $productList = [];
            foreach ($this->items as $eachProduct) {
                if (in_array($eachProduct->getId(), $this->getAllowedProductIds())) {
                    $product = $this->helperCatalog->getOneProductRelevantData($eachProduct, $this->storeId, $this->width, $this->customerId);
                    $productList[] = $product;                    
                }
            }
            $this->returnArray["productList"] = $productList;
            // Getting attribute value list /////////////////////////////////////////
            $block = $this->compareListBlock;
            $attributeValueList = [];
            foreach ($this->items->getComparableAttributes() as $attribute) {
                $eachRow = [];
                $eachRow["attributeName"] = $this->escaper->escapeHtml($attribute->getStoreLabel() ? $attribute->getStoreLabel() : __($attribute->getFrontendLabel()));
                foreach ($this->items as $item) {
                    $eachItem = "";
                    switch ($attribute->getAttributeCode()) {
                        case "price":
                            $eachItem = $this->helperCatalog->stripTags($this->pricingHelper->currency($item->getFinalPrice()));
                            break;
                        case "small_image":
                            $eachItem = $block->getImage($item, "product_small_image")->toHtml();
                            break;
                        default:
                            $attributeHtml = (string) $block->getProductAttributeValue(
                                $item,
                                $attribute
                            );
                            $value = (gettype($attributeHtml) == "string") ? $attributeHtml : "";
                            $eachItem = $this->catalogHelperOutput->productAttribute(
                                $item,
                                $value,
                                $attribute->getAttributeCode()
                            );
                            break;

                    }
                    $eachRow["value"][] = $eachItem;
                }
                $attributeValueList[] = $eachRow;
            }
            $this->returnArray["attributeValueList"] = $attributeValueList;
            $this->customerSession->setCustomerId(null);
            $this->checkNGenerateEtag($cacheString);
            $this->returnArray["success"] = true;
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function getAllowedProductIds() {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $hyperLocalhelper = $this->objectManager->create("Webkul\MpHyperLocal\Helper\Data");
        $sellerIds = $hyperLocalhelper->getNearestSellers();
        return $hyperLocalhelper->getNearestProducts($sellerIds);
    }

}