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

class HomePageData extends \Webkul\MobikulApi\Controller\Catalog\HomePageData  {
    
    protected function getFeaturedDeals()
    {
        $productList = [];
        $collection = new \Magento\Framework\DataObject();
        if ($this->helper->getConfigData("mobikul/configuration/featuredproduct") == 1) {
            $collection = $this->productCollection->create()->addAttributeToSelect('*');
            $collection->getSelect()->order("rand()");
            $collection->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()]);
            $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
            if ($this->helperCatalog->showOutOfStock() == 0) {
                $this->stockFilter->addInStockFilterToCollection($collection);
            }
            $collection->addAttributeToFilter('entity_id', ['in' => $this->getAllowedProductIds()]);
            $collection->setPage(1, 5)->load();
        } else {
            $collection = $this->productCollection->create()
                ->setStore($this->storeId)
                ->addAttributeToSelect('*')
                ->addAttributeToSelect("as_featured")
                ->addAttributeToSelect("image")
                ->addAttributeToSelect("thumbnail")
                ->addAttributeToSelect("small_image")
                ->addAttributeToSelect("visibility")
                ->addStoreFilter()
                ->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()])
                ->setVisibility($this->productVisibility->getVisibleInSiteIds())
                ->addAttributeToFilter("as_featured", 1);
            if ($this->helperCatalog->showOutOfStock() == 0) {
                $this->stockFilter->addInStockFilterToCollection($collection);
            }
            $collection->addAttributeToFilter('entity_id', ['in' => $this->getAllowedProductIds()]);
            $collection->setPageSize(5)->setCurPage(1);
        }
        foreach ($collection as $eachProduct) {
            $productList[] = $this->helperCatalog->getOneProductRelevantData($eachProduct, $this->storeId, $this->width, $this->customerId);
        }
        $carousel = [];
        $carousel["id"] = "featuredProduct";
        $carousel["type"] = "product";
        $carousel["label"] = __("Featured Products");
        $carousel["productList"] = $productList;
        if (count($carousel["productList"])) {
            $this->returnArray["carousel"][] = $carousel;
        }
    }

    protected function getNewDeals()
    {
        $productList = [];
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
        $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
        $newProductCollection = $this->productCollection->create()
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addStoreFilter()
            ->addMinimalPrice()
            ->addAttributeToFilter(
                "news_from_date",
                ["or"=>[
                    0=>["date"=>true, "to"=>$todayEndOfDayDate],
                    1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                "news_to_date",
                ["or"=>[
                    0=>["date"=>true, "from"=>$todayStartOfDayDate],
                    1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                [["attribute"=>"news_from_date", "is"=>new \Zend_Db_Expr("not null")],
                ["attribute"=>"news_to_date", "is"=>new \Zend_Db_Expr("not null")]]
            )
            ->addAttributeToSelect("image")
            ->addAttributeToSelect("thumbnail")
            ->addAttributeToSelect("small_image")
            ->addAttributeToSort("news_from_date", "desc");
        $newProductCollection->addAttributeToFilter('entity_id', ['in' => $this->getAllowedProductIds()]);
        if ($this->helperCatalog->showOutOfStock() == 0) {
            $this->stockFilter->addInStockFilterToCollection($newProductCollection);
        }
        $newProductCollection->setPageSize(5)->setCurPage(1);
        foreach ($newProductCollection as $eachProduct) {
            $productList[] = $this->helperCatalog->getOneProductRelevantData($eachProduct, $this->storeId, $this->width, $this->customerId);
        }
        $carousel = [];
        $carousel["id"] = "newProduct";
        $carousel["type"] = "product";
        $carousel["label"] = __("New Products");
        $carousel["productList"] = $productList;
        if (count($carousel["productList"])) {
            $this->returnArray["carousel"][] = $carousel;
        }
    }

    protected function getHotDeals()
    {
        $productList = [];
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
        $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
        $hotDealCollection = $this->productCollection->create()
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect("image")
            ->addAttributeToSelect("thumbnail")
            ->addAttributeToSelect("small_image")
            ->addAttributeToSelect("special_from_date")
            ->addAttributeToSelect("special_to_date")
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes());
        $hotDealCollection->addStoreFilter()
            ->addAttributeToFilter(
                "special_from_date",
                ["or"=>[
                    0=>["date"=>true, "to"=>$todayEndOfDayDate],
                    1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                "special_to_date",
                ["or"=>[
                    0=>["date"=>true, "from"=>$todayStartOfDayDate],
                    1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                [["attribute"=>"special_from_date", "is"=>new \Zend_Db_Expr("not null")],
                ["attribute"=>"special_to_date", "is"=>new \Zend_Db_Expr("not null")]]
            );
        $hotDealCollection->addAttributeToFilter('entity_id', ['in' => $this->getAllowedProductIds()]);
        if ($this->helperCatalog->showOutOfStock() == 0) {
            $this->stockFilter->addInStockFilterToCollection($hotDealCollection);
        }
        $hotDealCollection->setPageSize(5)->setCurPage(1);
        foreach ($hotDealCollection as $eachProduct) {
            $productList[] = $this->helperCatalog->getOneProductRelevantData($eachProduct, $this->storeId, $this->width, $this->customerId);
        }
        $carousel = [];
        $carousel["id"] = "hotDeals";
        $carousel["type"] = "product";
        $carousel["label"] = __("Hot Deals");
        $carousel["productList"] = $productList;
        if (count($carousel["productList"])) {
            $this->returnArray["carousel"][] = $carousel;
        }
    }

    protected function getImageNProductCarousel()
    {
        $collection = $this->carouselFactory->create()->getCollection()
            ->addFieldToFilter("status", 1)
            ->addFieldToFilter("store_id", ["in" => [$this->storeId, 0]])
            ->setOrder("sort_order", "ASC");
            
            foreach ($collection as $eachCarousel) {
            if ($eachCarousel->getType() == 2) {
                $oneCarousel = [];
                $productList = [];
                $oneCarousel["id"] = $eachCarousel->getId();
                $oneCarousel["type"] = "product";
                $oneCarousel["label"] = $eachCarousel->getTitle();
                if ($eachCarousel->getColorCode()) {
                    $oneCarousel["color"] = $eachCarousel->getColorCode();
                }
                if ($eachCarousel->getFilename()) {
                    $filePath = $this->helper->getUrl("media")."mobikul/carousel/".$eachCarousel->getFilename();
                    $oneCarousel["image"] = $filePath;
                    $oneCarousel["dominantColor"] = $this->helper->getDominantColor($filePath);
                }
                // $oneCarousel["order"] = $eachCarousel->getSortOrder();
                $selectedProdctIds = explode(",", $eachCarousel->getProductIds());
                $productCollection = $this->productCollection->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToSelect("image")
                    ->addAttributeToSelect("thumbnail")
                    ->addAttributeToSelect("small_image")
                    ->addAttributeToFilter("entity_id", ["in"=>$selectedProdctIds])
                    ->setVisibility($this->productVisibility->getVisibleInSiteIds())
                    ->addStoreFilter();

                $productCollection->addAttributeToFilter('entity_id', ['in' => $this->getAllowedProductIds()]);
                if ($this->helperCatalog->showOutOfStock() == 0) {
                    $this->stockFilter->addInStockFilterToCollection($productCollection);
                }
                $productCollection->setPageSize(5)->setCurPage(1);
                foreach ($productCollection as $eachProduct) {
                    $productList[] = $this->helperCatalog->getOneProductRelevantData($eachProduct, $this->storeId, $this->width, $this->customerId);
                }
                $oneCarousel["productList"] = $productList;
                if (count($oneCarousel["productList"])) {
                    $this->returnArray["carousel"][] = $oneCarousel;
                }
            } else {
                $banners = [];
                $oneCarousel = [];
                $oneCarousel["type"] = "image";
                $oneCarousel["label"] = $eachCarousel->getTitle();
                if ($eachCarousel->getColorCode()) {
                    $oneCarousel["color"] = $eachCarousel->getColorCode();
                }
                if ($eachCarousel->getFilename()) {
                    $filePath = $this->helper->getUrl("media")."mobikul/carousel/".$eachCarousel->getFilename();
                    $oneCarousel["image"] = $filePath;
                    $oneCarousel["dominantColor"] = $this->helper->getDominantColor($filePath);
                }
                // $oneCarousel["order"] = $eachCarousel->getSortOrder();
                $sellectedBanners = explode(",", $eachCarousel->getImageIds());
                $carouselImageColelction = $this->carouselImageFactory->create()->getCollection()->addFieldToFilter("id", ["in"=>$sellectedBanners]);
                foreach ($carouselImageColelction as $each) {
                    $oneBanner = [];
                    $newUrl = "";
                    $basePath = $this->baseDir.DS.$each->getFilename();
                    if (is_file($basePath)) {
                        $newPath = $this->baseDir.DS."mobikulresized".DS.$this->bannerWidth."x".$this->height.DS.$each->getFilename();
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $this->bannerWidth, $this->height);
                        $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->bannerWidth."x".$this->height.DS.$each->getFilename();
                    }
                    $oneBanner["url"] = $newUrl;
                    $oneBanner["title"] = $each->getTitle();
                    $oneBanner["bannerType"] = $each->getType();
                    $oneBanner["dominantColor"] = $this->helper->getDominantColor($newUrl);
                    if ($each->getType() == "category") {
                        $categoryName = $this->categoryResourceModel->getAttributeRawValue($each->getProCatId(), "name", $this->storeId);
                        if (is_array($categoryName)) {
                            continue;
                        }
                        $oneBanner["id"] = $each->getProCatId();
                        $oneBanner["name"] = $categoryName;
                    } elseif ($each->getType() == "product") {
                        $productName = $this->productResourceModel->getAttributeRawValue($each->getProCatId(), "name", $this->storeId);
                        if (is_array($productName)) {
                            continue;
                        }
                        $oneBanner["id"] = $each->getProCatId();
                        $oneBanner["name"] = $productName;
                    }
                    $banners[] = $oneBanner;
                }
                $oneCarousel["banners"] = $banners;
                if (count($oneCarousel["banners"])) {
                    $this->returnArray["carousel"][] = $oneCarousel;
                }
            }
        }
    }

    public function getAllowedProductIds() {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $hyperLocalhelper = $this->objectManager->create("Webkul\MpHyperLocal\Helper\Data");
        $sellerIds = $hyperLocalhelper->getNearestSellers();
        return $allowedIds = $hyperLocalhelper->getNearestProducts($sellerIds);
    }

    public function execute()
    {
        return parent::execute();
    }
}