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
 * Class PrintCreditmemo controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class PrintCreditmemo extends AbstractMarketplace
{
    /**
     * Execute function for class PrintCreditmemo
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
            $this->customerSession->setCustomerId($this->customerId);
            $order = $this->order->loadByIncrementId($this->incrementId);
            $orderId = $order->getId();
            $memoDetails = $this->_initCreditmemo($this->creditmemoId, $order);
            $isPartner = $this->marketplaceHelper->isSeller();
            if ($isPartner && $memoDetails['success']) {
                $creditmemo = $memoDetails['creditmemo'];
                $pdf = $this->pdfCreditmemo->getPdf(
                    [$creditmemo]
                );
                $date = date('Y-m-d_H-i-s');
                $this->returnArray["success"] = true;
                return $this->fileFactory->create(
                    'creditmemo'.$date.'.pdf',
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
            $this->emulate->stopEnvironmentEmulation($this->environment);
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
     * @param int                        $creditmemoId id of the credit memo
     * @param \Magento\Sales\Model\Order $order        order
     *
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface|false
     */
    private function _initCreditmemo($creditmemoId, $order)
    {
        $data = [];
        $data['success'] = false;
        $creditmemo = false;
        $creditmemo = $this->creditmemoRepository->get($creditmemoId);
        if (!$creditmemo) {
            return $data;
        }
        try {
            $tracking = $this->marketplaceOrderhelper->getOrderinfo($order->getId());
            if ($tracking && $tracking->getId()) {
                $creditmemoArr = explode(',', $tracking->getCreditmemoId());
                if (in_array($creditmemoId, $creditmemoArr)) {
                    if (!$creditmemoId) {
                        $data['message'] = __('The creditmemo no longer exists.');
                        throw new \BadMethodCallException(__("Something Went Wrong.".$data['message']));
                    }
                } else {
                    $data['message'] = __('You are not authorize to view this creditmemo.');
                    throw new \BadMethodCallException(__("Something Went Wrong.".$data['message']));
                }
            } else {
                $data['message'] = __('You are not authorize to view this creditmemo.');
                throw new \BadMethodCallException(__("Something Went Wrong.".$data['message']));
            }
        } catch (\NoSuchEntityException $e) {
            $data['message'] = $e->getMessage();
            throw new \BadMethodCallException(__("Something Went Wrong.".$data['message']));
        } catch (\InputException $e) {
            $data['message'] = $e->getMessage();
            throw new \BadMethodCallException(__("Something Went Wrong.".$data['message']));
        }
        $this->coreRegistry->register('sales_order', $order);
        $this->coreRegistry->register('current_order', $order);
        $this->coreRegistry->register('current_creditmemo', $creditmemo);
        $data['success'] = true;
        $data['creditmemo'] = $creditmemo;
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
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->incrementId   = $this->wholeData["incrementId"]   ?? "";
            $this->creditmemoId  = $this->wholeData["creditmemoId"]    ?? 1;
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
