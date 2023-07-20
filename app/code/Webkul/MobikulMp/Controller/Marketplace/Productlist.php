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
 * Class ProductList for displaying productList of seller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class Productlist extends AbstractMarketplace
{

    /**
     * Execute function for class Productlist
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "PRODUCTLIST".$this->toDate.$this->fromDate.$this->pageNumber.$this->productName;
            $cacheString .= $this->productStatus.$this->storeId.$this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->store->setCurrentCurrencyCode($this->currency);
            $to   = null;
            $from = null;
            if ($this->toDate) {
                $todate = date_create($this->toDate);
                $to = date_format($todate, "Y-m-d 23:59:59");
            }
            if (!$to) {
                $to = date("Y-m-d 23:59:59");
            }
            if ($this->fromDate) {
                $fromdate = date_create($this->fromDate);
                $from = date_format($fromdate, "Y-m-d H:i:s");
            }
            $proAttId                    = $this->eavAttribute->getIdByCode("catalog_product", "name");
            $proStatusAttId              = $this->eavAttribute->getIdByCode("catalog_product", "status");
            $catalogProductEntity        = $this->marketplaceProductResource->getTable("catalog_product_entity");
            $catalogProductEntityInt     = $this->marketplaceProductResource->getTable("catalog_product_entity_int");
            $catalogProductEntityVarchar = $this->marketplaceProductResource->getTable(
                "catalog_product_entity_varchar"
            );
            $catalogInventoryStockItem   = $this->marketplaceProductResource->getTable('cataloginventory_stock_item');
            $storeCollection             = $this->marketplaceProduct
                ->getCollection()
                ->addFieldToFilter("seller_id", $this->customerId)
                ->getColumnValues('mageproduct_id');
            $collection = $this->productFactory->create()->getCollection()
                ->addFieldToFilter('entity_id', ['in' => $storeCollection])
                ->addAttributeToSelect("*");
            $collection->getSelect()->joinLeft(
                $catalogInventoryStockItem.' as csi',
                'entity_id = csi.product_id',
                ["qty" => "qty"]
            )->where("csi.website_id = 0 OR csi.website_id = 1");
            $collection->setFlag('has_stock_status_filter', false);

            if (isset($this->wholeData["productStatus"]) && $this->wholeData["productStatus"] != null) {
                $collection->addFieldToFilter('status', $this->productStatus);
            }
            if ($this->productName) {
                $collection->addFieldToFilter('name', ['like' => '%'.$this->productName.'%']);
            }
            if ($from) {
                $collection->addFieldToFilter("created_at", ["gteq" => $from]);
            }
            if ($to) {
                $collection->addFieldToFilter("created_at", ["lteq" => $to]);
            }

            $enabledStatusText  = __("Enabled");
            $disabledStatusText = __("Disabled");
            if ($this->marketplaceHelper->getIsProductApproval() ||
                $this->marketplaceHelper->getIsProductEditApproval()
            ) {
                $enabledStatusText  = __("Approved");
                $disabledStatusText = __("Pending");
            }
            $this->returnArray["status"][] = ["value" => '', "label" => __("All")];
            $this->returnArray["status"][] = ["value" => 1, "label" => $enabledStatusText];
            $this->returnArray["status"][] = ["value" => 2, "label" => $disabledStatusText];
            
            $this->returnArray["enabledStatusText"]  = $enabledStatusText;
            $this->returnArray["disabledStatusText"] = $disabledStatusText;
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $collection->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $collection->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            $productList = [];
            foreach ($collection as $product) {
                $eachProduct["productId"] = $product->getEntityId();
                $eachProduct["image"] = $this->imageHelper->init(
                    $product,
                    "product_page_image_medium"
                )->setImageFile($product->getImage())->getUrl();
                $eachProduct["openable"] = false;
                if ($product->getStatus() == 1 && $product->getVisibility() != 1) {
                    $eachProduct["openable"] = true;
                    $eachProduct["productPrice"] = $this->helperCatalog->stripTags(
                        $this->checkoutHelper->formatPrice($product->getPrice())
                    );
                }
                
                $eachProduct["name"] = $product->getName();
                $eachProduct["productType"] = $product->getTypeId();
                $eachProduct["sku"] = $product->getSku();
                $eachProduct["specialPrice"] = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($product->getSpecialPrice())
                );
                $eachProduct["specialFromDate"] = $product->getSpecialFromDate();
                $eachProduct["taxClassId"] = $product->getTaxClassId();
                $eachProduct["categories"] = [];
                $productCategories = $product->getCategoryIds();
                foreach ($productCategories as $category) {
                    $eachProduct["categories"] []= $this->category->load($category)->getName();
                }
                $eachProduct["specialToDate"] = $product->getSpecialToDate();
                if ($product->getStatus() == 2 &&
                    (
                        $this->marketplaceHelper->getIsProductApproval() ||
                        $this->marketplaceHelper->getIsProductEditApproval()
                    )
                ) {
                    $eachProduct["status"]       = $disabledStatusText;
                    $eachProduct["qtySold"]      = __("Pending");
                    $eachProduct["qtyPending"]   = __("Pending");
                    $eachProduct["qtyConfirmed"] = __("Pending");
                    $eachProduct["earnedAmount"] = __("Pending");
                } else {
                    if ($product->getStatus() == 2) {
                        $eachProduct["status"]   = $disabledStatusText;
                    } else {
                        $eachProduct["status"]   = $enabledStatusText;
                    }
                    $salesdetail                 = $this->getSalesdetail($product->getEntityId());
                    $eachProduct["qtySold"]      = $salesdetail["quantitysold"];
                    $eachProduct["qtyPending"]   = $salesdetail["quantitysoldpending"];
                    $eachProduct["qtyConfirmed"] = $salesdetail["quantitysoldconfirmed"];
                    $eachProduct["earnedAmount"] = $this->helperCatalog->stripTags(
                        $this->checkoutHelper->formatPrice($salesdetail["amountearned"])
                    );
                }
                $productList[] = $eachProduct;
            }
            $this->returnArray["productList"] = $productList;
            $this->returnArray["success"]     = true;
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
            $this->eTag          = $this->wholeData["eTag"]          ?? "";
            $this->toDate        = $this->wholeData["toDate"]        ?? "";
            $this->fromDate      = $this->wholeData["fromDate"]      ?? "";
            $this->pageNumber    = $this->wholeData["pageNumber"]    ?? 1;
            $this->productName   = $this->wholeData["productName"]   ?? "";
            $this->productStatus = $this->wholeData["productStatus"] ?? 0;
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->currency       = $this->wholeData["currency"]     ?? $this->store->getBaseCurrencyCode();
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
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
