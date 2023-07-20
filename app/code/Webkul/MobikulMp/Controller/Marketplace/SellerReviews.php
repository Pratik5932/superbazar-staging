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
 * Class SellerReviews controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class SellerReviews extends AbstractMarketplace
{
    /**
     * Execute function for class SellerReviews
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
            $reviewCollection = $this->reviewModel->getCollection()
                ->addFieldToFilter("status", ["neq"=>0])
                ->addFieldToFilter("seller_id", $this->sellerId)
                ->setOrder("entity_id", "DESC");
            $reviewList = [];
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $reviewCollection->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $reviewCollection->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
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
            $this->pagenumber    = $this->wholeData["pagenumber"]    ?? 1000;
            $this->sellerId      = $this->wholeData["sellerId"]      ?? 0;
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
