<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMp
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

    namespace Webkul\MobikulMpHyperLocal\Controller\Marketplace;

class SellerList extends \Webkul\MobikulMp\Controller\Marketplace\SellerList {
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "SELLERLIST".$this->storeId.$this->width.$this->mFactor.$this->searchQuery;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment  = $this->emulate->startEnvironmentEmulation($this->storeId);
            $Iconheight   = $IconWidth = 144 * $this->mFactor;
            $bannerWidth  = $this->width * $this->mFactor;
            $bannerHeight = ($this->width/2) * $this->mFactor;
            $this->returnArray["displayBanner"] = (bool)$this->marketplaceHelper->getDisplayBanner();
            $this->returnArray["bannerContent"] = $this->marketplaceBlock->getCmsFilterContent($this->marketplaceHelper->getBannerContent());
            $this->returnArray["buttonNHeadingLabel"] = $this->marketplaceHelper->getMarketplacebutton();
            $bannerImage = $this->helper->getConfigData("marketplace/landingpage_settings/banner");
            $basePath    = $this->baseDir->getPath("media").DS."marketplace".DS."banner".DS.$bannerImage;
            $newUrl      = "";
            if (is_file($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$bannerWidth."x".$bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".$bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
            }
            $this->returnArray["bannerImage"] = $newUrl;
            $this->returnArray["topLabel"]    = $this->viewTemplate->escapeHtml($this->marketplaceHelper->getSellerlisttopLabel());
            $sellerArr         = [];
            $sellerProductColl = $this->marketplaceProduct
                ->getCollection()
                ->addFieldToFilter("status", 1)
                ->addFieldToSelect("seller_id")
                ->distinct(true);
            $sellerArr = $sellerProductColl->getAllSellerIds();
            $storeCollection = $this->sellerlistCollectionFactory
                ->create()
                ->addFieldToSelect("*")
                ->addFieldToFilter("seller_id", ["in"=>$sellerArr])
                ->addFieldToFilter("is_seller", 1)
                ->addFieldToFilter("store_id", $this->storeId)
                ->setOrder("entity_id", "desc");
            $storeSellerIDs     = $storeCollection->getAllIds();
            $storeMainSellerIDs = $storeCollection->getAllSellerIds();
            $sellerArr = array_diff($sellerArr, $storeMainSellerIDs);
            $adminStoreCollection = $this->sellerlistCollectionFactory
                ->create()
                ->addFieldToSelect("*")
                ->addFieldToFilter("seller_id", ["in"=>$sellerArr]);
            if (!empty($storeSellerIDs)) {
                $adminStoreCollection->addFieldToFilter("entity_id", ["nin"=>$storeSellerIDs]);
            }
            $adminStoreCollection->addFieldToFilter("is_seller", ["eq"=>1])
                ->addFieldToFilter("store_id", 0)
                ->setOrder("entity_id", "desc");
            $adminStoreSellerIDs = $adminStoreCollection->getAllIds();
            $allSellerIDs = array_merge($storeSellerIDs, $adminStoreSellerIDs);
            $collection = $this->sellerlistCollectionFactory
                ->create()
                ->addFieldToSelect("*")
                ->addFieldToFilter("entity_id", ["in"=>$allSellerIDs])
                ->setOrder("entity_id", "desc");
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $localSellerIds = $objectManager->create("\Webkul\MobikulMpHyperLocal\Helper\Data")->getNearestSellers();
            $collection->addFieldToFilter('seller_id', ['in' => $localSellerIds]);
            if ($this->searchQuery) {
                $collection->addFieldToFilter(
                    ["shop_title", "shop_url"],
                    [
                        ["like"=>"%".$this->searchQuery."%"],
                        ["like"=>"%".$this->searchQuery."%"]
                    ]
                );
            }
            $websiteId = $this->marketplaceHelper->getWebsiteId();
            $joinTable = $this->sellerCollection->getTable("customer_grid_flat");
            $collection->getSelect()->join($joinTable." as cgf", "main_table.seller_id=cgf.entity_id AND website_id=".$websiteId);
            
            $sellersData            = [];
            foreach ($collection as $seller) {
                $eachSellerData     = [];
                $sellerId           = $seller->getSellerId();
                $sellerProductCount = 0;
                $profileurl         = $seller->getShopUrl();
                $shoptitle          = "";
                $sellerProductCount = $this->marketplaceHelper->getSellerProCount($sellerId);
                $shoptitle          = $seller->getShopTitle();
                $companyDescription = $seller->getCompanyDescription();
                $companyLocality    = $seller->getCompanyLocality();

                $logo               = $seller->getLogoPic() == "" ? "noimage.png" : $seller->getLogoPic();
                if (!$shoptitle) {
                    $shoptitle = $profileurl;
                }
                $basePath = $this->baseDir->getPath("media")."/avatar/".$logo;
                if (is_file($basePath)) {
                    $newPath = $this->baseDir->getPath("media")."/mobikulresized/avatar/".$IconWidth."x".$Iconheight."/".$logo;
                    $this->helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                }
                $logo = $this->helper->getUrl("media")."mobikulresized/avatar/".$IconWidth."x".$Iconheight."/".$logo;
                $eachSellerData["logo"]         = $logo;
                $eachSellerData["sellerId"]     = $sellerId;
                $eachSellerData["shoptitle"]    = $shoptitle;
                $eachSellerData["companyDescription"] = $companyDescription;
                $eachSellerData["companyLocality"] = $companyLocality;
                $eachSellerData["bannerImage"] = "";
                $eachSellerData["productCount"] = __("%1 Products", $sellerProductCount);
                $sellersData[]                  = $eachSellerData;
            }
            $this->returnArray["sellersData"] = $sellersData;
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
}