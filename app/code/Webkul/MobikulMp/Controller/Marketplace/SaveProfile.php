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
 * Class SaveProfile controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class SaveProfile extends AbstractMarketplace
{
    /**
     * Execute function for class SaveProfile
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $errors      = $this->validateprofiledata($this->wholeData);
            if (!$errors) {
                $id = 0;
                $collection = $this->seller->getCollection()
                    ->addFieldToFilter("seller_id", $this->customerId)
                    ->addFieldToFilter("store_id", $this->storeId);
                foreach ($collection as $value) {
                    $id = $value->getId();
                }
                $sellerDefaultData = [];
                if (!count($collection)) {
                    $defaultCollection = $this->seller->getCollection()
                        ->addFieldToFilter("seller_id", $this->customerId)
                        ->addFieldToFilter("store_id", 0);
                    foreach ($defaultCollection as $eachSeller) {
                        $defaultId = $eachSeller->getId();
                        $sellerDefaultData = $eachSeller->getData();
                        $this->helper->printLog($sellerDefaultData, 1);
                    }
                }
                $seller = $this->seller->load($id);
                $profileData = [
                    "taxvat"              => $this->taxvat,
                    "gplus_id"            => $this->gplusId,
                    "vimeo_id"            => $this->vimeoId,
                    "tw_active"           => $this->twActive,
                    "fb_active"           => $this->fbActive,
                    "twitter_id"          => $this->twitterId,
                    "youtube_id"          => $this->youtubeId,
                    "shop_title"          => $this->shopTitle,
                    "facebook_id"         => $this->facebookId,
                    "country_pic"         => $this->country,
                    "instagram_id"        => $this->instagramId,
                    "pinterest_id"        => $this->pinterestId,
                    "meta_keyword"        => $this->metaKeyword,
                    "gplus_active"        => $this->gplusActive,
                    "vimeo_active"        => $this->vimeoActive,
                    "return_policy"       => $this->returnPolicy,
                    "youtube_active"      => $this->youtubeActive,
                    "contact_number"      => $this->contactNumber,
                    "payment_source"      => $this->paymentDetails,
                    "shipping_policy"     => $this->shippingPolicy,
                    "privacy_policy"      => $this->privacyPolicy,
                    "company_locality"    => $this->companyLocality,
                    "instagram_active"    => $this->instagramActive,
                    "pinterest_active"    => $this->pinterestActive,
                    "background_width"    => $this->backgroundColor,
                    "meta_description"    => $this->metaDescription,
                    "company_description" => $this->companyDescription
                ];
                if (isset($sellerDefaultData['shop_url'])) {
                    $profileData['shop_url'] = $sellerDefaultData['shop_url'];
                }
                if (isset($sellerDefaultData['shop_url'])) {
                    $profileData['is_seller'] = $sellerDefaultData['is_seller'];
                }
                if (isset($sellerDefaultData['seller_id'])) {
                    $profileData['seller_id'] = $sellerDefaultData['seller_id'];
                }
                $seller->addData($profileData);
                if (!$id) {
                    $seller->setCreatedAt($this->date->gmtDate());
                }
                $seller->setUpdatedAt($this->date->gmtDate());
                $seller->save();
                if ($this->companyDescription) {
                    $companyDescription = str_replace("script", "", $this->companyDescription);
                    $seller->setCompanyDescription($companyDescription);
                }
                if ($this->returnPolicy) {
                    $returnPolicy = str_replace("script", "", $this->returnPolicy);
                    $seller->setReturnPolicy($returnPolicy);
                }
                if ($this->shippingPolicy) {
                    $shippingPolicy = str_replace("script", "", $this->shippingPolicy);
                    $seller->setShippingPolicy($shippingPolicy);
                }
                $seller->setMetaDescription($this->metaDescription);
                if ($this->taxvat) {
                    $customer = $this->customer->load($this->customerId);
                    $customer->setTaxvat($this->taxvat);
                    $customer->setId($this->customerId)->save();
                }
                $target = $this->mediaDirectory->getAbsolutePath("avatar/");
                try {
                    $uploader = $this->fileUploaderFactory->create(["fileId"=>"banner_pic"]);
                    $uploader->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                    $uploader->setAllowRenameFiles(true);
                    $result = $uploader->save($target);
                    if ($result["file"]) {
                        $seller->setBannerPic($result["file"]);
                    }
                } catch (\Exception $e) {
                    if ($e->getMessage() != "The file was not uploaded.") {
                        $errors[] = $e->getMessage();
                    }
                }
                try {
                    $uploaderLogo = $this->fileUploaderFactory->create(["fileId"=>"logo_pic"]);
                    $uploaderLogo->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                    $uploaderLogo->setAllowRenameFiles(true);
                    $resultLogo = $uploaderLogo->save($target);
                    if ($resultLogo["file"]) {
                        $seller->setLogoPic($resultLogo["file"]);
                    }
                } catch (\Exception $e) {
                    if ($e->getMessage() != "The file was not uploaded.") {
                        $errors[] = $e->getMessage();
                    }
                }
                $seller->setCountryPic($this->country);
                $seller->setStoreId($this->storeId);
                $errorInFileUpload = false;
                $target = $this->mediaDirectory->getAbsolutePath("avatar/");
                $files  = $this->getRequest()->getFiles();
                if (isset($files["banner"])) {
                    try {
                        $uploader = $this->fileUploaderFactory->create(["fileId" => "banner"]);
                        $uploader->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                        $uploader->setAllowRenameFiles(true);
                        $result = $uploader->save($target);
                        if ($result["file"]) {
                            $seller->setBannerPic($result["file"]);
                        }
                    } catch (\Exception $e) {
                        if ($e->getMessage() != "The file was not uploaded.") {
                            $errorInFileUpload = true;
                            $this->returnArray["message"] = $e->getMessage();
                        }
                    }
                }
                if (isset($files["logo"])) {
                    try {
                        $uploader = $this->fileUploaderFactory->create(["fileId"=>"logo"]);
                        $uploader->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                        $uploader->setAllowRenameFiles(true);
                        $result = $uploader->save($target);
                        if ($result["file"]) {
                            $seller->setLogoPic($result["file"]);
                        }
                    } catch (\Exception $e) {
                        if ($e->getMessage() != "The file was not uploaded.") {
                            $errorInFileUpload = true;
                            $this->returnArray["message"] = $e->getMessage();
                        }
                    }
                }
                $seller->save();
                $this->returnArray["success"] = true;
                if ($errorInFileUpload) {
                    $this->returnArray["message"] = __("Profile information was successfully saved, except image(s).");
                } else {
                    $this->returnArray["message"] = __("Profile information was successfully saved.");
                }
            } else {
                $this->returnArray["message"] = implode(", ", $errors);
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function to validate profile data
     *
     * @param array $fields fields
     *
     * @return array
     */
    protected function validateprofiledata(&$fields)
    {
        $errors = [];
        $data   = [];
        foreach ($fields as $code => $value) {
            switch ($code):
                case "twitterId":
                    if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                        $errors[] = __(
                            "Twitterid cannot contain space and special characters, 
                            allowed special carecters are @,#,_,-"
                        );
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case "facebookId":
                    if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                        $errors[] = __(
                            "Facebookid cannot contain space and special characters, 
                            allowed special carecters are @,#,_,-"
                        );
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case "instagramId":
                    if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                        $errors[] = __(
                            "Instagram ID cannot contain space and special characters, 
                            allowed special carecters are @,#,_,-"
                        );
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case "gplusId":
                    if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                        $errors[] = __(
                            "Google Plus ID cannot contain space and special characters, 
                            allowed special carecters are @,#,_,-"
                        );
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case "youtubeId":
                    if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                        $errors[] = __(
                            "Youtube ID cannot contain space and special characters, 
                            allowed special carecters are @,#,_,-"
                        );
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case "vimeoId":
                    if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                        $errors[] = __(
                            "Vimeo ID cannot contain space and special characters, 
                            allowed special carecters are @,#,_,-"
                        );
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case "pinterestId":
                    if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                        $errors[] = __(
                            "Pinterest ID cannot contain space and special characters, 
                            allowed special carecters are @,#,_,-"
                        );
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case "taxvat":
                    if (trim($value) != "" && preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)) {
                        $errors[] = __("Tax/VAT Number cannot contain space and special characters");
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case "shopTitle":
                    $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                    $fields[$code] = $value;
                    break;
                case "contactNumber":
                    $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                    $fields[$code] = $value;
                    break;
                case "companyLocality":
                    $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                    $fields[$code] = $value;
                    break;
                case "companyDescription":
                    $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                    $value = $this->marketplaceHelper->validateXssString($value);
                    $fields[$code] = $value;
                    break;
                case "metaKeyword":
                    $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                    $value = $this->marketplaceHelper->validateXssString($value);
                    $fields[$code] = $value;
                    break;
                case "metaDescription":
                    $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                    $value = $this->marketplaceHelper->validateXssString($value);
                    $fields[$code] = $value;
                    break;
                case "shippingPolicy":
                    $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                    $value = $this->marketplaceHelper->validateXssString($value);
                    $fields[$code] = $value;
                    break;

                case "returnPolicy":
                    $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                    $value = $this->marketplaceHelper->validateXssString($value);
                    $fields[$code] = $value;
                    break;
                case "backgroundColor":
                    if (trim($value) != "" && strlen($value) != 6 && substr($value, 0, 1) != "#") {
                        $errors[] = __("Invalid Background Color");
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
            endswitch;
        }
        return $errors;
    }

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId            = $this->wholeData["storeId"]            ?? 0;
            $this->taxvat             = $this->wholeData["taxvat"]             ?? "";
            $this->gplusId            = $this->wholeData["gplusId"]            ?? "";
            $this->country            = $this->wholeData["country"]            ?? "";
            $this->vimeoId            = $this->wholeData["vimeoId"]            ?? "";
            $this->twActive           = $this->wholeData["twActive"]           ?? 0;
            $this->fbActive           = $this->wholeData["fbActive"]           ?? 0;
            $this->twitterId          = $this->wholeData["twitterId"]          ?? "";
            $this->youtubeId          = $this->wholeData["youtubeId"]          ?? "";
            $this->shopTitle          = $this->wholeData["shopTitle"]          ?? "";
            $this->facebookId         = $this->wholeData["facebookId"]         ?? "";
            $this->instagramId        = $this->wholeData["instagramId"]        ?? "";
            $this->pinterestId        = $this->wholeData["pinterestId"]        ?? "";
            $this->metaKeyword        = $this->wholeData["metaKeyword"]        ?? "";
            $this->gplusActive        = $this->wholeData["gplusActive"]        ?? 0;
            $this->vimeoActive        = $this->wholeData["vimeoActive"]        ?? 0;
            $this->returnPolicy       = $this->wholeData["returnPolicy"]       ?? "";
            $this->contactNumber      = $this->wholeData["contactNumber"]      ?? "";
            $this->youtubeActive      = $this->wholeData["youtubeActive"]      ?? 0;
            $this->shippingPolicy     = $this->wholeData["shippingPolicy"]     ?? "";
            $this->privacyPolicy      = $this->wholeData["privacyPolicy"]     ?? "";
            $this->paymentDetails     = $this->wholeData["paymentDetails"]     ?? "";
            $this->companyLocality    = $this->wholeData["companyLocality"]    ?? "";
            $this->backgroundColor    = $this->wholeData["backgroundColor"]    ?? "";
            $this->metaDescription    = $this->wholeData["metaDescription"]    ?? "";
            $this->instagramActive    = $this->wholeData["instagramActive"]    ?? 0;
            $this->pinterestActive    = $this->wholeData["pinterestActive"]    ?? 0;
            $this->companyDescription = $this->wholeData["companyDescription"] ?? "";
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
