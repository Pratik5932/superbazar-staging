<?php
/**
* Webkul Software.
*
* @category  Webkul
* @package   Webkul_Mobikul
* @author    Webkul
* @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html
*/

namespace Webkul\MobikulMpHyperLocal\Controller\Extra;

class SearchSuggestion extends \Webkul\MobikulApi\Controller\Extra\SearchSuggestion  {

    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $helper = $this->searchSuggestionHelper;
            $query = is_array($this->searchQuery) ? "" : trim($this->searchQuery);
            $maxQueryLength = $this->helper->getConfigData("catalog/search/max_query_length");
            $query = substr($this->searchQuery, 0, $maxQueryLength);
            $tagArray = [];
            $productArray = [];
            if ($helper->displayTags()) {
                $tagCollection = $this->queryCollection
                    ->addFieldToFilter("store_id", [["finset"=>[$this->storeId]]])
                    ->setPopularQueryFilter($this->storeId)
                    ->addFieldToFilter("query_text", ["like"=>"%".$query."%"])
                    ->setPageSize($helper->getNumberOfTags())
                    ->load()
                    ->getItems();
                foreach ($tagCollection as $item) {
                    $tagArray[] = [
                        "term" => $query,
                        "title" => $item->getQueryText(),
                        "count" => $item->getNumResults()
                    ];
                }
            }
            if ($helper->displayProducts()) {
                $productCollection = $this->productCollection;
                if ($this->categoryId > 0) {
                    $productCollection = $this->categoryFactory->create()->load($this->categoryId)
                        ->getProductCollection()
                        ->addAttributeToSelect("*")
                        ->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()])
                        ->addAttributeToFilter("visibility", ["in"=>[2, 3, 4]])
                        ->addAttributeToFilter(
                            [
                                ["attribute"=>"sku", "like"=>"%".$query."%"],
                                ["attribute"=>"name", "like"=>"%".$query."%"],
                            ]
                        );
                    $productCollection->addAttributeToFilter('entity_id', ['in' => $this->getAllowedProductIds()]);
                } else {
                    $productCollection
                        ->addAttributeToSelect("*")
                        ->addAttributeToSelect("sku")
                        ->addAttributeToSelect("name")
                        ->addAttributeToSelect("description")
                        ->addAttributeToSelect("short_description")
                        ->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()])
                        ->addAttributeToFilter(
                            [
                                ["attribute"=>"sku", "like"=>"%".$query."%"],
                                ["attribute"=>"name", "like"=>"%".$query."%"],
                            ]
                        )
                        ->addAttributeToFilter("visibility", ["in"=>[2, 3, 4]]);
                }
                $productCollection->addAttributeToFilter('entity_id', ['in' => $this->getAllowedProductIds()]);
                $productCollection->setPageSize($helper->getNumberOfProducts());
                $productArray = $this->getProductArray($productCollection, $query);
            }
            $suggestData = [$tagArray, $productArray];
            $suggestProductArray = $this->getSuggestedProductArray($suggestData);
            $this->returnArray["success"] = true;
            $this->returnArray["suggestProductArray"] = $suggestProductArray;
            if (count($this->returnArray["suggestProductArray"]) == 0) {
                $this->returnArray["suggestProductArray"] = new \stdClass();
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function getAllowedProductIds() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $hyperLocalhelper = $objectManager->create("Webkul\MpHyperLocal\Helper\Data");
        $sellerIds = $hyperLocalhelper->getNearestSellers();
        return $hyperLocalhelper->getNearestProducts($sellerIds);
    }

}