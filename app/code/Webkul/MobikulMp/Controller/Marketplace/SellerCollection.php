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
namespace Webkul\MobikulMp\Controller\Marketplace;

/**
 * Class SellerCollection controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class SellerCollection extends AbstractMarketplace
{
    /**
     * Execute function for class SellerCollection
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "SELLERCOLLECTION".$this->storeId.$this->width.$this->sellerId.$this->sortData;
            $cacheString .= $this->categoryId.$this->pageNumber.$this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->store->setCurrentCurrencyCode($this->currency);
            $sortData    = $this->jsonHelper->jsonDecode($this->sortData);
            // getting recently added products /////////////////////////////////////////////////////////////////
            $catalogProductWebsite = $this->marketplaceProductResource->getTable("catalog_product_website");
            $querydata = $this->marketplaceProduct->getCollection()
                ->addFieldToFilter("seller_id", $this->sellerId)
                ->addFieldToFilter("status", ["neq"=>2])
                ->addFieldToSelect("mageproduct_id")
                ->setOrder("mageproduct_id");
                
            if ($this->categoryId == 0) {
                $this->categoryId = $this->marketplaceHelper->getRootCategoryIdByStoreId($this->storeId);
            }
            
            $category = $this->category->setStoreId($this->storeId)->load($this->categoryId);
            $productCollection = $this->productModel->getCollection()
                ->addAttributeToSelect("*")
                ->addCategoryFilter($category)
                ->addAttributeToFilter(
                    "entity_id",
                    [
                    "in" =>
                    $querydata->getAllIds()
                    ]
                )
                ->addAttributeToFilter("visibility", ["in"=>[4]])
                ->addAttributeToFilter("status", 1);
            // Sorting product collection ///////////////////////////////////////////////////
            if (count($sortData) > 0) {
                $sortBy = $sortData[0];
                if ($sortData[1] == 0) {
                    $productCollection->setOrder($sortBy, "ASC");
                } else {
                    $productCollection->setOrder($sortBy, "DESC");
                }
            } else {
                $productCollection->setOrder("position", "ASC");
            }
            $productList = [];
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $productCollection->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $productCollection->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            foreach ($productCollection as $eachProduct) {
                $productList[] = $this->helperCatalog->getOneProductRelevantData(
                    $eachProduct,
                    $this->storeId,
                    $this->width,
                    $this->customerId
                );
            }
            $this->returnArray["productList"] = $productList;
            
            // Creating sort attribute collection ///////////////////////////////////////////
            $sortingData = [];
            $toolbar     = $this->toolBar;
            foreach ($toolbar->getAvailableOrders() as $key => $order) {
                $each          = [];
                $each["code"]  = $key;
                $each["label"] = $order;
                $sortingData[] = $each;
            }
            $this->returnArray["sortingData"] = $sortingData;
            // getting category /////////////////////////////////////////////////////////////
            $collection = $this->productModel->getCollection()
                ->addAttributeToSelect("entity_id")
                ->addAttributeToFilter("entity_id", ["in" => $querydata->getData()])
                ->addAttributeToFilter("visibility", ["in" => [4]]);
            $collection->addStoreFilter();
            $marketplaceProduct = $this->marketplaceProductResource->getTable("marketplace_product");
            $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
            $collection->getSelect()->join(
                ["mpp"=>$marketplaceProduct],
                "mpp.mageproduct_id=e.entity_id",
                ["mageproduct_id"=>"e.entity_id"]
            );
            $proAttId = $this->eavAttribute->getIdByCode("catalog_category", "name");
            $catalogCategoryProduct = $this->sellerCollection->getTable("catalog_category_product");
            $catalogCategoryEntity = $this->sellerCollection->getTable("catalog_category_entity");
            $catalogCategoryEntityVarchar = $this->sellerCollection->getTable("catalog_category_entity_varchar");
            $collection->getSelect()
                ->join(
                    ["ccp"=>$catalogCategoryProduct],
                    "ccp.product_id=mpp.mageproduct_id",
                    ["category_id"=>"category_id"]
                )
                ->join(["cce"=>$catalogCategoryEntity], "cce.entity_id=ccp.category_id", ["parent_id"=>"parent_id"])
                ->where("cce.parent_id='".$this->categoryId."'")
                ->columns("COUNT(*) AS countCategory")
                ->group("category_id")
                ->join(["ce1"=>$catalogCategoryEntityVarchar], "ce1.entity_id=ccp.category_id", ["catname"=>"value"])
                ->where("ce1.attribute_id=".$proAttId." AND ce1.store_id=0")
                ->order("catname");
            $categoryList = [];
            foreach ($collection as $each) {
                $eachCategory          = [];
                $eachCategory["id"]    = $each["category_id"];
                $eachCategory["name"]  = $each["catname"];
                $eachCategory["count"] = $each["countCategory"];
                $categoryList[]        = $eachCategory;
            }
            $this->returnArray["categoryList"] = $categoryList;
            $this->returnArray["success"] = true;
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
            $this->eTag          = $this->wholeData["eTag"]       ?? "";
            $this->storeId       = $this->wholeData["storeId"]    ?? 0;
            $this->width         = $this->wholeData["width"]      ?? 1000;
            $this->sellerId      = $this->wholeData["sellerId"]   ?? 0;
            $this->sortData      = $this->wholeData["sortData"]   ?? "[]";
            $this->categoryId    = $this->wholeData["categoryId"] ?? 0;
            $this->pageNumber    = $this->wholeData["pageNumber"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->currency      = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
            $this->customerId    = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            } elseif ($this->customerId != 0) {
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
