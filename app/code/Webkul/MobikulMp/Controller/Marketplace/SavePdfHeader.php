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
 * Class SavePdfHeader controller
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
class SavePdfHeader extends AbstractMarketplace
{
    /**
     * Execute function for class SavePdfHeader
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
            $sellerId      = 0;
            $sellerCollection = $this->seller->getCollection()
                ->addFieldToFilter("seller_id", $this->customerId)
                ->addFieldToFilter("store_id", $this->storeId);
            foreach ($sellerCollection as $eachSeller) {
                $sellerId = $eachSeller->getId();
            }
            $sellerData = [];
            if (!$sellerId) {
                $sellerDefaultData = [];
                $sellerCollection  = $this->seller->getCollection()
                    ->addFieldToFilter("seller_id", $this->customerId)
                    ->addFieldToFilter("store_id", 0);
                foreach ($sellerCollection as $eachSeller) {
                    $sellerDefaultData = $eachSeller->getData();
                    $eachSeller->setOthersInfo($this->pdfHeader);
                    $eachSeller->save();
                }
                foreach ($sellerDefaultData as $key => $value) {
                    if ($key != "entity_id") {
                        $sellerData[$key] = $value;
                    }
                }
            }
            $seller = $this->seller->load($sellerId);
            if (!empty($sellerData)) {
                $seller->addData($sellerData);
            }
            $seller->setOthersInfo($this->pdfHeader);
            $seller->setStoreId($this->storeId);
            $seller->save();
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Information was successfully saved");
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
            $this->pdfHeader     = $this->wholeData["pdfHeader"]     ?? "";
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
