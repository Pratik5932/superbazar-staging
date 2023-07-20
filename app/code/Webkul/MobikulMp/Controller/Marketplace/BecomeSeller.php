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
 * Class BecomeSeller controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class BecomeSeller extends AbstractMarketplace
{
    /**
     * Execute function for class BecomeSeller
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
            $status      = $this->marketplaceHelper->getIsPartnerApproval() ? 0 : 1;
            if ($status == 0) {
                $this->returnArray["isPending"] = true;
            } else {
                $this->returnArray["isPending"] = false;
            }
            $model = $this->seller->getCollection()->addFieldToFilter("shop_url", $this->shopUrl);
            if (!$model->getSize()) {
                $seller     = $this->seller;
                $collection = $this->seller->getCollection()->addFieldToFilter("seller_id", $this->customerId);
                foreach ($collection as $value) {
                    $seller = $this->seller->load($value->getId());
                }
                $seller->setData("is_seller", $status);
                $seller->setData("shop_url", $this->shopUrl);
                $seller->setData("seller_id", $this->customerId);
                $seller->setCreatedAt($this->date->gmtDate());
                $seller->setUpdatedAt($this->date->gmtDate());
                $seller->setAdminNotification(1);
                $seller->save();
                if ($status) {
                    $this->returnArray["message"] = __('Congratulations! Your seller account is created.');
                } else {
                    $this->returnArray["message"] = __('Your request to become seller is successfully raised.');
                }
                $this->returnArray["success"] = true;
            } else {
                $this->returnArray["message"] = __("Shop URL already exist please set another.");
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
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->shopUrl = $this->wholeData["shopUrl"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->customerId    = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
