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
 * Class Edit Form Data for editing vendor Product
 *
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class EditFormData extends AbstractProduct
{

    /**
     * Execute function for class EditFormData
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        $this->returnArray["inventoryAvailabilityOptions"] = [
            ["value"=>1, "label"=>__("In Stock")],
            ["value"=>0, "label"=>__("Out of Stock")]
        ];
        try {
            $this->verifyRequest();
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);
            $attibuteSets  = [];
            if (count($this->marketplaceHelper->getAllowedSets()) > 1) {
                foreach ($this->marketplaceHelper->getAllowedSets() as $set) {
                    $attibuteSets[] = $set;
                }
                $this->returnArray["allowedAttributes"] = $attibuteSets;
            } else {
                $allowedSets = $this->marketplaceHelper->getAllowedSets();
                $this->returnArray["allowedAttributes"][] = $allowedSets[0]["value"];
            }
            $allowedTypes = [];
            if (count($this->marketplaceHelper->getAllowedProductTypes()) > 1) {
                foreach ($this->marketplaceHelper->getAllowedProductTypes() as $type) {
                    $allowedTypes[] = $type;
                }
                $this->returnArray["allowedTypes"] = $allowedTypes;
            } else {
                $allowedProducts = $this->marketplaceHelper->getAllowedProductTypes();
                $this->returnArray["allowedTypes"][] = $allowedProducts[0]["value"];
            }
            $this->returnArray["showHint"] = (bool)$this->marketplaceHelper->getProductHintStatus();
            $this->returnArray["isCategoryTreeAllowed"] = (bool)$this->marketplaceHelper->getIsAdminViewCategoryTree();
            if ($this->mpMobikulHelper->getAllowedCategoryIds($this->sellerId) &&
                !$this->returnArray["isCategoryTreeAllowed"]
            ) {
                $categoryIds = explode(",", trim($this->mpMobikulHelper->getAllowedCategoryIds($this->sellerId)));
                foreach ($categoryIds as $categoryId) {
                    $category = $this->category->setStoreId($this->storeId)->load($this->categoryId);
                    if ($category->getId()) {
                        $eachCategory                = [];
                        $eachCategory["id"]          = $category->getId();
                        $eachCategory["name"]        = $category->getName();
                        $this->returnArray["categories"][] = $eachCategory;
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
            if (count($this->returnArray["categories"]) == 0) {
                $rootNode = $this->categoryTree->getRootNode();
                $this->returnArray["categories"] = $this->categoryTree->getTree(
                    $rootNode,
                    null,
                    $this->storeId
                )->__toArray();
            }
            $this->returnArray["skuType"]                   = $this->marketplaceHelper->getSkuType();
            $this->returnArray["skuhint"]                   = $this->marketplaceHelper->getProductHintSku();
            $this->returnArray["skuPrefix"]                 = $this->marketplaceHelper->getSkuPrefix();
            $this->returnArray["priceHint"]                 = $this->marketplaceHelper->getProductHintPrice();
            $this->returnArray["productHint"]               = $this->marketplaceHelper->getProductHintName();
            $this->returnArray["categoryHint"]              = $this->marketplaceHelper->getProductHintCategory();
            $this->returnArray["inventoryHint"]             = $this->marketplaceHelper->getProductHintQty();
            $this->returnArray["currencySymbol"]            = $this->marketplaceHelper->getCurrencySymbol();
            $this->returnArray["descriptionHint"]           = $this->marketplaceHelper->getProductHintDesc();
            $this->returnArray["specialPriceHint"]          = $this->marketplaceHelper->getProductHintSpecialPrice();
            $this->returnArray["specialEndDateHint"]        = $this->marketplaceHelper->getProductHintEndDate();
            $this->returnArray["shortdescriptionHint"]      = $this->marketplaceHelper->getProductHintShortDesc();
            $this->returnArray["specialStartDateHint"]      = $this->marketplaceHelper->getProductHintStartDate();
            $this->returnArray["inventoryAvailabilityHint"] = $this->marketplaceHelper->getProductHintStock();
            $productVisibility                        = $this->marketplaceHelper->getVisibilityOptionArray();
            foreach ($productVisibility as $key => $value) {
                $this->returnArray["visibilityOptions"][] = ["value"=>$key, "label"=>$value];
            }
            $this->returnArray["taxHint"] = $this->marketplaceHelper->getProductHintTax();
            $taxes = $this->marketplaceHelper->getTaxClassModel();
            foreach ($taxes as $tax) {
                $this->returnArray["taxOptions"][] = ["value"=>$tax->getId(), "label"=>$tax->getClassName()];
            }
            $this->returnArray["weightHint"] = $this->marketplaceHelper->getProductHintWeight();
            if ($this->productId) {
                $product = $this->product->create()->load($this->productId);
                $productData = $product->getData();

                $this->returnArray["productData"]["type"] = $productData['type_id'];
                $this->returnArray["productData"]["websiteIds"][] = $product->getStore()->getWebsiteId();
                $this->returnArray["productData"]["attributeSetId"] = $productData['attribute_set_id'];
                $this->returnArray["productData"]["categoryIds"] = $productData['category_ids'];
                $this->returnArray["productData"]["name"] = $productData['name'];
                $this->returnArray["productData"]["description"] = $productData['description'];
                $this->returnArray["productData"]["shortDescription"] = $productData['short_description'];
                $this->returnArray["productData"]["sku"] = $productData['sku'];
                $this->returnArray["productData"]["price"] = $productData['price'];
                $this->returnArray["productData"]["specialPrice"] = $productData['special_price'];
                $this->returnArray["productData"]["specialFromDate"] = $productData['special_from_date'];
                $this->returnArray["productData"]["specialToDate"] = $productData['special_to_date'];
                $this->returnArray["productData"]["qty"] = $productData['quantity_and_stock_status']['qty'];
                $this->returnArray["productData"]["isInStock"] = $productData[
                    'quantity_and_stock_status'
                ]['is_in_stock'];
                $this->returnArray["productData"]["taxClassId"] = $productData['tax_class_id'];
                $this->returnArray["productData"]["productHasWeight"] = 0;
                if ($productData['type_id'] =='simple' ||
                    (
                        $productData['type_id']=='configurable' &&
                        !empty($productData['weight'])
                    )
                ) {
                    $this->returnArray["productData"]["productHasWeight"] = 1;
                    $this->returnArray["productData"]["weight"] = $productData['weight'];
                }
                $this->returnArray["productData"]["metaTitle"] = '';
                if (!empty($productData['meta_title'])) {
                    $this->returnArray["productData"]["metaTitle"] = $productData['meta_title'];
                }
                $this->returnArray["productData"]["metaKeyword"] = '';
                if (!empty($productData['meta_keyword'])) {
                    $this->returnArray["productData"]["metaKeyword"] = $productData['meta_keyword'];
                }
                $this->returnArray["productData"]["metaDescription"] = '';
                if (!empty($productData['meta_description'])) {
                    $this->returnArray["productData"]["metaDescription"] = $productData['meta_description'];
                }
                $this->returnArray["productData"]["mpProductCartLimit"] = null;
                if (!empty($productData['mp_product_cart_limit'])) {
                    $this->returnArray["productData"]["mpProductCartLimit"] = $productData['mp_product_cart_limit'];
                }
                $this->returnArray["productData"]["baseImage"] = $productData['image'];
                $this->returnArray["productData"]["smallImage"] = $productData['small_image'];
                $this->returnArray["productData"]["swatchImage"] = $productData['swatch_image'];
                $this->returnArray["productData"]["thumbnail"] = $productData['thumbnail'];
                $this->returnArray["productData"]["mediaGallery"] = [];
                foreach ($product->getMediaGalleryImages() as $gal) {
                    array_push($this->returnArray["productData"]["mediaGallery"], $gal->getData());
                }
                $this->returnArray["productData"]["related"] = $this->getRelatedProductsData($product, "related");
                $this->returnArray["productData"]["upsell"] = $this->getRelatedProductsData($product, "upsell");
                $this->returnArray["productData"]["crossSell"] = $this->getRelatedProductsData($product, "crosssell");
                
            }
            $this->returnArray["success"]    = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
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
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->productId     = $this->wholeData["productId"]     ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->customerId    = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            } elseif ($this->customerId != 0) {
                $this->sellerId = $this->customerId;
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function To get Related product data
     *
     * @param \Magento\Catalog\Model\Product $product object of catelog product
     * @param string                         $type    this is the product type
     *
     * @return array
     */
    public function getRelatedProductsData($product, $type)
    {
        $productIds = [];
        $relatedProductColl = $product->getProductLinks();
        foreach ($relatedProductColl as $key => $value) {
            if ($value['link_type'] == $type) {
                $productBySku = $this->productRepository->get($value['linked_product_sku']);
                array_push($productIds, $productBySku->getId());
            }
        }
        return $productIds;
    }
}
