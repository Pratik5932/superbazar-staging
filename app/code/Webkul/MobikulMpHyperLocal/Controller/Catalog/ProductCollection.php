<?php
/**
* Webkul Software.
*
* @category Webkul
*
* @author    Webkul
* @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html
*/

namespace Webkul\MobikulMpHyperLocal\Controller\Catalog;

class ProductCollection extends \Webkul\MobikulApi\Controller\Catalog\ProductCollection
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "PRODUCTCOLLECTION".$this->width.$this->storeId.$this->type.$this->id.
            $this->quoteId.$this->mFactor.$this->pageNumber.
            $this->id.$this->customerToken.$this->currency;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            } 
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->sortData = $this->jsonHelper->jsonDecode($this->sortData);
            $this->filterData = $this->jsonHelper->jsonDecode($this->filterData);
            // Setting currency /////////////////////////////////////////////////////////////////////////////
            $this->store->setCurrentCurrencyCode($this->currency);
            if ($this->type == "customCarousel") {
                switch ($this->id) {
                    case "featuredProduct":
                        $this->getFeaturedProductCollection();
                        break;
                    case "newProduct":
                        $this->getNewProductCollection();
                        break;
                    case "hotDeals":
                        $this->getHotDealsCollection();
                        break;
                    default:
                        $this->getCarouselProductCollection();
                        break;
                } 
            } elseif ($this->type == "search") {
                $isFlatEnabled = $this->productResourceCollection->isEnabledFlat();
                $this->getRequest()->setParam("q", $this->id);
                $query = $this->queryFactory->get();
                $query->setStoreId($this->storeId);
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity()+1);
                } else {
                    $query->setQueryText($this->id)
                        ->setIsActive(1)
                        ->setPopularity(1)
                        ->setIsProcessed(1)
                        ->setDisplayInTerms(1);
                } 
                $query->prepare()->save();
                $this->collection = $this->helperCatalog->getProductListColl($this->storeInterface->getStore()->getRootCategoryId(), "search");
            } elseif ($this->type == "advSearch") {
                $this->sortData = "{}";
                $this->filterData = "{}";
                $this->sortData = $this->jsonHelper->jsonDecode($this->sortData);
                $this->filterData = $this->jsonHelper->jsonDecode($this->filterData);
                $this->queryArray = $this->jsonHelper->jsonDecode($this->id);
                $this->queryArray = $this->helperCatalog->getQueryArray($this->queryArray);
                $advancedSearch = $this->advancedCatalogSearch->addFilters($this->queryArray);
                $this->collection = $advancedSearch->getProductCollection();
                $criteriaData    = [];
                $searchCriterias = $this->getSearchCriterias($advancedSearch->getSearchCriterias());
                foreach (["left", "right"] as $side) {
                    if ($searchCriterias[$side]) {
                        foreach ($searchCriterias[$side] as $criteria) {
                            $criteriaData[] = $this->helperCatalog->stripTags($criteria["name"])." : ".$this->helperCatalog->stripTags($criteria["value"]);
                        }
                    }
                }
                $this->returnArray["criteriaData"] = $criteriaData;
            } elseif ($this->type == "carousel") {
                $this->getCarouselProductCollection();
            } elseif ($this->type == "customCollection") {
                $this->notification = $this->mobikulNotification->create()->load($this->id);
                $customFilterData = unserialize($this->notification->getFilterData());
                $notificationCollectionType = $this->notification->getCollectionType();
                $this->getCustomNotificationCollection($notificationCollectionType, $customFilterData);
            } else {
                // Creating product collection //////////////////////////////////////
                $this->loadedCategory = $this->category->create()->setStoreId($this->storeId)->load($this->id);
                $this->coreRegistry->register("current_category", $this->loadedCategory);
                // $categoryBlock = $this->listProduct;
                $this->collection = $this->helperCatalog->getProductListColl($this->id);
                $this->collection->addAttributeToSelect("*");
            } 
            if ($this->collection) { 
                if(count($this->getAllowedProductIds()) && ($this->getRequest()->getParam("address"))){
                $this->collection->addAttributeToFilter('entity_id', ['in' => $this->getAllowedProductIds()]);
                }
            }
            if ($this->collection && $this->helperCatalog->showOutOfStock() == 0) {
                $this->stockFilter->addInStockFilterToCollection($this->collection);
            } 
            // Filtering product collection /////////////////////////////////////////
           
            if (count($this->filterData) > 0) { 
           
            $this->filterProductCollection();
           }
            // Sorting product collection ///////////////////////////////////////////
            $this->sortProductCollection(); 
            // Applying pagination //////////////////////////////////////////////////
            if ($this->pageNumber >= 1) {
                if ($this->collection) {
                    $this->returnArray["totalCount"] = $this->collection->getSize();
                } else {
                    $this->returnArray["totalCount"] = 0;
                }
                $pageSize = $this->helperCatalog->getPageSize();
                if ($this->collection) {
                    $this->collection->setPageSize($pageSize)->setCurPage($this->pageNumber);
                }
            } 
            // Creating product collection //////////////////////////////////////////
            $productList = [];
            if ($this->collection) {
               
                foreach ($this->collection->getData() as $eachProduct) {
                    
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($eachProduct['entity_id']);
                    
                    $productList[] = $this->helperCatalog->getOneProductRelevantData($product, $this->storeId, $this->width, $this->customerId);
                }
            } 
           
            $this->returnArray["productList"] = $productList;
            // Creating filter attribute collection /////////////////////////////////
            $this->getLayeredData();
            // Creating sort attribute collection ///////////////////////////////////
            $this->getSortingData();
            // Cart Count ///////////////////////////////////////////////////////////
            if ($this->quoteId != 0) {
                $this->returnArray["cartCount"] = $this->helper->getCartCount($this->quoteModel->setStoreId($this->storeId)->load($this->quoteId));
            } 
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
                $this->returnArray["cartCount"] = $this->helper->getCartCount($quote);
            } 
            // Getting category banner image ////////////////////////////////////////
            if ($this->type == "category") {
                $this->getCategoryImages();
            } 
            $this->returnArray["success"] = true;
           
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->checkNGenerateEtag($cacheString);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            echo $e->getMessage();die;
            
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }



     /**
     * Function to get layered data
     *
     * @return object
     */
    protected function getLayeredData()
    {
        
        $layeredData = [];
        $doCategory = true;
        if (count($this->filterData) > 0) {
            if (in_array("cat", $this->filterData[1])) {
                $doCategory = false;
            }
        }
        if ($this->type == "category" && $doCategory) {
            
            $categoryFilterModel = $this->categoryLayer;
            if ($categoryFilterModel->getItemsCount()) {
                $each = [];
                $each["code"] = "cat";
                $each["label"] = $categoryFilterModel->getName();
                $each["options"] = $this->addCountToCategories($this->loadedCategory->getChildrenCategories());
                if (!empty($each["options"])) {
                    $layeredData[] = $each;
                }
            }
        }
        $doPrice = true;
        if (count($this->filterData) > 0) {
            if (in_array("price", $this->filterData[1])) {
                $doPrice = false;
            }
        }
        $filters = $this->filterableAttributes->getList();
        if ($this->type == "notification") {
            $layeredData = [];
            $filters = $this->filterableAttributes->getList();
            foreach ($filters as $filter) {
                $doAttribute = true;
                if (count($this->filterData) > 0) {
                    if (in_array($filter->getAttributeCode(), $this->filterData[1])) {
                        $doAttribute = false;
                    }
                }
                if ($doAttribute) {
                    $attributeFilterModel = $this->filterAttribute->setAttributeModel($filter);
                    if ($attributeFilterModel->getItemsCount()) {
                        $each = [];
                        $each["code"] = $filter->getAttributeCode();
                        $each["label"] = $filter->getFrontendLabel();
                        $each["options"] = $this->helperCatalog->getAttributeFilter($attributeFilterModel, $filter);
                        $layeredData[] = $each;
                    }
                }
            }
            $this->returnArray["layeredData"] = $layeredData;
            return;
        }
        if ($this->type == "customCollection") {
            $doPrice = true;
            $layeredData = [];
            if (count($this->filterData) > 0) {
                if (in_array("price", $this->filterData[1])) {
                    $doPrice = false;
                }
            }
            $filters = $this->filterableAttributes->getList();
            foreach ($filters as $filter) {
                if ($filter->getFrontendInput() == "price") {
                    if ($doPrice) {
                       
                        $priceFilterModel = $this->filterPriceDataprovider->create();
                        if ($priceFilterModel) {
                            $each = [];
                            
                            $each["code"] = $filter->getAttributeCode();
                            $each["label"] = $filter->getStoreLabel();
                            $each["options"] = $this->helperCatalog->getPriceFilter($priceFilterModel, $this->storeId);
                            if (!empty($each["options"])) {
                                $layeredData[] = $each;
                            }
                        }
                    }
                } else {
                    $doAttribute = true;
                    if (count($this->filterData) > 0) {
                        if (in_array($filter->getAttributeCode(), $this->filterData[1])) {
                            $doAttribute = false;
                        }
                    }
                    if ($doAttribute) {
                        $attributeFilterModel = $this->layerAttribute->create()->setAttributeModel($filter);
                        
                        if ($attributeFilterModel->getItemsCount()) {
                            $each = [];
                            $each["code"] = $filter->getAttributeCode();
                            $each["label"] = $filter->getStoreLabel();
                            $each["options"] = $this->helperCatalog->getAttributeFilter($attributeFilterModel, $filter);
                            if (!empty($each["options"])) {
                                $layeredData[] = $each;
                            }
                        }
                    }
                }
            }
            $this->returnArray["layeredData"] = $layeredData;
            return;
        }
        if ($this->type == "advSearch") {
            $this->mobikulLayer->customCollection = $this->collection;
            $this->mobikulLayerPrice->customCollection = $this->collection;
            $layeredData = [];
            $doPrice = true;
            if (count($this->filterData) > 0) {
                if (in_array("price", $this->filterData[1])) {
                    $doPrice = false;
                }
            }
            $filters = $this->filterableAttributes->getList();
            foreach ($filters as $filter) {
                if ($filter->getFrontendInput() == "price") {
                    if ($doPrice) {
                        $priceFilterModel = $this->filterPriceDataprovider->create();
                        if ($priceFilterModel) {
                            $each = [];
                            $each["code"] = $filter->getAttributeCode();
                            $each["label"] = $filter->getStoreLabel();
                            $each["options"] = $this->helperCatalog->getPriceFilter($priceFilterModel, $this->storeId);
                            if (!empty($each["options"])) {
                                $layeredData[] = $each;
                            }
                        }
                    }
                } else {
                    $doAttribute = true;
                    if (!empty($this->filterData)) {
                        if (in_array($filter->getAttributeCode(), $this->filterData[1])) {
                            $doAttribute = false;
                        }
                    }
                    if ($doAttribute) {
                        $attributeFilterModel = $this->layerAttribute->create()->setAttributeModel($filter);
                        if ($attributeFilterModel->getItemsCount()) {
                            $each = [];
                            $each["code"] = $filter->getAttributeCode();
                            $each["label"] = $filter->getStoreLabel();
                            $each["options"] = $this->helperCatalog->getAttributeFilter($attributeFilterModel, $filter);
                            if (!empty($each["options"])) {
                                $layeredData[] = $each;
                            }
                        }
                    }
                }
            }
            $this->returnArray["layeredData"] = $layeredData;
        }
        if ($this->type != "custom" && $this->type != "customCarousel" && $this->type != "advSearch") {
         
            $allowIds = $this->getAllowedProductIds();
            if($this->getRequest()->getParam("address")){
                $this->collection->addAttributeToFilter('entity_id', ['in' => $allowIds]);
            } 

            foreach ($filters as $filter) {
                if ($filter->getFrontendInput() == "price") {
                    if ($doPrice) {
                        $priceFilterModel = $this->filterPriceDataprovider->create();
                        if ($priceFilterModel) {
                            $each = [];
                            $each["code"] = $filter->getAttributeCode();
                            $each["label"] = $filter->getStoreLabel();
                            $each["options"] = $this->helperCatalog->getPriceFilterOptions($filter, $this->collection);
                            if (!empty($each["options"])) {
                                $layeredData[] = $each;
                            }
                        }
                    }
                } else {
                    $doAttribute = true;
                    if (count($this->filterData) > 0) {
                        if (in_array($filter->getAttributeCode(), $this->filterData[1])) {
                            $doAttribute = false;
                        }
                    }
                    if ($doAttribute) {
                        $attributeFilterModel = $this->layerAttribute->create()->setAttributeModel($filter);
                        if ($attributeFilterModel->getItemsCount()) {
                            $each = [];
                            $each["code"] = $filter->getAttributeCode();
                            $each["label"] = $filter->getStoreLabel();
                            $each["options"] = $this->helperCatalog->getFilterOptions($filter, $this->collection);
                            if (!empty($each["options"])) {
                                $layeredData[] = $each;
                            }
                        }
                    }
                }
            }
        }
        $this->returnArray["layeredData"] = $layeredData;
    }


    public function getAllowedProductIds() {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $hyperLocalhelper = $this->objectManager->create("Webkul\MpHyperLocal\Helper\Data");
        $sellerIds = $hyperLocalhelper->getNearestSellers();
        return $allowedIds = $hyperLocalhelper->getNearestProducts($sellerIds);
    }

    public function verifyRequest()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->create('Psr\Log\LoggerInterface');
        $productCollection->info('======postdata============');
        $productCollection->info(json_encode($this->wholeData));
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->id = $this->wholeData["id"] ?? 0;
            $this->type = $this->wholeData["type"] ?? "category";
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->mFactor = $this->wholeData["mFactor"] ?? 1;
            $this->mFactor = $this->helper->calcMFactor($this->mFactor);
            $this->sortData = $this->wholeData["sortData"] ?? "[]";
            $this->pageNumber = $this->wholeData["pageNumber"] ?? 1;
            $this->filterData = $this->wholeData["filterData"] ?? "[]";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->address = $this->wholeData["address"] ?? "";
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->country = $this->wholeData["country"] ?? "";
            $this->state = $this->wholeData["state"] ?? "";
            $this->currency = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            // Checking customer token //////////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __("Customer you are requesting does not exist, so you need to logout.");
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            } elseif ($this->customerId != 0) {
                $this->customerSession->setCustomerId($this->customerId);
            }
            if(isset($this->wholeData['address']) && $this->wholeData['address'] == "") {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Address not set properly. Please select your address again.")
                );
            }
        } else {
            throw new \Exception(__("Invalid Request"));
        }
    }
}
