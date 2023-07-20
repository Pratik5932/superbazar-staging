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
 * Class ProfileFormData controller
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
class ProfileFormData extends AbstractMarketplace
{
    /**
     * Execute function for class ProfileFormData
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "PROFILEFORMDATA".$this->storeId.$this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment      = $this->emulate->startEnvironmentEmulation($this->storeId);
            $data             = [];
            $logopic          = "";
            $bannerpic        = "";
            $countrylogopic   = "";
            $sellerCollection = $this->seller->getCollection()
                ->addFieldToFilter("seller_id", $this->customerId)
                ->addFieldToFilter("store_id", $this->storeId);
            if (!count($sellerCollection)) {
                $sellerCollection = $this->seller->getCollection()
                    ->addFieldToFilter("seller_id", $this->customerId)
                    ->addFieldToFilter("store_id", 0);
            }
            $customer = $this->customer->load($this->customerId);
            foreach ($sellerCollection as $seller) {
                $logopic        = $seller->getLogoPic();
                $bannerpic      = $seller->getBannerPic();
                $countrylogopic = $seller->getCountryPic();
                if (strlen($bannerpic) <= 0) {
                    $bannerpic  = "banner-image.png";
                }
                if (strlen($logopic) <= 0) {
                    $logopic    = "noimage.png";
                }
            }
            $this->returnArray["showProfileHint"] = (bool)$this->marketplaceHelper->getProfileHintStatus();
            // seller twitter Details /////////////////////////////////////////////////////////////
            if ($seller->getTwActive() == 1) {
                $this->returnArray["isTwitterActive"] = true;
            }
            $this->returnArray["twitterId"]   = $seller->getTwitterId();
            $this->returnArray["twitterHint"] = $this->marketplaceHelper->getProfileHintTw();
            // seller facebook Details //////////////////////////////////////////////////////////
            if ($seller->getFbActive() == 1) {
                $this->returnArray["isFacebookActive"] = true;
            }
            $this->returnArray["facebookId"]   = $seller->getFacebookId();
            $this->returnArray["facebookHint"] = $this->marketplaceHelper->getProfileHintFb();
            // seller instagram Details /////////////////////////////////////////////////////////
            if ($seller->getInstagramActive() == 1) {
                $this->returnArray["isInstagramActive"] = true;
            }
            $this->returnArray["instagramId"]  = $seller->getInstagramId();
            // seller google plus Details ///////////////////////////////////////////////////////
            if ($seller->getGplusActive() == 1) {
                $this->returnArray["isgoogleplusActive"] = true;
            }
            $this->returnArray["googleplusId"] = $seller->getGplusId();
            // seller youtube Details ///////////////////////////////////////////////////////////
            if ($seller->getYoutubeActive() == 1) {
                $this->returnArray["isYoutubeActive"] = true;
            }
            $this->returnArray["youtubeId"]    = $seller->getYoutubeId();
            // seller Vimeo Details /////////////////////////////////////////////////////////////
            if ($seller->getVimeoActive() == 1) {
                $this->returnArray["isVimeoActive"] = true;
            }
            $this->returnArray["vimeoId"] = $seller->getVimeoId();
            // seller Pinterest Details /////////////////////////////////////////////////////////
            if ($seller->getPinterestActive() == 1) {
                $this->returnArray["isPinterestActive"] = true;
            }
            $this->returnArray["pinterestId"] = $seller->getPinterestId();
            // seller Contact Number Details ////////////////////////////////////////////////////
            $this->returnArray["contactNumber"]     = $seller->getContactNumber();
            $this->returnArray["contactNumberHint"] = $this->marketplaceHelper->getProfileHintCn();
            // seller Tax Vat Details ///////////////////////////////////////////////////////////
            $this->returnArray["taxvat"]     = $customer->getTaxvat();
            $this->returnArray["taxvatHint"] = $this->marketplaceHelper->getProductHintTax();
            // seller Background Color Details //////////////////////////////////////////////////
            $this->returnArray["backgroundColor"]     = $seller->getBackgroundWidth();
            $this->returnArray["backgroundColorHint"] = $this->marketplaceHelper->getProfileHintBc();
            // seller Shop Title Details ////////////////////////////////////////////////////////
            $this->returnArray["shopTitle"]     = $seller->getShopTitle();
            $this->returnArray["shopTitleHint"] = $this->marketplaceHelper->getProfileHintShop();
            // seller BannerImage Details ///////////////////////////////////////////////////////
            $this->returnArray["bannerHint"]  = $this->marketplaceHelper->getProfileHintBanner();
            $this->returnArray["bannerImage"] = $this->marketplaceHelper->getMediaUrl()."avatar/".$bannerpic;
            // seller ProfileImage Details //////////////////////////////////////////////////////
            $this->returnArray["profileImageHint"] = $this->marketplaceHelper->getProfileHintLogo();
            $this->returnArray["profileImage"]     = $this->marketplaceHelper->getMediaUrl()."avatar/".$logopic;
            // seller Company Locality Details //////////////////////////////////////////////////
            $this->returnArray["companyLocalityHint"] = $this->marketplaceHelper->getProfileHintLoc();
            $this->returnArray["companyLocality"]     = $seller->getCompanyLocality();
            // seller Company Description Details ///////////////////////////////////////////////
            $this->returnArray["companyDescriptionHint"] = $this->marketplaceHelper->getProfileHintDesc();
            $this->returnArray["companyDescription"]     = $seller->getCompanyDescription();
            // seller Return Policy Details /////////////////////////////////////////////////////
            $this->returnArray["returnPolicyHint"] = $this->marketplaceHelper->getProfileHintReturnPolicy();
            $this->returnArray["returnPolicy"]     = $seller->getReturnPolicy();
            // seller Shipping Policy Details ///////////////////////////////////////////////////
            $this->returnArray["shippingPolicyHint"] = $this->marketplaceHelper->getProfileHintShippingPolicy();
            $this->returnArray["shippingPolicy"]     = $seller->getShippingPolicy();
            // seller Privacy Policy Details ///////////////////////////////////////////////////////////
            $this->returnArray["privacyPolicy"]     = $seller->getPrivacyPolicy();
            // seller Country Details ///////////////////////////////////////////////////////////
            $this->returnArray["countryHint"] = $this->marketplaceHelper->getProfileHintCountry();
            $this->returnArray["country"]     = $seller->getCountryPic();
            $destinations = $this->helper->getConfigData("general/country/destinations");
            $destinations = !empty($destinations) ? explode(",", $destinations) : [];
            $countryCollection = $this->countryCollectionFactory->create()->loadByStore()
                ->setForegroundCountries($destinations)
                ->toOptionArray();
            $countryList = [];
            foreach ($countryCollection as $country) {
                $countryList[] = $country;
            }
            $this->returnArray["countryList"]  = $countryList;
            $this->returnArray["flagImageUrl"] = $this->viewTemplate->getViewFileUrl(
                "Webkul_Marketplace::images/country/countryflags/"
            );
            // seller Meta Keyword Details //////////////////////////////////////////////////////
            $this->returnArray["metaKeywordHint"] = $this->marketplaceHelper->getProfileHintMeta();
            $this->returnArray["metaKeyword"]     = $seller->getMetaKeyword();
            // seller Meta Description Details //////////////////////////////////////////////////
            $this->returnArray["metaDescriptionHint"] = $this->marketplaceHelper->getProfileHintMetaDesc();
            $this->returnArray["metaDescription"]     = $seller->getMetaDescription();
            // seller Payment Details ///////////////////////////////////////////////////////////
            $this->returnArray["paymentDetailsHint"] = $this->marketplaceHelper->getProfileHintBank();
            $this->returnArray["paymentDetails"]     = $seller->getPaymentSource();
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
            $this->eTag          = $this->wholeData["eTag"]          ?? 0;
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
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
