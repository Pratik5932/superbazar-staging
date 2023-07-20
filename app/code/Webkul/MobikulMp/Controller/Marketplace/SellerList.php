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
 * Class SellerList controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class SellerList extends AbstractMarketplace
{
    /**
     * Execute function for class SellerList
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
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
            $this->returnArray["bannerContent"] = $this->marketplaceBlock->getCmsFilterContent(
                $this->marketplaceHelper->getBannerContent()
            );
            $this->returnArray["buttonNHeadingLabel"] = $this->marketplaceHelper->getMarketplacebutton();
            $this->returnArray["bottomLabel"] = $this->marketplaceHelper->getSellerlistbottomLabel();
            $bannerImage = $this->helper->getConfigData("marketplace/landingpage_settings/banner");
            $basePath    = $this->baseDir->getPath("media").DS."marketplace".DS."banner".DS.$bannerImage;
            $newUrl      = "";
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$bannerWidth."x".
                $bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".
                $bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
            }
            $this->returnArray["bannerImage"] = $newUrl;
            $this->returnArray["topLabel"]    = $this->viewTemplate->escapeHtml(
                $this->marketplaceHelper->getSellerlisttopLabel()
            );
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
            $collection->getSelect()->join(
                $joinTable." as cgf",
                "main_table.seller_id=cgf.entity_id AND website_id=".$websiteId
            );
            
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
                $banner             = $seller->getBannerPic() == "" ? "banner-image.png" : $seller->getBannerPic();
                if (!$shoptitle) {
                    $shoptitle = $profileurl;
                }
                $logoBasePath = $this->baseDir->getPath("media")."/avatar/".$logo;
                if ($this->fileDriver->isFile($logoBasePath)) {
                    $newPath = $this->baseDir->getPath(
                        "media"
                    )."/mobikulresized/avatar/".$IconWidth."x".$Iconheight."/".$logo;
                    $this->helperCatalog->resizeNCache($logoBasePath, $newPath, $IconWidth, $Iconheight);
                }
                $bannerBasePath = $this->baseDir->getPath("media")."/avatar/".$banner;
                if ($this->fileDriver->isFile($bannerBasePath)) {
                    $newPath = $this->baseDir->getPath(
                        "media"
                    )."/mobikulresized/avatar/".$bannerWidth."x".$bannerHeight."/".$banner;
                    $this->helperCatalog->resizeNCache($bannerBasePath, $newPath, $IconWidth, $Iconheight);
                }
                $logo = $this->helper->getUrl("media")."mobikulresized/avatar/".$IconWidth."x".$Iconheight."/".$logo;
                $banner = $this->helper->getUrl("media")."mobikulresized/avatar/".$bannerWidth."x".
                    $bannerHeight."/".$banner;
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

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag        = $this->wholeData["eTag"]        ?? "";
            $this->storeId     = $this->wholeData["storeId"]     ?? 0;
            $this->width       = $this->wholeData["width"]       ?? 1000;
            $this->mFactor     = $this->wholeData["mFactor"]     ?? 1;
            $this->searchQuery = $this->wholeData["searchQuery"] ?? "";
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
