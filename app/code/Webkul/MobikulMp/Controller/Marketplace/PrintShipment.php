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

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class PrintShipment controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class PrintShipment extends AbstractMarketplace
{
    /**
     * Execute function for class PrintShipment
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
            $this->customerSession->setCustomerId($this->customerId);
            $order = $this->order->loadByIncrementId($this->incrementId);
            $orderId = $order->getId();
            $shipmentDetails = $this->_initShipment($this->shipmentId, $order);
            if ($shipmentDetails['success']) {
                $shipment = $shipmentDetails['shipment'];
                $pdf = $this->shipmentPdf
                    ->getPdf(
                        [$shipment]
                    );
                $date = date('Y-m-d_H-i-s');
                $this->returnArray["success"] = true;
                return $this->fileFactory->create(
                    'packingslip'.$date.'.pdf',
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            } else {
                $this->returnArray["message"] = $shipmentDetails['success'] ?? __('Failed to print the Shipping.');
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
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->storeId       = $this->wholeData["storeId"]       ?? 1;
            $this->incrementId   = $this->wholeData["incrementId"]   ?? 0;
            $this->shipmentId     = $this->wholeData["shipmentId"]    ?? 0;
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
