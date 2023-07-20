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
 * Class SaveProduct for saving vendor Product
 *
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class SaveProduct extends AbstractProduct
{
    /**
     * Execute function for class SaveProduct
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $this->customerSession->setCustomerId($this->sellerId);
            $environment  = $this->emulate->startEnvironmentEmulation($this->storeId);
            if (!$this->marketplaceHelper->isSeller()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("invalid seller")
                );
            }
            if ($this->productId) {
                $product = $this->marketplaceHelper->getSellerProductDataByProductId($this->productId);
                if ($product->getsize() && $product->setPageSize(1)->getFirstItem()->getSellerId() != $this->sellerId) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("invalid seller")
                    );
                }
            }
            $skuType = $this->marketplaceHelper->getSkuType();
            $skuPrefix = $this->marketplaceHelper->getSkuPrefix();
            if ($skuType == "dynamic" && $this->productId == '') {
                $sku = $skuPrefix.$this->name;
                $sku = $this->checkSkuExist($sku);
                $this->sku = $sku;
            } else {
                $this->checkSkuExists($this->sku);
            }
            if ($this->productId) {
                $wholeProductData["id"] = $this->productId;
                $wholeProductData["product_id"] = $this->productId;
                $wholeProductData["status"] = $this->status;
            }
            if (!$this->marketplaceHelper->getAllowProductLimit()) {
                $mpProductCartLimit = $this->marketplaceHelper->getGlobalProductLimitQty();
            }
            if ($this->specialFromDate) {
                $this->specialFromDate = date("m/d/Y", strtotime($this->specialFromDate));
            }
            if ($this->specialToDate) {
                $this->specialToDate = date("m/d/Y", strtotime($this->specialToDate));
            }
            $wholeProductData["type"] = $this->type;
            $wholeProductData["set"] = $this->attributeSet;
            $wholeProductData["product"] = [
                "website_ids"       => $this->websiteIds,
                "category_ids"      => $this->categoryIds,
                "name"              => $this->name,
                "description"       => $this->descriprion,
                "short_description" => $this->shortDescription,
                "sku"               => $this->sku,
                "price"             => $this->price,
                "special_price"     => $this->specialPrice,
                "special_from_date" => $this->specialFromDate,
                "special_to_date"   => $this->specialToDate,
                "stock_data"        =>
                    [
                        "manage_stock"            => 1,
                        "use_config_manage_stock" => 1
                    ],
                "quantity_and_stock_status" =>
                    [
                        "qty"         => $this->qty,
                        "is_in_stock" => $this->isInStock
                    ],
                "visibility"            => $this->visibility,
                "tax_class_id"          => $this->taxClassId,
                "product_has_weight"    => $this->productHasWeight,
                "meta_title"            => $this->metaTitle,
                "meta_keyword"          => $this->metaKeyword,
                "meta_description"      => $this->metaDescription,
                "as_featured"   => $this->isFeaturedProduct,
                "mp_product_cart_limit" => $this->mpProductCartLimit
            ];

            //manage downloadable product data
            if ($this->type == "downloadable") {
                $wholeProductData["product"]["links_title"] = $this->linksTitle;
                $wholeProductData["product"]["links_purchased_separately"] = $this->purchasedSeparately;
                $wholeProductData["product"]["samples_title"] = $this->samplesTitle;
                $wholeProductData["is_downloadable"] = $this->isDownloadable;
                $wholeProductData["downloadable"]["link"] = $this->linksData;
                $wholeProductData["downloadable"]["sample"] = $this->samplesData;
            }

            // manage custom attribute data
            if (count($this->customAttribute)) {
                foreach ($this->customAttribute as $key => $value) {
                    $wholeProductData["product"][$key] = $value;
                }
            }
            //manage custom option data
            if (!empty($this->customOptionData)) {
                $customOption = $this->getCustomOptions();
                $wholeProductData["product"]["options"] = $customOption;
                $wholeProductData["affect_product_custom_options"] = 1;
            }
            if ($wholeProductData["product"]["product_has_weight"]) {
                $wholeProductData["product"]["weight"] = $this->weight;
            }
            if ($this->upsell) {
                $upsellData = [];
                foreach ($this->upsell as $key => $recordId) {
                    $upsellData[]["id"] = $recordId;
                }
                $wholeProductData["links"]["upsell"] = $upsellData;
            }
            if ($this->crossSell) {
                $crossSellData = [];
                foreach ($this->crossSell as $key => $recordId) {
                    $crossSellData[]["id"] = $recordId;
                }
                $wholeProductData["links"]["crosssell"] = $crossSellData;
            }
            
            if ($this->related) {
                $relatedData = [];
                foreach ($this->related as $key => $recordId) {
                    $relatedData[]["id"] = $recordId;
                }
                $wholeProductData["links"]["related"] = $relatedData;
            }

            if (isset($wholeProductData["links"])) {
                $this->getRequest()->setParam("links", $wholeProductData["links"]);
            }
            if (!empty($this->mediaGallery)) {
                foreach ($this->mediaGallery as $key => $gallery) {
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["position"] = $gallery["position"];
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["media_type"] = $gallery[
                        "media_type"
                    ] ?? "";
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["video_provider"] = $gallery[
                        "video_provider"
                    ] ?? "";
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["file"] = $gallery["file"] ?? "";
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["value_id"] = $gallery[
                        "value_id"
                    ] ?? "";
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["label"] = $gallery["label"] ?? "";
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["disabled"] = $gallery[
                        "disabled"
                    ] ?? "";
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["removed"] = $gallery[
                        "removed"
                    ] ?? "";
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["video_url"] = $gallery[
                        "video_url"
                    ] ?? "";
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["video_title"] = $gallery[
                        "video_title"
                    ] ?? "";
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["video_description"] = $gallery[
                        "video_description"
                    ] ?? "";
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["video_metadata"] = $gallery[
                        "video_metadata"
                    ] ?? "";
                    $wholeProductData["product"]["media_gallery"]["images"][$key]["role"] = $gallery["role"] ?? "";
                }
                $wholeProductData["product"]["image"]        = $this->baseImage;
                $wholeProductData["product"]["small_image"]  = $this->smallImage;
                $wholeProductData["product"]["swatch_image"] = $this->swatchImage;
                $wholeProductData["product"]["thumbnail"]    = $this->thumbnail;
            }
            list($errors) = $this->validatePost($wholeProductData);
            if (empty($errors)) {
                $returnArr = $this->saveProduct->saveProductData(
                    $this->sellerId,
                    $wholeProductData
                );
                $this->productId = $returnArr["product_id"];
                $this->returnArray["success"]    = true;
                $this->returnArray["message"] = __("Your product has been successfully saved");
            } else {
                $this->returnArray["message"] = $errors;
            }
            $this->returnArray["productId"] = $this->productId;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function getCustomOptions()
    {
        $customOption = [];
        foreach ($this->customOptionData as $key => $option) {
            $optionValues = [];
            if (!empty($option["values"])) {
                foreach ($option["values"] as $valueKey => $value) {
                    $valueKey = (
                        $this->productId && !empty($value["option_type_id"])
                    ) ? $value["option_type_id"] : $valueKey;
                    if (empty($value["option_type_id"])) {
                        unset($value["option_type_id"]);
                    }
                    $optionValues[$valueKey] = $value;
                }
            }
            unset($option["values"]);
            $option["values"] = $optionValues;
            $key = ($this->productId && !empty($option["option_id"])) ? $option["option_id"] : $key;
            $customOption[$key] = $option;
        }
        return $customOption;
    }

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->sku                 = $this->wholeData["sku"]                ?? "";
            $this->qty                 = $this->wholeData["qty"]                ?? 0;
            $this->type                = $this->wholeData["type"]               ?? "";
            $this->name                = $this->wholeData["name"]               ?? "";
            $this->price               = $this->wholeData["price"]              ?? 0;
            $this->status              = $this->wholeData["status"]             ?? 1;
            $this->isFeaturedProduct   = $this->wholeData["isFeaturedProduct"]  ?? 0;
            $this->weight              = $this->wholeData["weight"]             ?? null;
            $this->upsell              = $this->wholeData["upsell"]             ?? "{}";
            $this->storeId             = $this->wholeData["storeId"]            ?? 0;
            $this->related             = $this->wholeData["related"]            ?? "{}";
            $this->productId           = $this->wholeData["productId"]          ?? "";
            $this->linksData           = $this->wholeData["linksData"]          ?? "{}";
            $this->crossSell           = $this->wholeData["crossSell"]          ?? "{}";
            $this->metaTitle           = $this->wholeData["metaTitle"]          ?? "";
            $this->thumbnail           = $this->wholeData["thumbnail"]          ?? null;
            $this->baseImage           = $this->wholeData["baseImage"]          ?? null;
            $this->isInStock           = $this->wholeData["isInStock"]          ?? 1;
            $this->linksTitle          = $this->wholeData["linksTitle"]         ?? "";
            $this->visibility          = $this->wholeData["visibility"]         ?? null;
            $this->taxClassId          = $this->wholeData["taxClassId"]         ?? null;
            $this->smallImage          = $this->wholeData["smallImage"]         ?? null;
            $this->websiteIds          = $this->wholeData["websiteIds"]         ?? "{}";
            $this->categoryIds         = $this->wholeData["categoryIds"]        ?? "{}";
            $this->descriprion         = $this->wholeData["description"]        ?? "";
            $this->swatchImage         = $this->wholeData["swatchImage"]        ?? null;
            $this->metaKeyword         = $this->wholeData["metaKeyword"]        ?? "";
            $this->samplesData         = $this->wholeData["samplesData"]        ?? "{}";
            $this->samplesTitle        = $this->wholeData["samplesTitle"]       ?? "";
            $this->specialPrice        = $this->wholeData["specialPrice"]       ?? null;
            $this->mediaGallery        = $this->wholeData["mediaGallery"]       ?? "{}";
            $this->specialToDate       = $this->wholeData["specialToDate"]      ?? null;
            $this->customerToken       = $this->wholeData["customerToken"]      ?? "";
            $this->isDownloadable      = $this->wholeData["isDownloadable"]     ?? "off";
            $this->attributeSet        = $this->wholeData["attributeSetId"]     ?? 4;
            $this->specialFromDate     = $this->wholeData["specialFromDate"]    ?? null;
            $this->metaDescription     = $this->wholeData["metaDescription"]    ?? "";
            $this->customAttribute     = $this->wholeData["customAttribute"]    ?? "{}";
            $this->shortDescription    = $this->wholeData["shortDescription"]   ?? "";
            $this->productHasWeight    = $this->wholeData["productHasWeight"]   ?? 0;
            $this->customOptionData    = $this->wholeData["customOptionData"]   ?? "[]";
            $this->mpProductCartLimit  = $this->wholeData["mpProductCartLimit"] ?? null;
            $this->purchasedSeparately = $this->wholeData["purchasedSeparately"]?? 0;
            $this->sellerId            = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->sellerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            } elseif ($this->sellerId != 0) {
                $this->customerSession->setCustomerId($this->sellerId);
                $this->upsell           = $this->jsonHelper->jsonDecode($this->upsell);
                $this->related          = $this->jsonHelper->jsonDecode($this->related);
                $this->linksData        = $this->jsonHelper->jsonDecode($this->linksData);
                $this->crossSell        = $this->jsonHelper->jsonDecode($this->crossSell);
                $this->websiteIds       = $this->jsonHelper->jsonDecode($this->websiteIds);
                $this->samplesData      = $this->jsonHelper->jsonDecode($this->samplesData);
                $this->categoryIds      = $this->jsonHelper->jsonDecode($this->categoryIds);
                $this->mediaGallery     = $this->jsonHelper->jsonDecode($this->mediaGallery);
                $this->customAttribute  = $this->jsonHelper->jsonDecode($this->customAttribute);
                $this->customOptionData = $this->jsonHelper->jsonDecode($this->customOptionData);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function to validate the sku
     *
     * @param string $sku sku
     *
     * @return string sku
     */
    public function checkSkuExist($sku)
    {
        try {
            $id = $this->productResourceModel->getIdBySku($sku);
            if ($id) {
                $avialability = 0;
            } else {
                $avialability = 1;
            }
        } catch (\Exception $e) {
            $avialability = 0;
        }
        if ($avialability == 0) {
            $sku = $sku.rand();
            $sku = $this->checkSkuExist($sku);
        }
        return $sku;
    }

    public function checkSkuExists($sku)
    {
        try {
            $id = $this->productResourceModel->getIdBySku($sku);
            if ($id && $id != $this->productId) {
                $avialability = 0;
            } else {
                $avialability = 1;
            }
        } catch (\Exception $e) {
            $avialability = 0;
        }
        if ($avialability == 0) {
            throw new \InvalidArgumentException(__("SKU %1 already exists.", $sku));
        }
    }

    /**
     * Function to validiate request params
     *
     * @param array $wholedata array of request params
     *
     * @return array
     */
    public function validatePost(&$wholedata)
    {
        $errors = [];
        $data = [];
        foreach ($wholedata["product"] as $code => $value) {
            switch ($code) {
                case "name":
                    $result = $this->nameValidateFunction($value, $code, $data);
                    if ($result["error"]) {
                        $errors[] = __("Name has to be completed");
                    } else {
                        $wholedata["product"][$code] = $result["data"][$code];
                    }
                    break;
                case "description":
                    $result = $this->descriptionValidateFunction($value, $code, $data);
                    if ($result["error"]) {
                        $errors[] = __("Description has to be completed");
                    } else {
                        $wholedata["product"][$code] = $result["data"][$code];
                    }
                    break;
                case "short_description":
                    $result = $this->descriptionValidateFunction($value, $code, $data);
                    if ($result["error"]) {
                        $wholedata["product"][$code] = "";
                    } else {
                        $wholedata["product"][$code] = $result["data"][$code];
                    }
                    break;
                case "price":
                    $result = $this->priceValidateFunction($value, $code, $data);
                    if ($result["error"]) {
                        $errors[] = __("Price should contain only decimal numbers");
                    } else {
                        $wholedata["product"][$code] = $result["data"][$code];
                    }
                    break;
                case "weight":
                    $result = $this->weightValidateFunction($value, $code, $data);
                    if ($result["error"]) {
                        $errors[] = __("Weight should contain only decimal numbers");
                    } else {
                        $wholedata["product"][$code] = $result["data"][$code];
                    }
                    break;
                case "stock":
                    $result = $this->stockValidateFunction($value, $code, $errors, $data);
                    if ($result["error"]) {
                        $errors[] = __("Product quantity should contain only decimal numbers");
                    } else {
                        $wholedata["product"][$code] = $result["data"][$code];
                    }
                    break;
                case "sku_type":
                    $result = $this->skuTypeValidateFunction($value, $code, $data);
                    if ($result["error"]) {
                        $errors[] = __("Sku Type has to be selected");
                    } else {
                        $wholedata["product"][$code] = $result["data"][$code];
                    }
                    break;
                case "sku":
                    $result = $this->skuValidateFunction($value, $code, $data);
                    if ($result["error"]) {
                        $errors[] = __("Sku has to be completed");
                    } else {
                        $wholedata["product"][$code] = $result["data"][$code];
                    }
                    break;
                case "price_type":
                    $result = $this->priceTypeValidateFunction($value, $code, $data);
                    if ($result["error"]) {
                        $errors[] = __("Price Type has to be selected");
                    } else {
                        $wholedata["product"][$code] = $result["data"][$code];
                    }
                    break;
                case "weight_type":
                    $result = $this->weightTypeValidateFunction($value, $code, $data);
                    if ($result["error"]) {
                        $errors[] = __("Weight Type has to be selected");
                    } else {
                        $wholedata["product"][$code] = $result["data"][$code];
                    }
                    break;
                case "bundle_options":
                    $result = $this->bundleOptionValidateFunction($value, $code, $data);
                    if ($result["error"]) {
                        $errors[] = __("Default Title has to be completed");
                    } else {
                        $wholedata["product"][$code] = $result["data"][$code];
                    }
                    break;
                case "meta_title":
                    $result = $this->metaTitleValidateFunction($value, $code, $data);
                    $wholedata["product"][$code] = $result["data"][$code];
                    break;
                case "meta_keyword":
                    $result = $this->metaKeywordValidateFunction($value, $code, $data);
                    $wholedata["product"][$code] = $result["data"][$code];
                    break;
                case "meta_description":
                    $result = $this->metaDiscValidateFunction($value, $code, $data);
                    $wholedata["product"][$code] = $result["data"][$code];
                    break;
                case "mp_product_cart_limit":
                    if (!empty($value)) {
                        $result = $this->stockValidateFunction($value, $code, $errors, $data);
                        if ($result["error"]) {
                            $errors[] = __("Allowed Product Cart Limit Qty should contain only decimal numbers");
                        } else {
                            $wholedata["product"][$code] = $result["data"][$code];
                        }
                    }
                    break;
            }
        }
        return [$errors];
    }

    /**
     * Function nameValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function nameValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == "") {
            $error = true;
        } else {
            $data[$code] = strip_tags($value);
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function descriptionValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function descriptionValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == "") {
            $error = true;
        } else {
            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
            $helper = $this->_objectManager->create(
                \Webkul\Marketplace\Helper\Data::class
            );
            $value = $helper->validateXssString($value);
            $data[$code] = $value;
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function shortDescValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function shortDescValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == "") {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function priceValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function priceValidateFunction($value, $code, $data)
    {
        $error = false;
        if (!preg_match("/^([0-9])+?[0-9.,]*$/", $value)) {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function weightValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function weightValidateFunction($value, $code, $data)
    {
        $error = false;
        if (!preg_match("/^([0-9])+?[0-9.,]*$/", $value)) {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function stockValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function stockValidateFunction($value, $code, $data)
    {
        $error = false;
        if (!preg_match("/^([0-9])+?[0-9.]*$/", $value)) {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function skuTypeValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function skuTypeValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == "") {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function skuValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function skuValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == "") {
            $error = true;
        } else {
            $data[$code] = strip_tags($value);
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function priceTypeValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function priceTypeValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == "") {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function weightTypeValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function weightTypeValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == "") {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function bundleOptionValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function bundleOptionValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == "") {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function metaTitleValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function metaTitleValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == "") {
            $error = true;
            $data[$code] = "";
        } else {
            $data[$code] = strip_tags($value);
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function metaKeywordValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function metaKeywordValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == "") {
            $error = true;
            $data[$code] = "";
        } else {
            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
            $helper = $this->_objectManager->create(
                \Webkul\Marketplace\Helper\Data::class
            );
            $value = $helper->validateXssString($value);
            $data[$code] = $value;
        }
        return ["error" => $error, "data" => $data];
    }

    /**
     * Function metaDiscValidateFunction
     *
     * @param string $value value of parameter
     * @param string $code  code of the field
     * @param array  $data  wholedata
     *
     * @return array
     */
    private function metaDiscValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == "") {
            $error = true;
            $data[$code] = "";
        } else {
            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
            $helper = $this->_objectManager->create(
                \Webkul\Marketplace\Helper\Data::class
            );
            $value = $helper->validateXssString($value);
            $data[$code] = $value;
        }
        return ["error" => $error, "data" => $data];
    }
}
