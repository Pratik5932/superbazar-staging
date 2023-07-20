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
 * Class ProductMassDelete controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class ProductMassDelete extends AbstractMarketplace
{
    /**
     * Execute function for class ProductMassDelete
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $productIds    = $this->jsonHelper->jsonDecode($this->productIds);
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->coreRegistry->register("isSecureArea", 1);
            $deletedIds = [];
            $sellerProducts = $this->sellerProductCollectionFactory
                ->create()
                ->addFieldToFilter("mageproduct_id", ["in" => $productIds])
                ->addFieldToFilter("seller_id", $this->customerId);
            foreach ($sellerProducts as $sellerProduct) {
                array_push($deletedIds, $sellerProduct["mageproduct_id"]);
                $this->eventManager->dispatch("mp_delete_product", [$sellerProduct["mageproduct_id"]]);
                $sellerProduct->delete();
            }
            $mageProducts = $this->productCollectionFactory->create()->addFieldToFilter(
                "entity_id",
                ["in"=>$deletedIds]
            );
            foreach ($mageProducts as $mageProduct) {
                $mageProduct->delete();
            }
            $unauthIds = array_diff($productIds, $deletedIds);
            $this->coreRegistry->unregister("isSecureArea");
            if (!count($unauthIds)) {
                $this->returnArray["message"] = __("Products are successfully deleted from your account.");
            }
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
            $this->productIds    = $this->wholeData["productIds"]    ?? "[]";
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
