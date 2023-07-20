<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMp
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulMp\Controller\Product;

/**
 * Class NewFormData for Creating vendor Product
 *
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class NewFormData extends AbstractProduct
{

    /**
     * Execute function for class NewFormData
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "NEWFORMDATA".$this->storeId.$this->productId.$this->customerToken.$this->sellerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customerSession->setCustomerId($this->sellerId);
            if (!$this->marketplaceHelper->isSeller()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("invalid seller")
                );
            }
            $this->returnArray["productData"] = new \Magento\Framework\DataObject();
            $this->returnArray["inventoryAvailabilityOptions"] = [
                ["value"=>1, "label"=>__("In Stock")],
                ["value"=>0, "label"=>__("Out of Stock")]
            ];
            if ($this->productId) {
                $product = $this->marketplaceHelper->getSellerProductDataByProductId($this->productId);
                if ($product->getsize() && $product->setPageSize(1)->getFirstItem()->getSellerId() != $this->sellerId) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("invalid seller")
                    );
                }
            }
            $attibuteSets = [];
            if (count($this->marketplaceHelper->getAllowedSets()) > 1) {
                foreach ($this->marketplaceHelper->getAllowedSets() as $set) {
                    $attibuteSets[] = $set;
                }
                $this->returnArray["allowedAttributes"] = $attibuteSets;
            } else {
                $allowedSets = $this->marketplaceHelper->getAllowedSets();
                $this->returnArray["allowedAttributes"] = $allowedSets;
            }
            if (!$this->productId) {
                foreach ($this->product->create()->getMediaAttributes() as $attribute) {
                    $code = $attribute->getAttributeCode();
                    if ($attribute->getAttributeCode() == "small_image") {
                        $code = "smallImage";
                    } elseif ($attribute->getAttributeCode() == "image") {
                        $code = "baseImage";
                    } elseif ($attribute->getAttributeCode() == "swatch_image") {
                        $code = "swatchImage";
                    }
                    $this->returnArray["imageRole"][] = [
                        "id" => 0, "value" => $code, "label" => $attribute->getFrontendLabel()
                    ];
                }
            }
            $allowedTypes = [];
            if (count($this->marketplaceHelper->getAllowedProductTypes()) > 1) {
                foreach ($this->marketplaceHelper->getAllowedProductTypes() as $type) {
                    if ($type["value"] == "simple" || $type["value"] == "virtual" || $type["value"] == "downloadable") {
                        $allowedTypes[] = $type;
                    }
                }
                $this->returnArray["allowedTypes"] = $allowedTypes;
            } else {
                $allowedProducts = $this->marketplaceHelper->getAllowedProductTypes();
                if ($allowedProducts[0]["value"] == "simple" || $allowedProducts[0]["value"] == "virtual") {
                    $this->returnArray["allowedTypes"] = $allowedProducts;
                }
            }
            $this->returnArray["isCategoryTreeAllowed"] = (bool)$this->marketplaceHelper->getIsAdminViewCategoryTree();
            $this->returnArray["categories"] = new \Magento\Framework\DataObject();
            $this->returnArray["assignedCategories"] = [];
            if ($this->mpMobikulHelper->getAllowedCategoryIds($this->sellerId) &&
                !$this->returnArray["isCategoryTreeAllowed"]
            ) {
                $this->returnArray["assignedCategories"] = [];
                $categoryIds = explode(",", trim($this->mpMobikulHelper->getAllowedCategoryIds($this->sellerId)));
                foreach ($categoryIds as $categoryId) {
                    $category = $this->category->setStoreId($this->storeId)->load($categoryId);
                    if ($category->getId()) {
                        $eachCategory                = [];
                        $eachCategory["id"]          = $category->getId();
                        $eachCategory["name"]        = $category->getName();
                        $this->returnArray["assignedCategories"][] = $eachCategory;
                    }
                }
            } else {
                $rootNode = $this->categoryTree->getRootNode();
                $this->returnArray["categories"] = $this->categoryTree->getTree(
                    $rootNode,
                    null,
                    $this->storeId
                )->__toArray();
            }
            if (
                count((array)$this->returnArray["categories"]) == 0 &&
                count($this->returnArray["assignedCategories"]) == 0
            ) {
                $rootNode = $this->categoryTree->getRootNode();
                $this->returnArray["categories"] = $this->categoryTree->getTree(
                    $rootNode,
                    null,
                    $this->storeId
                )->__toArray();
            }
            $this->getDefaultConfigData();
            $productVisibility = $this->marketplaceHelper->getVisibilityOptionArray();
            foreach ($productVisibility as $key => $value) {
                $this->returnArray["visibilityOptions"][] = ["value"=>$key, "label"=>$value];
            }
            $this->returnArray["taxHint"] = $this->marketplaceHelper->getProductHintTax();
            $taxes = $this->marketplaceHelper->getTaxClassModel();
            foreach ($taxes as $tax) {
                $this->returnArray["taxOptions"][] = ["value"=>$tax->getId(), "label"=>$tax->getClassName()];
            }
            if ($this->marketplaceHelper->getProductHintStatus()) {
                $this->returnArray["weightHint"] = $this->marketplaceHelper->getProductHintWeight() ?? '';
            } else {
                $this->returnArray["weightHint"] = '';
            }
            $this->returnArray['weightUnit'] = $this->marketplaceHelper->getWeightUnit();
            if ($this->productId) {
                $this->getProductDataFromProductId();
            }
            $this->returnArray["success"]    = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            $this->checkNGenerateEtag($cacheString);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag          = $this->wholeData["eTag"]          ?? '';
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->productId     = $this->wholeData["productId"]     ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->sellerId      = $this->helper->getCustomerByToken($this->customerToken)?? 0;
            if (!$this->sellerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            } elseif ($this->sellerId != 0) {
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function to get default configuration data
     *
     * @return void
     */
    public function getDefaultConfigData()
    {
        $this->returnArray["skuType"] = $this->marketplaceHelper->getSkuType();
        $this->returnArray["skuhint"] = $this->marketplaceHelper->getProductHintSku();
        $this->returnArray["showHint"] = (bool)$this->marketplaceHelper->getProductHintStatus();
        $this->returnArray["skuPrefix"] = $this->marketplaceHelper->getSkuPrefix();
        $this->returnArray["priceHint"] = $this->marketplaceHelper->getProductHintPrice();
        $this->returnArray["imageHint"] = $this->marketplaceHelper->getProductHintImage();
        $this->returnArray["weightUnit"] = $this->marketplaceHelper->getWeightUnit();
        $this->returnArray["productHint"] = $this->marketplaceHelper->getProductHintName();
        $this->returnArray["categoryHint"] = $this->marketplaceHelper->getProductHintCategory();
        $this->returnArray["inventoryHint"] = $this->marketplaceHelper->getProductHintQty();
        $this->returnArray["currencySymbol"] = $this->marketplaceHelper->getCurrencySymbol();
        $this->returnArray["descriptionHint"] = $this->marketplaceHelper->getProductHintDesc();
        $this->returnArray["specialPriceHint"] = $this->marketplaceHelper->getProductHintSpecialPrice();
        $this->returnArray["specialEndDateHint"] = $this->marketplaceHelper->getProductHintEndDate();
        $this->returnArray["shortdescriptionHint"] = $this->marketplaceHelper->getProductHintShortDesc();
        $this->returnArray["specialStartDateHint"] = $this->marketplaceHelper->getProductHintStartDate();
        $this->returnArray["addProductLimitStatus"] = (bool)$this->marketplaceHelper->getAllowProductLimit();
        $this->returnArray["addUpsellProductStatus"] = (bool)$this->mpMobikulHelper->addUpsellProductStatus();
        $this->returnArray["addRelatedProductStatus"] = (bool)$this->mpMobikulHelper->addRelatedProductStatus();
        $this->returnArray["addCrosssellProductStatus"] = (bool)$this->mpMobikulHelper->addCrosssellProductStatus();
        $this->returnArray["inventoryAvailabilityHint"] = $this->marketplaceHelper->getProductHintStock();
    }

    /**
     * Function to get product Data form product id
     *
     * @return void
     */
    public function getProductDataFromProductId()
    {
        $product = $this->productBuilder->build(
            ["id" => $this->productId],
            $this->marketplaceHelper->getCurrentStoreId()
        );
        $product = $this->product->create()->load($this->productId);
        $imageRoleArray = [];
        foreach ($product->getMediaGalleryEntries() as $entry) {
            foreach ($entry->getTypes() as $role) {
                $imageRoleArray[$role] = $entry->getId();
            }
        }
        foreach ($product->getMediaAttributes() as $attribute) {
            $entryId = $imageRoleArray[$attribute->getAttributeCode()] ?? 0;
            $code = $attribute->getAttributeCode();
            if ($attribute->getAttributeCode() == "small_image") {
                $code = "smallImage";
            } elseif ($attribute->getAttributeCode() == "image") {
                $code = "baseImage";
            } elseif ($attribute->getAttributeCode() == "swatch_image") {
                $code = "swatchImage";
            }
            $this->returnArray["imageRole"][] = [
                "id" => $entryId, "value"=>$code, "label"=>$attribute->getFrontendLabel()
            ];
        }
        $productData = $product->getData();
        if (empty($productData["entity_id"])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Product does not exists.")
            );
        }

        $this->returnArray["productData"] = json_decode(json_encode($this->returnArray["productData"]), true);
        $this->returnArray["productData"]["type"]             = $productData["type_id"];
        $this->returnArray["productData"]["websiteIds"][]     = $product->getStore()->getWebsiteId();
        $this->returnArray["productData"]["attributeSetId"]   = $productData["attribute_set_id"];
        $this->returnArray["productData"]["categoryIds"]      = $productData["category_ids"];
        $this->returnArray["productData"]["name"]             = $productData["name"];
        $this->returnArray["productData"]["description"]      = $productData["description"] ?? "";
        $this->returnArray["productData"]["shortDescription"] = $productData["short_description"] ?? "";
        $this->returnArray["productData"]["sku"]              = $productData["sku"];
        $this->returnArray["productData"]["price"]            = $productData["price"];
        $this->returnArray["productData"]["specialPrice"]     = $productData["special_price"] ?? "";
        $this->returnArray["productData"]["specialFromDate"]  = $productData["special_from_date"] ?? "";
        $this->returnArray["productData"]["specialToDate"]    = $productData["special_to_date"] ?? "";
        $this->returnArray["productData"]["qty"]              = $productData["quantity_and_stock_status"]["qty"];
        $this->returnArray["productData"]["isInStock"]       = $productData["quantity_and_stock_status"]["is_in_stock"];
        $this->returnArray["productData"]["visibility"]       = $productData["visibility"] ?? "";
        $this->returnArray["productData"]["taxClassId"]       = $productData["tax_class_id"];
        $this->returnArray["productData"]["productHasWeight"] = 0;
        $this->returnArray["productData"]["status"]            = $productData["status"] ?? "0";
        $this->returnArray["productData"]["isFeaturedProduct"] = $productData["as_featured"] ?? "0";
        if ($productData["type_id"] =="simple" ||
            ($productData["type_id"]=="configurable" && !empty($productData["weight"]))
        ) {
            $this->returnArray["productData"]["productHasWeight"] = 1;
            $this->returnArray["productData"]["weight"] = $productData["weight"];
        }
        $this->returnArray["productData"]["metaTitle"] = "";
        if (!empty($productData["meta_title"])) {
            $this->returnArray["productData"]["metaTitle"] = $productData["meta_title"];
        }
        $this->returnArray["productData"]["metaKeyword"] = "";
        if (!empty($productData["meta_keyword"])) {
            $this->returnArray["productData"]["metaKeyword"] = $productData["meta_keyword"];
        }
        $this->returnArray["productData"]["metaDescription"] = "";
        if (!empty($productData["meta_description"])) {
            $this->returnArray["productData"]["metaDescription"] = $productData["meta_description"];
        }
        $this->returnArray["productData"]["mpProductCartLimit"] = null;
        if (!empty($productData["mp_product_cart_limit"])) {
            $this->returnArray["productData"]["mpProductCartLimit"] = $productData["mp_product_cart_limit"];
        }
        $this->returnArray["productData"]["baseImage"]    = $productData["image"] ?? "";
        $this->returnArray["productData"]["smallImage"]   = $productData["small_image"] ?? "";
        $this->returnArray["productData"]["swatchImage"]  = $productData["swatch_image"]?? "";
        $this->returnArray["productData"]["thumbnail"]    = $productData["thumbnail"] ?? "";
        $this->returnArray["productData"]["mediaGallery"] = [];
        if (!empty($productData["media_gallery"])) {
            foreach ($productData["media_gallery"]["images"] as &$galleryImage) {
                $galleryImage['url'] = $this->mediaConfig->getMediaUrl(
                    $galleryImage['file']
                );
                $galleryImage['id'] = $galleryImage['value_id'];
                array_push($this->returnArray["productData"]["mediaGallery"], $galleryImage);
            }
        }
        $this->returnArray["productData"]["related"] = $this->getRelatedProductsData($product, "related");
        $this->returnArray["productData"]["upsell"] = $this->getRelatedProductsData($product, "upsell");
        $this->returnArray["productData"]["crossSell"] = $this->getRelatedProductsData($product, "crosssell");
        if ($productData["type_id"] == "downloadable") {
            $this->returnArray["productData"]["linkData"]            = $this->convertArrayObjToArray(
                $this->linksBlock->getDownloadableLinkInfo()
            );
            $this->returnArray["productData"]["linksTitle"]          = $productData["links_title"] ?? "";
            $this->returnArray["productData"]["sampleData"]          = $this->convertArrayObjToArray(
                $this->samplesBlock->getDownloadableSampleInfo()
            );
            $this->returnArray["productData"]["samplesTitle"]        = $productData["samples_title"] ?? "";
            $this->returnArray["productData"]["purchasedSeparately"] = $productData["links_purchased_separately"];
        }
    }

    /**
     * Function to get related products data
     *
     * @param \Magento\Catalog\Model\Product $product product
     * @param string                         $type    product type
     *
     * @return array
     */
    public function getRelatedProductsData($product, $type)
    {
        $productIds = [];
        $relatedProductColl = $product->getProductLinks();
        foreach ($relatedProductColl as $key => $value) {
            if ($value["link_type"] == $type) {
                $productBySku = $this->productRepository->get($value["linked_product_sku"]);
                array_push($productIds, $productBySku->getId());
            }
        }
        return $productIds;
    }

    /**
     * Function to convert object to array
     *
     * @param object $arrayOfObjectData array of object data
     *
     * @return array
     */
    public function convertArrayObjToArray($arrayOfObjectData)
    {
        $finalData = [];
        $loop = 0;
        foreach ($arrayOfObjectData as $object) {
            $finalData[] = $object->getData();
            foreach ($object->getData() as $key => $value) {
                if ($key == "file_save") {
                        $finalData[$loop]["file_save"] = (!empty($value[0])) ? $value[0] : "{}";
                }
                if ($key == "sample_file_save") {
                        $finalData[$loop]["sample_file_save"] = (!empty($value[0])) ? $value[0] : "{}";
                }
            }
            $loop++;
        }
        return $finalData;
    }
}
