<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulB2B
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\MobikulMp\Controller\Seller;

/**
 * Abstract Class AbstractSeller for adding all the required dependencies used for customer features.
 *
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */

class RewriteUrlPost extends AbstractSeller
{
    /**
     * Seller prodile Url
     *
     * @var String
     */
    private $profileUrl;

    public function execute()
    {

        try {
            $this->verifyRequest();
            $cacheString = strtoupper("RewritePostUrl").$this->storeId.$this->collectionRequestUrl;
            $cacheString .= $this->profileRequestUrl.$this->reviewRequestUrl.$this->locationRequestUrl;
            $cacheString .= $this->policyRequestUrl.$this->customerToken;
            if ($this->mobikulHelper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
                        
            $sellerId = $this->customerId;
            $collection = $this->sellerModel
            ->getCollection()
            ->addFieldToFilter('seller_id', $sellerId);
            foreach ($collection as $value) {
                $profileurl = $value->getShopUrl();
            }

            $getCurrentStoreId = $this->mpHelper->getCurrentStoreId();

            if ($this->profileRequestUrl) {
                $sourceUrl = 'marketplace/seller/profile/shop/'.$profileurl;
                /*
                * Check if already rexist in url rewrite model
                */
                $urlId = 0;
                $profileRequestUrl = '';
                $urlCollectionData = $this->urlRewriteFactory->create()
                ->getCollection()
                ->addFieldToFilter('target_path', $sourceUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
                foreach ($urlCollectionData as $value) {
                    $urlId = $value->getId();
                    $profileRequestUrl = $value->getRequestPath();
                }
                if ($profileRequestUrl != $this->profileRequestUrl) {
                    $idPath = rand(1, 100000);
                    $this->urlRewriteFactory->create()
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceUrl)
                    ->setRequestPath($this->profileRequestUrl)
                    ->save();
                }
            }
            if ($this->collectionRequestUrl) {
                $sourceUrl = 'marketplace/seller/collection/shop/'.$profileurl;
                /*
                * Check if already rexist in url rewrite model
                */
                $urlId = 0;
                $collectionRequestUrl = '';
                $urlCollectionData = $this->urlRewriteFactory->create()
                ->getCollection()
                ->addFieldToFilter('target_path', $sourceUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
                foreach ($urlCollectionData as $value) {
                    $urlId = $value->getId();
                    $collectionRequestUrl = $value->getRequestPath();
                }
                if ($collectionRequestUrl != $this->collectionRequestUrl) {
                    $idPath = rand(1, 100000);
                    $this->urlRewriteFactory->create()
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceUrl)
                    ->setRequestPath($this->collectionRequestUrl)
                    ->save();
                }
            }
            if ($this->reviewRequestUrl) {
                $sourceUrl = 'marketplace/seller/feedback/shop/'.$profileurl;
                /*
                * Check if already rexist in url rewrite model
                */
                $urlId = 0;
                $reviewRequestUrl = '';
                $urlCollectionData = $this->urlRewriteFactory->create()
                ->getCollection()
                ->addFieldToFilter('target_path', $sourceUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
                foreach ($urlCollectionData as $value) {
                    $urlId = $value->getId();
                    $reviewRequestUrl = $value->getRequestPath();
                }
                if ($reviewRequestUrl != $this->reviewRequestUrl) {
                    $idPath = rand(1, 100000);
                    $this->urlRewriteFactory->create()
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceUrl)
                    ->setRequestPath($this->reviewRequestUrl)
                    ->save();
                }
            }
            if ($this->locationRequestUrl) {
                $sourceUrl = 'marketplace/seller/location/shop/'.$profileurl;
                /*
                * Check if already rexist in url rewrite model
                */
                $urlId = 0;
                $locationRequestUrl = '';
                $urlCollectionData = $this->urlRewriteFactory->create()
                ->getCollection()
                ->addFieldToFilter('target_path', $sourceUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
                foreach ($urlCollectionData as $value) {
                    $urlId = $value->getId();
                    $locationRequestUrl = $value->getRequestPath();
                }
                if ($locationRequestUrl != $this->locationRequestUrl) {
                    $idPath = rand(1, 100000);
                    $this->urlRewriteFactory->create()
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceUrl)
                    ->setRequestPath($this->locationRequestUrl)
                    ->save();
                }
            }
            if ($this->policyRequestUrl) {
                $sourceUrl = 'marketplace/seller/policy/shop/'.$profileurl;
                /*
                * Check if already rexist in url rewrite model
                */
                $urlId = 0;
                $policyRequestUrl = '';
                $urlCollectionData = $this->urlRewriteFactory->create()
                ->getCollection()
                ->addFieldToFilter('target_path', $sourceUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
                foreach ($urlCollectionData as $value) {
                    $urlId = $value->getId();
                    $policyRequestUrl = $value->getRequestPath();
                }
                if ($policyRequestUrl != $this->policyRequestUrl) {
                    $idPath = rand(1, 100000);
                    $this->urlRewriteFactory->create()
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceUrl)
                    ->setRequestPath($this->policyRequestUrl)
                    ->save();
                }
            }
            // clear cache
            $this->mpHelper->clearCache();
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __('The URL Rewrite has been saved.');

            $this->emulate->stopEnvironmentEmulation($environment);
            $this->checkNGenerateEtag($cacheString);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->collectionRequestUrl = $this->wholeData["collectionRequestUrl"] ?? "";
            $this->profileRequestUrl = $this->wholeData["profileRequestUrl"] ?? "";
            $this->reviewRequestUrl = $this->wholeData["reviewRequestUrl"] ?? "";
            $this->locationRequestUrl = $this->wholeData["locationRequestUrl"] ?? "";
            $this->policyRequestUrl = $this->wholeData["policyRequestUrl"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->mobikulHelper->getCustomerByToken($this->customerToken);
            if (!$this->customerId && $this->customerToken == "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Please login to Continue...")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
