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
 * Class SaveReview controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class SaveReview extends AbstractMarketplace
{
    /**
     * Execute function for class SaveReview
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
            $sellerCollection = $this->marketplaceHelper->getSellerCollection()
                ->addFieldToFilter("seller_id", $this->customerId)
                ->setPagesize(1)
                ->getFirstItem();
            $sellerId = $this->sellerId;
            if ($sellerCollection->getSellerId() && $sellerId == $sellerCollection->getSellerId()) {
                $this->returnArray["success"] = false;
                $this->returnArray["message"] = __("You can not review your own shop.");
                return $this->getJsonResponse($this->returnArray);
            }
            $data                       = [];
            $data["buyer_id"]           = $this->customerId;
            $data["shop_url"]           = $this->shopUrl;
            $data["seller_id"]          = $this->sellerId;
            $data["created_at"]         = $this->date->gmtDate();
            $data["feed_price"]         = $this->priceRating;
            $data["feed_value"]         = $this->valueRating;
            $data["buyer_email"]        = $this->customerEmail;
            $data["feed_review"]        = $this->description;
            $data["feed_quality"]       = $this->qualityRating;
            $data["feed_summary"]       = $this->summary;
            $data["feed_nickname"]      = $this->nickName;
            $data["admin_notification"] = 1;
            $feedbackcount  = 0;
            $collectionfeed = $this->feedBackModel->getCollection()
                ->addFieldToFilter("seller_id", $this->sellerId)
                ->addFieldToFilter("buyer_id", $this->customerId);
            foreach ($collectionfeed as $value) {
                $feedbackcount = $value->getFeedbackCount();
                $value->setFeedbackCount($feedbackcount + 1);
                $value->save();
            }
            $this->reviewModel->setData($data)->save();
            $this->returnArray["message"] = __("Your Review was successfully saved");
            $this->returnArray["success"] = true;
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
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->summary       = $this->wholeData["summary"]       ?? "";
            $this->shopUrl       = $this->wholeData["shopUrl"]       ?? "";
            $this->nickName      = $this->wholeData["nickName"]      ?? "";
            $this->sellerId      = $this->wholeData["sellerId"]      ?? 0;
            $this->priceRating   = $this->wholeData["priceRating"]   ?? 20;
            $this->valueRating   = $this->wholeData["valueRating"]   ?? 20;
            $this->description   = $this->wholeData["description"]   ?? "";
            $this->customerEmail = $this->wholeData["customerEmail"] ?? "";
            $this->qualityRating = $this->wholeData["qualityRating"] ?? 20;
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
