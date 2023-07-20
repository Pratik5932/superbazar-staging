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
 * Class PrintInvoice controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class PrintInvoice extends AbstractMarketplace
{
    /**
     * Execute function for class PrintInvoice
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
            $invoiceDetails = $this->_initInvoice($this->invoiceId, $order);
            $isPartner = $this->marketplaceHelper->isSeller();
            if ($isPartner && $invoiceDetails['success']) {
                $invoice = $invoiceDetails['invoice'];
                $pdf = $this->invoicePdf->getPdf(
                    [$invoice]
                );
                $date = $this->date->date('Y-m-d_H-i-s');
                return $this->fileFactory->create(
                    'invoice' . $date . '.pdf',
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            } else {
                $this->returnArray["message"] = $invoiceDetails['message'] ?? __("Failed to print the invoice.");
                $this->returnArray["success"] = false;
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->getJsonResponse(
                $this->returnArray
            );
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Initialize invoice model instance.
     *
     * @param int                        $invoiceId invoice Id
     * @param \Magento\Sales\Model\Order $order     order
     *
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface|false
     */
    private function _initInvoice($invoiceId, $order)
    {
        $data = [];
        $data['success'] = false;
        if (!$invoiceId) {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $this->invoiceRepository->get($invoiceId);
        if (!$invoice) {
            throw new \BadMethodCallException(__("Invalid Invoice Id"));
        }
        try {
            $tracking = $this->marketplaceOrderhelper->getOrderinfo($order->getId());
            if ($tracking && $tracking->getId()) {
                if ($tracking->getInvoiceId() == $invoiceId) {
                    if (!$invoiceId) {
                        $data['message'] = __("The invoice no longer exists.");
                        throw new \BadMethodCallException(__($data['message']));
                    }
                } else {
                    $data['message'] = __("You are not authorize to view this invoice.");
                    throw new \BadMethodCallException(__($data['message']));
                }
            } else {
                $data['message'] = __("You are not authorize to view this invoice.");
                throw new \BadMethodCallException(__($data['message']));
            }
        } catch (\NoSuchEntityException $e) {
            throw new \BadMethodCallException(__($e->getMessage()));
        } catch (\InputException $e) {
            throw new \BadMethodCallException(__($e->getMessage()));
        }
        $this->coreRegistry->register('sales_order', $order);
        $this->coreRegistry->register('current_order', $order);
        $this->coreRegistry->register('current_invoice', $invoice);
        $data['success'] = true;
        $data['invoice'] = $invoice;
        $data['tracking'] = $tracking;
        return $data;
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
            $this->invoiceId     = $this->wholeData["invoiceId"]     ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->customerId    = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken == "") {
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
