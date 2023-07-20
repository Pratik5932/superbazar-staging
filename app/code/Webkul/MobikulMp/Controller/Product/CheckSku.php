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
namespace Webkul\MobikulMp\Controller\Product;

/**
 * Class CheckSku for Validating the sku entered by vendor while generating the product
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class CheckSku extends AbstractProduct
{
    /**
     * Execute function for class CheckSku
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customerSession->setCustomerId($this->sellerId);
            $skuPrefix = $this->marketplaceHelper->getSkuPrefix();
            $isPartner = $this->marketplaceHelper->isSeller();
            if ($isPartner == 1) {
                $sku = $skuPrefix.$this->sku;
                $id = $this->productResourceModel->getIdBySku($sku);
                if ($id) {
                    $availability = false;
                    $this->returnArray["message"] = __("SKU Already Exist");
                } else {
                    $availability = true;
                    $this->returnArray["message"] = __("SKU Available");
                }
            } else {
                $this->returnArray["message"] = __("Invalid Seller");
            }
            $this->returnArray["availability"]  = $availability;
            $this->returnArray["success"]       = true;
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
            $this->sku           = $this->wholeData["sku"]           ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->sellerId      = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->sellerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            } elseif ($this->sellerId != 0) {
                $this->customerSession->setCustomerId($this->sellerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
