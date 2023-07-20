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
 * Class DownloadAllInvoice controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class DownloadAllInvoice extends AbstractMarketplace
{

    /**
     * Execute function for class DownloadAllInvoice
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
            $customer    = $this->customer->load($this->customerId);
            $this->customerSession->setCustomer($customer);
            $to = date_format(date_create($this->dateTo), "Y-m-d H:i:s");
            $from = date_format(date_create($this->dateFrom), "Y-m-d H:i:s");
            $invoiceIds = [];
            try {
                $collection = $this->marketplaceSaleList
                    ->getCollection()
                    ->addFieldToFilter("seller_id", $this->customerId)
                    ->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$from, "to"=>$to])
                    ->addFieldToSelect("order_id")
                    ->distinct(true);
                foreach ($collection as $coll) {
                    $shippingColl = $this->marketplaceOrders
                        ->getCollection()
                        ->addFieldToFilter("order_id", $coll->getOrderId())
                        ->addFieldToFilter("seller_id", $this->customerId);
                    foreach ($shippingColl as $tracking) {
                        if ($tracking->getInvoiceId()) {
                            array_push($invoiceIds, $tracking->getInvoiceId());
                        }
                    }
                }
                if (!empty($invoiceIds)) {
                    $invoices = $this->invoiceCollection
                        ->addAttributeToSelect("*")
                        ->addAttributeToFilter("entity_id", ["in"=>$invoiceIds])
                        ->load();
                    if (!$invoices->getSize()) {
                        $this->returnArray["message"] = __(
                            "There are no printable documents related to selected date range."
                        );
                        return $this->getJsonResponse($this->returnArray);
                    }
                    $pdf  = $this->invoicePdf->getPdf($invoices);
                    $date = $this->dateTime->date("Y-m-d_H-i-s");
                    return $this->fileFactory->create(
                        "invoiceslip".$date.".pdf",
                        $pdf->render(),
                        DirectoryList::VAR_DIR,
                        "application/pdf"
                    );
                } else {
                    $this->returnArray["message"] = __(
                        "There are no printable documents related to selected date range."
                    );
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->returnArray["message"] = $e->getMessage();
            } catch (\Exception $e) {
                $this->returnArray["message"] = __("We can't print the invoice right now.");
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
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->dateTo        = $this->wholeData["dateTo"]        ?? "";
            $this->dateFrom      = $this->wholeData["dateFrom"]      ?? "";
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
