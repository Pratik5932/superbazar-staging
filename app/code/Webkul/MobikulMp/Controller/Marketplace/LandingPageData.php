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
 * Class LandingPageData controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class LandingPageData extends AbstractMarketplace
{

    /**
     * Execute function for class LandingPageData
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "LANDINGPAGEDATA".$this->width.$this->mFactor.$this->storeId;
            $cacheString .= $this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment  = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->store->setCurrentCurrencyCode($this->currency);
            $this->iconHeight   = $this->iconWidth = 144 * $this->mFactor;
            $this->bannerWidth  = $this->width * $this->mFactor;
            $this->bannerHeight = ($this->width*(2/3)) * $this->mFactor;
            $this->returnArray["pageLayout"] = $this->marketplaceHelper->getPageLayout();
            if ($this->returnArray["pageLayout"] == 1) {
                $this->getLandingPageDataOne();
            } elseif ($this->returnArray["pageLayout"] == 2) {
                $this->getLandingPageDataTwo();
            } else {
                $this->getLandingPageDataThree();
            }
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
            $this->eTag          = $this->wholeData["eTag"]          ?? "";
            $this->width         = $this->wholeData["width"]         ?? 1000;
            $this->mFactor       = $this->wholeData["mFactor"]       ?? 1;
            $this->storeId       = $this->wholeData["storeId"]       ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->currency      = $this->wholeData['currency'] ?? $this->store->getBaseCurrencyCode();
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

    /**
     * Function to add data of landing page one in return array
     *
     * @return void
     */
    public function getLandingPageDataOne()
    {
        $this->returnArray["layout1"]["displayBanner"] = (bool)$this->marketplaceHelper->getDisplayBanner();
        $this->returnArray["layout1"]["bannerContent"] = $this->marketplaceBlock->getCmsFilterContent(
            $this->marketplaceHelper->getBannerContent()
        );
        $this->returnArray["layout1"]["buttonHeadingLabel"] = $this->marketplaceHelper->getMarketplacebutton();
        $bannerImage = $this->helper->getConfigData("marketplace/landingpage_settings/banner");
        $basePath    = $this->baseDir->getPath("media").DS."marketplace".DS."banner".DS.$bannerImage;
        $newUrl      = "";
        if ($this->fileDriver->isFile($basePath)) {
            $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$this->bannerWidth."x".
            $this->bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
            $this->helperCatalog->resizeNCache($basePath, $newPath, $this->bannerWidth, $this->bannerHeight);
            $newUrl  = $this->helper->getUrl("media")."mobikulresized".DS.$this->bannerWidth."x".
            $this->bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
        }
        $this->returnArray["layout1"]["bannerImage"] = $newUrl;
        $this->returnArray["layout1"]["firstLabel"]  = $this->escaper->escapeHtml(
            $this->marketplaceHelper->getMarketplacelabel1()
        );
        // collecting icon related data ////////////////////////////////////////////
        $this->returnArray["layout1"]["displayIcon"] = (bool)$this->marketplaceHelper->getDisplayIcon();
        if ($this->returnArray["layout1"]["displayIcon"]) {
            $iconUrl  = "";
            $icon1    = $this->helper->getConfigData("marketplace/landingpage_settings/feature_icon1");
            $basePath = $this->baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon1;
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon1;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->iconWidth, $this->iconHeight);
                $iconUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon1;
            }
            $this->returnArray["layout1"]["iconOne"]  = $iconUrl;
            $this->returnArray["layout1"]["labelOne"] = $this->escaper->escapeHtml(
                $this->marketplaceHelper->getIconImageLabel1()
            );
            $icon2    = $this->helper->getConfigData("marketplace/landingpage_settings/feature_icon2");
            $basePath = $this->baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon2;
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon2;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->iconWidth, $this->iconHeight);
                $iconUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon2;
            }
            $this->returnArray["layout1"]["iconTwo"]  = $iconUrl;
            $this->returnArray["layout1"]["labelTwo"] = $this->escaper->escapeHtml(
                $this->marketplaceHelper->getIconImageLabel2()
            );
            $icon3    = $this->helper->getConfigData("marketplace/landingpage_settings/feature_icon3");
            $basePath = $this->baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon3;
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon3;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->iconWidth, $this->iconHeight);
                $iconUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon3;
            }
            $this->returnArray["layout1"]["iconThree"]  = $iconUrl;
            $this->returnArray["layout1"]["labelThree"] = $this->escaper->escapeHtml(
                $this->marketplaceHelper->getIconImageLabel3()
            );
            $icon4    = $this->helper->getConfigData("marketplace/landingpage_settings/feature_icon4");
            $basePath = $this->baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon4;
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon4;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->iconWidth, $this->iconHeight);
                $iconUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon4;
            }
            $this->returnArray["layout1"]["iconFour"]  = $iconUrl;
            $this->returnArray["layout1"]["labelFour"] = $this->escaper->escapeHtml(
                $this->marketplaceHelper->getIconImageLabel4()
            );
        }
        // seller details //////////////////////////////////////////////////////////
        $this->returnArray["layout1"]["showSellers"]    = (bool)$this->marketplaceHelper->getSellerProfileDisplayFlag();
        if ($this->returnArray["layout1"]["showSellers"]) {
            $this->returnArray["layout1"]["secondLabel"] = $this->escaper->escapeHtml(
                $this->marketplaceHelper->getMarketplacelabel2()
            );
            $bestSellersData  = $this->marketplaceBlock->getBestSaleSellers();
            $seller_arr       = $bestSellersData[0];
            $sellerCountArr   = $bestSellersData[2];
            $sellerProfileArr = $bestSellersData[1];
            $i                = 0;
            $sellersData      = [];
            foreach ($seller_arr as $seller_id => $products) {
                $eachSellerData = [];
                $sellerProducts = [];
                $i++;
                if ($i <= 4) {
                    $logo               = "noimage.png";
                    $shoptitle          = "";
                    $profileurl         = 0;
                    $sellerProductCount = 0;
                    $sellerProductCount = $sellerCountArr[$seller_id];
                    if (isset($sellerProfileArr[$seller_id][0])) {
                        $logo       = $sellerProfileArr[$seller_id][0]["logo"];
                        $shoptitle  = $sellerProfileArr[$seller_id][0]["shoptitle"];
                        $profileurl = $sellerProfileArr[$seller_id][0]["profileurl"];
                    }
                    if (!$shoptitle) {
                        $shoptitle = $profileurl;
                    }
                    $basePath = $this->baseDir->getPath("media")."/avatar/".$logo;
                    if ($this->fileDriver->isFile($basePath)) {
                        $newPath = $this->baseDir->getPath(
                            "media"
                        )."/mobikulresized/avatar/".$this->iconWidth."x".$this->iconHeight."/".$logo;
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $this->iconWidth, $this->iconHeight);
                    }
                    $logo = $this->helper->getUrl(
                        "media"
                    )."mobikulresized/avatar/".$this->iconWidth."x".$this->iconHeight."/".$logo;
                    if (isset($products[0])) {
                        $product          = $this->productRepository->getById($products[0]);
                        $sellerProducts[] = $this->helperCatalog->getOneProductRelevantData(
                            $product,
                            $this->storeId,
                            $this->width
                        );
                    }
                    if (isset($products[1])) {
                        $product          = $this->productRepository->getById($products[1]);
                        $sellerProducts[] = $this->helperCatalog->getOneProductRelevantData(
                            $product,
                            $this->storeId,
                            $this->width
                        );
                    }
                    if (isset($products[2])) {
                        $product          = $this->productRepository->getById($products[2]);
                        $sellerProducts[] = $this->helperCatalog->getOneProductRelevantData(
                            $product,
                            $this->storeId,
                            $this->width
                        );
                    }
                
                    $eachSellerData["logo"]         = $logo;
                    $eachSellerData["products"]     = $sellerProducts;
                    $eachSellerData["sellerId"]     = $seller_id;
                    $eachSellerData["shopTitle"]    = $shoptitle;
                    $eachSellerData["productCount"] = __("%1 Products", $sellerProductCount);
                    $sellersData[]                  = $eachSellerData;
                }
            }
            $this->returnArray["layout1"]["sellersData"] = $sellersData;
            $this->returnArray["layout1"]["thirdLabel"]  = $this->escaper->escapeHtml(
                $this->marketplaceHelper->getMarketplacelabel3()
            );
        }
        $this->returnArray["layout1"]["fourthLabel"]  = $this->escaper->escapeHtml(
            $this->marketplaceHelper->getMarketplacelabel4()
        );
        $this->returnArray["layout1"]["aboutContent"] = $this->marketplaceBlock->getCmsFilterContent(
            $this->marketplaceHelper->getMarketplaceprofile()
        );
    }

    /**
     * Function to add data of landing page two in return array
     *
     * @return void
     */
    public function getLandingPageDataTwo()
    {
        $this->returnArray["layout2"]["displayBanner"] = (bool)$this->marketplaceHelper->getDisplayBannerLayout2();
        if ($this->returnArray["layout2"]["displayBanner"]) {
            $this->returnArray["layout2"]["bannerContent"] = $this->marketplaceBlock->getCmsFilterContent(
                $this->marketplaceHelper->getBannerContentLayout2()
            );
            $this->returnArray["layout2"]["buttonLabel"] = $this->marketplaceHelper->getBannerButtonLayout2();
            $bannerImage = $this->helper->getConfigData("marketplace/landingpage_settings/bannerLayout2");
            $basePath    = $this->baseDir->getPath("media").DS."marketplace".DS."banner".DS.$bannerImage;
            $newUrl      = "";
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$this->bannerWidth."x".
                $this->bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->bannerWidth, $this->bannerHeight);
                $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->bannerWidth."x".
                $this->bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
            }
            $this->returnArray["layout2"]["bannerImage"] = $newUrl;
        }
    }

    /**
     * Function to add data of landing page three in return array
     *
     * @return void
     */
    public function getLandingPageDataThree()
    {
        $this->returnArray["layout3"]["displayBanner"] = (bool)$this->marketplaceHelper->getDisplayBannerLayout3();
        if ($this->returnArray["layout3"]["displayBanner"]) {
            $this->returnArray["layout3"]["bannerContent"] = $this->marketplaceBlock->getCmsFilterContent(
                $this->marketplaceHelper->getBannerContentLayout3()
            );
            $bannerImage = $this->helper->getConfigData("marketplace/landingpage_settings/bannerLayout3");
            $basePath    = $this->baseDir->getPath("media").DS."marketplace".DS."banner".DS.$bannerImage;
            $newUrl      = "";
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$this->bannerWidth."x".
                $this->bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->bannerWidth, $this->bannerHeight);
                $newUrl  = $this->helper->getUrl("media")."mobikulresized".DS.$this->bannerWidth."x".
                $this->bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
            }
            $this->returnArray["layout3"]["bannerImage"] = $newUrl;
        }
        $this->returnArray["layout3"]["headingOne"]    = $this->marketplaceHelper->getMarketplacelabel1Layout3();
        $this->returnArray["layout3"]["displayIcon"] = (bool)$this->marketplaceHelper->getDisplayIconLayout3();
        if ($this->returnArray["layout3"]["displayIcon"]) {
            $iconUrl  = "";
            $icon1    = $this->helper->getConfigData("marketplace/landingpage_settings/feature_icon1_layout3");
            $basePath = $this->baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon1;
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon1;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->iconWidth, $this->iconHeight);
                $iconUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon1;
            }
            $this->returnArray["layout3"]["iconOne"]  = $iconUrl;
            $this->returnArray["layout3"]["labelOne"] = $this->escaper->escapeHtml(
                $this->marketplaceHelper->getIconImageLabel1Layout3()
            );
            $icon2    = $this->helper->getConfigData("marketplace/landingpage_settings/feature_icon2_layout3");
            $basePath = $this->baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon2;
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon2;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->iconWidth, $this->iconHeight);
                $iconUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon2;
            }
            $this->returnArray["layout3"]["iconTwo"]  = $iconUrl;
            $this->returnArray["layout3"]["labelTwo"] = $this->escaper->escapeHtml(
                $this->marketplaceHelper->getIconImageLabel2Layout3()
            );
            $icon3    = $this->helper->getConfigData("marketplace/landingpage_settings/feature_icon3_layout3");
            $basePath = $this->baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon3;
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon3;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->iconWidth, $this->iconHeight);
                $iconUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon3;
            }
            $this->returnArray["layout3"]["iconThree"]  = $iconUrl;
            $this->returnArray["layout3"]["labelThree"] = $this->escaper->escapeHtml(
                $this->marketplaceHelper->getIconImageLabel3Layout3()
            );
            $icon4    = $this->helper->getConfigData("marketplace/landingpage_settings/feature_icon4_layout3");
            $basePath = $this->baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon4;
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon4;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->iconWidth, $this->iconHeight);
                $iconUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon4;
            }
            $this->returnArray["layout3"]["iconFour"]  = $iconUrl;
            $this->returnArray["layout3"]["labelFour"] = $this->escaper->escapeHtml(
                $this->marketplaceHelper->getIconImageLabel4Layout3()
            );
            $icon5    = $this->helper->getConfigData("marketplace/landingpage_settings/feature_icon5_layout3");
            $basePath = $this->baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon5;
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir->getPath("media").DS."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon5;
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->iconWidth, $this->iconHeight);
                $iconUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->iconWidth."x".
                $this->iconHeight.DS."marketplace".DS."icon".DS.$icon5;
            }
            $this->returnArray["layout3"]["iconFive"]  = $iconUrl;
            $this->returnArray["layout3"]["labelFive"] = $this->escaper->escapeHtml(
                $this->marketplaceHelper->getIconImageLabel5Layout3()
            );
        }
        $this->returnArray["layout3"]["headingTwo"]      = $this->marketplaceHelper->getMarketplacelabel2Layout3();
        $this->returnArray["layout3"]["headingThree"]    = $this->marketplaceHelper->getMarketplacelabel3Layout3();
    }
}
