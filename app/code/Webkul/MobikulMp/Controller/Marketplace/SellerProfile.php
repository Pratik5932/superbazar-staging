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
 * Class SellerProfile controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class SellerProfile extends AbstractMarketplace
{
    /**
     * Execute function for class SellerProfile
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "SELLERPROFILE".$this->storeId.$this->width.$this->sellerId;
            $cacheString .= $this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->store->setCurrentCurrencyCode($this->currency);
            $sellerCollection = $this->seller->getCollection()
                ->addFieldToFilter("is_seller", 1)
                ->addFieldToFilter("seller_id", $this->sellerId)
                ->addFieldToFilter("store_id", $this->storeId);
            if (!count($sellerCollection)) {
                $sellerCollection = $this->seller->getCollection()
                    ->addFieldToFilter("is_seller", 1)
                    ->addFieldToFilter("seller_id", $this->sellerId)
                    ->addFieldToFilter("store_id", 0);
            }
            if ($sellerCollection->getSize() > 0) {
                foreach ($sellerCollection as $eachSeller) {
                    $seller = $eachSeller;
                }
                $logopic    = $seller->getLogoPic();
                $bannerpic  = $seller->getBannerPic();
                if (strlen($bannerpic) <= 0) {
                    $bannerpic = "banner-image.png";
                }
                if (strlen($logopic) <= 0) {
                    $logopic   = "noimage.png";
                }
                $this->returnArray["bannerImage"]     = $this->marketplaceHelper->getMediaUrl()."avatar/".$bannerpic;
                $this->returnArray["profileImage"]    = $this->marketplaceHelper->getMediaUrl()."avatar/".$logopic;
                $this->returnArray["backgroundColor"] = $seller->getBackgroundWidth();
                $shopUrl   = $this->viewTemplate->escapeHtml($seller->getShopUrl());
                $shopTitle = $this->viewTemplate->escapeHtml($seller->getShopTitle());
                if (!$shopTitle) {
                    $shopTitle = $shopUrl;
                }
                $this->returnArray["shopUrl"]   = $shopUrl;
                $this->returnArray["sellerId"]  = $seller->getSellerId();
                $this->returnArray["location"]  = $this->viewTemplate->escapeHtml($seller->getCompanyLocality());
                $this->returnArray["shopTitle"] = $shopTitle;
                if ($seller->getTwActive() == 1) {
                    $this->returnArray["isTwitterActive"] = true;
                }
                $this->returnArray["twitterId"] = $seller->getTwitterId();
                // seller facebook Details /////////////////////////////////////////////////////////////////////////////
                if ($seller->getFbActive() == 1) {
                    $this->returnArray["isFacebookActive"] = true;
                }
                $this->returnArray["facebookId"] = $seller->getFacebookId();
                // seller instagram Details ////////////////////////////////////////////////////////////////////////////
                if ($seller->getInstagramActive() == 1) {
                    $this->returnArray["isInstagramActive"] = true;
                }
                $this->returnArray["instagramId"] = $seller->getInstagramId();
                // seller google plus Details //////////////////////////////////////////////////////////////////////////
                if ($seller->getGplusActive() == 1) {
                    $this->returnArray["isgoogleplusActive"] = true;
                }
                $this->returnArray["googleplusId"] = $seller->getGplusId();
                // seller youtube Details /////////////////////////////////////////////////////////////////////////////
                if ($seller->getYoutubeActive() == 1) {
                    $this->returnArray["isYoutubeActive"] = true;
                }
                $this->returnArray["youtubeId"] = $seller->getYoutubeId();
                // seller Vimeo Details ////////////////////////////////////////////////////////////////////////////////
                if ($seller->getVimeoActive() == 1) {
                    $this->returnArray["isVimeoActive"] = true;
                }
                $this->returnArray["vimeoId"] = $seller->getVimeoId();
                // seller Pinterest Details ////////////////////////////////////////////////////////////////////////////
                if ($seller->getPinterestActive() == 1) {
                    $this->returnArray["isPinterestActive"] = true;
                }
                $this->returnArray["orderCount"]     = $this->marketplaceOrderhelper->getSellerOrders($this->sellerId);
                $this->returnArray["pinterestId"]    = $seller->getPinterestId();
                $this->returnArray["description"]    = $seller->getCompanyDescription();
                $this->returnArray["productCount"]   = $this->marketplaceHelper->getSellerProCount($this->sellerId);
                $this->returnArray["returnPolicy"]   = $seller->getReturnPolicy();
                $this->returnArray["averageRating"]  = $this->marketplaceHelper->getSelleRating($this->sellerId);
                $this->returnArray["shippingPolicy"] = $seller->getShippingPolicy();
                $this->returnArray["privacyPolicy"] = $seller->getPrivacyPolicy();
                // getting recently added products /////////////////////////////////////////////////////////////////////
                $catalogProductWebsite = $this->marketplaceProductResource->getTable("catalog_product_website");
                $websiteId = $this->marketplaceHelper->getWebsiteId();
                $querydata = $this->marketplaceProduct->getCollection()
                    ->addFieldToFilter("seller_id", $this->sellerId)
                    ->addFieldToFilter("status", ["neq"=>2])
                    ->addFieldToSelect("mageproduct_id")
                    ->setOrder("mageproduct_id");
                $productCollection = $this->productModel->getCollection()
                    ->addAttributeToSelect("*")
                    ->addAttributeToFilter("entity_id", ["in"=>$querydata->getAllIds()])
                    ->addAttributeToFilter("visibility", ["in"=>[4]])
                    ->addAttributeToFilter("status", 1);
                if ($websiteId) {
                    $productCollection->getSelect()->join(["cpw"=>$catalogProductWebsite], "cpw.product_id=e.entity_id")
                        ->where("cpw.website_id=".$websiteId);
                }
                $productCollection->setPageSize(4)->setCurPage(1)->setOrder("entity_id");
                $recentProductList = [];
                foreach ($productCollection as $eachProduct) {
                    $recentProductList[] = $this->helperCatalog->getOneProductRelevantData(
                        $eachProduct,
                        $this->storeId,
                        $this->width,
                        $this->customerId
                    );
                }
                $this->returnArray["recentProductList"] = $recentProductList;
                // getting rating data for seller //////////////////////////////////////////////////////////////////////
                $feeds = $this->marketplaceHelper->getFeedTotal($this->sellerId);
                if (empty($feeds["feed_price"])) {
                    $feeds["feed_price"] = 0;
                }
                if (empty($feeds["feed_value"])) {
                    $feeds["feed_value"] = 0;
                }
                if (empty($feeds["feed_quality"])) {
                    $feeds["feed_quality"] = 0;
                }
                $this->returnArray["feedbackCount"] = $feeds["feedcount"];
                // price rating ////////////////////////////////////////////////////////////////////////////////////////
                $this->returnArray["price5Star"]  = $feeds["price_star_5"];
                $this->returnArray["price4Star"]  = $feeds["price_star_4"];
                $this->returnArray["price3Star"]  = $feeds["price_star_3"];
                $this->returnArray["price2Star"]  = $feeds["price_star_2"];
                $this->returnArray["price1Star"]  = $feeds["price_star_1"];
                $this->returnArray["averagePriceRating"] = round(($feeds["price"]/20), 1, PHP_ROUND_HALF_UP);
                // value rating ////////////////////////////////////////////////////////////////////////////////////////
                $this->returnArray["value5Star"]  = $feeds["value_star_5"];
                $this->returnArray["value4Star"]  = $feeds["value_star_4"];
                $this->returnArray["value3Star"]  = $feeds["value_star_3"];
                $this->returnArray["value2Star"]  = $feeds["value_star_2"];
                $this->returnArray["value1Star"]  = $feeds["value_star_1"];
                $this->returnArray["averageValueRating"] = round(($feeds["value"]/20), 1, PHP_ROUND_HALF_UP);
                // quality rating //////////////////////////////////////////////////////////////////////////////////////
                $this->returnArray["quality5Star"] = $feeds["quality_star_5"];
                $this->returnArray["quality4Star"] = $feeds["quality_star_4"];
                $this->returnArray["quality3Star"] = $feeds["quality_star_3"];
                $this->returnArray["quality2Star"] = $feeds["quality_star_2"];
                $this->returnArray["quality1Star"] = $feeds["quality_star_1"];
                $this->returnArray["averageQualityRating"] = round(($feeds["quality"]/20), 1, PHP_ROUND_HALF_UP);
                // getting review list /////////////////////////////////////////////////////////////////////////////////
                $reviewCollection = $this->reviewModel->getCollection()
                    ->addFieldToFilter("status", ["neq"=>0])
                    ->addFieldToFilter("seller_id", $this->sellerId)
                    ->setOrder("entity_id", "DESC")
                    ->setPageSize(4)
                    ->setCurPage(1);
                $reviewList = [];
                foreach ($reviewCollection as $each) {
                    $eachReview                = [];
                    $eachReview["date"]        = date("M d, Y", strtotime($each["created_at"]));
                    $eachReview["summary"]     = $each["feed_summary"];
                    $eachReview["userName"]    = $this->customer->load($each["buyer_id"])->getName();
                    $eachReview["feedPrice"]   = $each["feed_price"];
                    $eachReview["feedValue"]   = $each["feed_value"];
                    $eachReview["feedQuality"] = $each["feed_quality"];
                    $eachReview["description"] = $each["feed_review"];
                    $reviewList[]              = $eachReview;
                }
                $this->returnArray["reviewList"] = $reviewList;

                $reviewStatus = $this->helper->getConfigData("marketplace/review_settings/review_status");
                if ($reviewStatus) {
                    $productCollection = $this->marketplaceHelper->getSellerProductCollection($this->sellerId);
                    $sellerProductIds = $productCollection->getAllIds();
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $orderCollection = $objectManager->create(
                        \Magento\Sales\Model\ResourceModel\Order\Collection::class
                    );
                    $customerOrders = $orderCollection
                        ->addAttributeToFilter('customer_id', $this->customerId)
                        ->addAttributeToFilter('status', 'complete')
                        ->load();
                    $customerProductIds = [];
                    foreach ($customerOrders as $order) {
                        foreach ($order->getAllItems() as $item) {
                            $customerProductIds[] = $item->getProductId();
                        }
                    }
                    $customerProductIds = array_unique($customerProductIds, SORT_NUMERIC);
                    $showReview = array_intersect($sellerProductIds, $customerProductIds);
                    if (count($showReview) > 0) {
                        $this->returnArray["showReview"] = true;
                    } else {
                        $this->returnArray["showReview"] = false;
                    }
                } else {
                    $this->returnArray["showReview"] = true;
                }

                $this->returnArray['reportData']["showReportSeller"] = (bool) $this->helper->getConfigData(
                    "marketplace/seller_flag/status"
                );
                $this->returnArray['reportData']["guestCanReport"] = (bool) $this->helper->getConfigData(
                    "marketplace/seller_flag/guest_status"
                );
                $this->returnArray['reportData']["showReportReason"] = (bool) $this->helper->getConfigData(
                    "marketplace/seller_flag/reason"
                );
                $this->returnArray['reportData']["sellerReportLabel"] = $this->helper->getConfigData(
                    "marketplace/seller_flag/seller_flag_label"
                );
                if ($this->returnArray['reportData']["showReportReason"]) {
                    $this->returnArray['reportData']["showReportOtherReason"] = (bool) $this->helper->getConfigData(
                        "marketplace/seller_flag/other_reason"
                    );
                    $this->returnArray['reportData']["sellerOtherReportLabel"] = $this->helper->getConfigData(
                        "marketplace/seller_flag/other_reason_label"
                    );
                    // $reasons = $this->sellerFlagReason->getCollection();
                    // $this->returnArray['reportData']["sellerFlagReasons"] = $reasons->getData();
                }

                $this->returnArray["success"] = true;
                $this->emulate->stopEnvironmentEmulation($environment);
                $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
                $this->checkNGenerateEtag($cacheString);
                return $this->getJsonResponse($this->returnArray);
            } else {
                $this->returnArray["message"] = __("Invalid Seller");
                $this->returnArray["success"] = false;
                $this->helper->printLog($this->returnArray, 1);
                return $this->getJsonResponse($this->returnArray);
            }
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
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->width         = $this->wholeData["width"]         ?? 1000;
            $this->sellerId      = $this->wholeData["sellerId"]      ?? 0;
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
