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

    class AdvancedSearchResult extends \Webkul\MobikulApi\Controller\Catalog\AbstractCatalog  {

        public function execute()  {
            $returnArray                           = [];
            $returnArray["success"]                = false;
            $returnArray["message"]                = "";
            $returnArray["otherError"]             = "";
            $returnArray["totalCount"]             = 0;
            $returnArray["productList"]            = [];
            $returnArray["sortingData"]            = [];
            $returnArray["layeredData"]            = [];
            $returnArray["criteriaData"]           = [];
            $returnArray["showSwatchOnCollection"] = false;
            try  {
                $wholeData      = $this->getRequest()->getPostValue();
                $this->headers = $this->getRequest()->getHeaders();
                $this->helper->log(__CLASS__, "logClass", $wholeData);
                $this->helper->log($wholeData, "logParams", $wholeData);
                $this->helper->log($this->headers, "logHeaders", $wholeData);
                if ($wholeData)  {
                    $authKey    = $this->getRequest()->getHeader("authKey");
                    $authData   = $this->helper->isAuthorized($authKey);
                    if ($authData["code"] == 1)  {
                        $width         = $wholeData["width"]         ?? 1000;
                        $storeId       = $wholeData["storeId"]       ?? 0;
                        $sortData      = $wholeData["sortData"]      ?? "[]";
                        $pageNumber    = $wholeData["pageNumber"]    ?? 1;
                        $filterData    = $wholeData["filterData"]    ?? "[]";
                        $queryString   = $wholeData["queryString"]   ?? "[]";
                        $customerToken = $wholeData["customerToken"] ?? "";
                        $customerId    = $this->helper->getCustomerByToken($customerToken) ?? 0;
                        $sortData      = $this->jsonHelper->jsonDecode($sortData);
                         $filterData    = $this->jsonHelper->jsonDecode($filterData);
                         $queryArray    = $this->jsonHelper->jsonDecode($queryString);
                        $environment   = $this->emulate->startEnvironmentEmulation($storeId); 
// checking customer token ///////////////////////////////////////////////////////////////////////////////////////////////////////
                        if (!$customerId && $customerToken != "") {
                            $returnArray["message"]    = __("As customer you are requesting does not exist, so you need to logout.");
                            $returnArray["otherError"] = "customerNotExist";
                            $customerId = 0;           
                        } elseif ($customerId) {
                            $this->customerSession->setCustomerId($customerId);
                        } 
// setting currency /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $currency      = $wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
                        $this->store->setCurrentCurrencyCode($currency);
// checking is swatch allowed on colletion page /////////////////////////////////////////////////////////////////////////////////
                        $returnArray["showSwatchOnCollection"] = (bool)$this->helper->getConfigData("catalog/frontend/show_swatches_in_product_list");
// Getting Product Collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                       $queryArray        = $this->helperCatalog->getQueryArray($queryArray);
                       $advancedSearch    = $this->advancedCatalogSearch->addFilters($queryArray);
                       $productCollection = $advancedSearch->getProductCollection();

                        // Hyper Local Filter /////////
                        $productCollection->addAttributeToFilter('entity_id', ['in' => $this->getAllowedProductIds()]);
                        //end////
                        if ($this->helperCatalog->showOutOfStock() == 0)
                            $this->stockFilter->addInStockFilterToCollection($productCollection);
// Filtering product collection /////////////////////////////////////////////////////////////////////////////////////////////////
                        if (count($filterData) > 0)  {
                            for ($i=0; $i<count($filterData[0]); ++$i)  {
                                if ($filterData[0][$i] != "" && $filterData[1][$i] == "price")  {
                                    $priceRange    = explode("-", $filterData[0][$i]);
                                    $to            = $priceRange[1];
                                    $from          = $priceRange[0];
                                    $currencyRate  = $productCollection->getCurrencyRate();
                                    $fromRange     = ($from - (.01 / 2)) / $currencyRate;
                                    $toRange       = ($to - (.01 / 2)) / $currencyRate;
                                    $select        = $productCollection->getSelect();
                                    $isFlatEnabled = $this->productResourceCollection->isEnabledFlat();
                                    if ($isFlatEnabled)  {
                                        if ($from !== "")
                                            $select->where("price_index.price".">=".$fromRange);
                                        if ($to !== "")
                                            $select->where("price_index.price"."<".$toRange);
                                    } else  {
                                        if ($from !== "")
                                            $select->where("price_index.min_price".">=".$fromRange);
                                        if ($to !== "")
                                            $select->where("price_index.min_price"."<".$toRange);
                                    }
                                } elseif ($filterData[0][$i] != "" && $filterData[1][$i] == "cat")  {
                                    $categoryToFilter = $this->category->create()->load($filterData[0][$i]);
                                    $productCollection->setStoreId($storeId)->addCategoryFilter($categoryToFilter);
                                } else  {
                                    $attribute = $this->eavConfig->getAttribute("catalog_product", $filterData[1][$i]);
                                    $this->layerAttribute->create()->setAttributeModel($attribute);
                                    $connection = $this->layerFilterAttributeResource->create()->getConnection();
                                    $tableAlias = $attribute->getAttributeCode()."_idx";
                                    $conditions = [
                                        "{$tableAlias}.entity_id = e.entity_id",
                                        $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                                        $connection->quoteInto("{$tableAlias}.store_id = ?", $productCollection->getStoreId()),
                                        $connection->quoteInto("{$tableAlias}.value = ?", $filterData[0][$i]),
                                    ];
                                    $productCollection->getSelect()->join([$tableAlias=>$filterAtr->getMainTable()],implode(" AND ",$conditions),[]);
                                }
                            }
                        }
// Sorting product collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        

                        if (count($sortData) > 0)  {
                            $sortBy = $sortData[0];
                            if ($sortData[1] == 0)
                                $productCollection->setOrder($sortBy, "ASC");
                            else
                                $productCollection->setOrder($sortBy, "DESC");
                        }
// Applying pagination //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($pageNumber >= 1)  {
                            $returnArray["totalCount"] = $productCollection->getSize();
                            $pageSize = $this->helperCatalog->getPageSize();
                            $productCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
// Creating product collection //////////////////////////////////////////////////////////////////////////////////////////////////
                        $productList = [];
                        foreach ($productCollection as $eachProduct)  {
                            $eachProduct   = $this->productFactory->create()->load($eachProduct->getId());
                            $productList[] = $this->helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $productList;
// Creating layered attribute collection ////////////////////////////////////////////////////////////////////////////////////////
                        $this->mobikulLayer->_customCollection = $productCollection;
                        $this->mobikulLayerPrice->_customCollection = $productCollection;
                        $doPrice     = true;
                        $layeredData = [];
                        if (count($filterData) > 0)  {
                            if (in_array("price", $filterData[1]))
                                $doPrice = false;
                        }
                        $filters = $this->filterableAttributes->getList();
                        foreach ($filters as $filter)  {
                            if ($filter->getFrontendInput() == "price")  {
                                if ($doPrice)  {
                                    $priceFilterModel    = $this->filterPriceDataprovider->create();
                                    if ($priceFilterModel)  {
                                        $each            = [];
                                        $each["code"]    = $filter->getAttributeCode();
                                        $each["label"]   = $filter->getStoreLabel();
                                        $each["options"] = $this->helperCatalog->getPriceFilter($priceFilterModel, $storeId);
                                        if (!empty($each["options"]))
                                            $layeredData[]   = $each;
                                    }
                                }
                            } else  {
                                $doAttribute = true;
                                if (count($filterData) > 0)  {
                                    if (in_array($filter->getAttributeCode(), $filterData[1]))
                                        $doAttribute = false;
                                }
                                if ($doAttribute)  {
                                    $attributeFilterModel = $this->layerAttribute->create()->setAttributeModel($filter);
                                    if ($attributeFilterModel->getItemsCount())  {
                                        $each            = [];
                                        $each["code"]    = $filter->getAttributeCode();
                                        $each["label"]   = $filter->getStoreLabel();
                                        $each["options"] = $this->helperCatalog->getAttributeFilter($attributeFilterModel, $filter);
                                        if (!empty($each["options"]))
                                            $layeredData[]   =  $each;
                                    }
                                }
                            }
                        }
                        $returnArray["layeredData"] = $layeredData;
// Getting Sorating Collection //////////////////////////////////////////////////////////////////////////////////////////////////
                        $toolbar           = $this->toolbar;
                        $availableOrders   = $toolbar->getAvailableOrders();
                        unset($availableOrders["position"]);
                        $availableOrders   = array_merge(["relevance"=>"Relevance"], $availableOrders);
                        foreach ($availableOrders as $key=>$order)  {
                            $each          = [];
                            $each["code"]  = $key;
                            $each["label"] = __($order);
                            $sortingData[] = $each;
                        }
                        $returnArray["sortingData"] = $sortingData;
// Getting Criteria /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $criteriaData    = [];
                        $searchCriterias = $this->getSearchCriterias($advancedSearch->getSearchCriterias());
                        foreach(["left", "right"] as $side)  {
                            if ($searchCriterias[$side])  {
                                foreach ($searchCriterias[$side] as $criteria)  {
                                    $criteriaData[] = $this->helperCatalog->stripTags($criteria["name"])." : ".$this->helperCatalog->stripTags($criteria["value"]);
                                }
                            }
                        }
                        $returnArray["success"]      = true;
                        $returnArray["criteriaData"] = $criteriaData;
                        $this->customerSession->setCustomerId(null);
                        $this->emulate->stopEnvironmentEmulation($environment);
                        $this->helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    } else  {
                        return $this->getJsonResponse($returnArray, 401, $authData["token"]);
                    }
                } else  {
                    $returnArray["message"]      = __("Invalid Request");
                    $this->helper->log($returnArray, "logResponse", $wholeData);
                    return $this->getJsonResponse($returnArray);
                }
            } catch (\Exception $e)  {
                $returnArray["message"] = __($e->getMessage());
                $this->helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

        public function getSearchCriterias($searchCriterias)  {
            $middle = ceil(count($searchCriterias) / 2);
            $left   = array_slice($searchCriterias, 0, $middle);
            $right  = array_slice($searchCriterias, $middle);
            return ["left"=>$left, "right"=>$right];
        }

        public function getAllowedProductIds() {
            $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $hyperLocalhelper = $this->objectManager->create("Webkul\MpHyperLocal\Helper\Data");
            $sellerIds = $hyperLocalhelper->getNearestSellers();
            return $hyperLocalhelper->getNearestProducts($sellerIds);
        }

    }