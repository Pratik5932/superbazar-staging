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
 * Class CreateCreditMemo conreoller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class CreateCreditMemo extends AbstractMarketplace
{
    /**
     * Execute function for class ContactSeller
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $itemsData   = $this->jsonHelper->jsonDecode($this->itemsData);
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customerSession->setCustomerId($this->customerId);
            $order = $this->order->loadByIncrementId($this->incrementId);
            $orderId = $order->getId();
            $orderDetails = $this->_initOrder($order);
            if ($orderDetails['success']) {
                $refundData = [
                    'invoice_id' => $this->invoiceId,
                    'id'         => $orderId,
                    'creditmemo' => [
                        'items'                   => $itemsData,
                        'comment_text'            => $this->comment,
                        'shipping_amount'         => $this->shippingAmount,
                        'adjustment_positive'     => $this->adjustmentPositive,
                        'adjustment_negative'     => $this->adjustmentNegative,
                        'do_offline'              => $this->doOffline,
                        'comment_customer_notify' => $this->commentCustomerNotify,
                        'is_visible_on_front'     => $this->isVisibleOnFront,
                        'send_email'              => $this->invoiceId
                    ]
                ];
                $creditmemo = $this->_initOrderCreditmemo($order, $refundData, $this->customerId);
                if ($creditmemo) {
                    if (!$creditmemo->isValidGrandTotal()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The credit memo\'s total must be positive.')
                        );
                    }
                    $data = $refundData['creditmemo'];

                    if (!empty($data['comment_text'])) {
                        $creditmemo->addComment(
                            $data['comment_text'],
                            isset($data['comment_customer_notify']),
                            isset($data['is_visible_on_front'])
                        );
                        $creditmemo->setCustomerNote($data['comment_text']);
                        $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                    }

                    if (isset($data['do_offline'])) {
                        //do not allow online refund for Refund to Store Credit
                        if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('Cannot create online refund for Refund to Store Credit.')
                            );
                        }
                    }
                    $creditmemoManagement = $this->creditmemoManager;
                    $creditmemo = $creditmemoManagement
                        ->refund($creditmemo, (bool) $data['do_offline'], !empty($data['send_email']));
                    /*update records*/
                    $creditmemoIds = [];
                    $trackingcol1 = $this->marketplaceOrders
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            ['eq' => $orderId]
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $this->customerId]
                        );
                    foreach ($trackingcol1 as $tracking) {
                        if ($tracking->getCreditmemoId()) {
                            $creditmemoIds = explode(',', $tracking->getCreditmemoId());
                        }
                        array_push($creditmemoIds, $creditmemo->getId());
                        $tracking->setCreditmemoId(implode(',', $creditmemoIds));
                        $tracking->save();
                    }

                    if (!empty($data['send_email'])) {
                        $this->creditmemoSender->send($creditmemo);
                    }

                    if (!empty($data['send_email'])) {
                        $this->creditmemoSender->send($creditmemo);
                    }
                    $this->returnArray['creditmemoId'] = $creditmemo->getId();
                    $this->returnArray['success'] = true;
                    $this->returnArray['message'] = __('You created the credit memo.');
                }
            } else {
                $this->returnArray['message'] = $orderDetails['message']?? __(
                    'We can\'t save the credit memo right now.'
                );
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
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
            $this->storeId     = $this->wholeData["storeId"]     ?? 0;
            $this->comment     = $this->wholeData["comment"]     ?? '';
            $this->itemsData   = $this->wholeData["item"]        ?? '{}';
            $this->doOffline   = $this->wholeData["doOffline"]   ?? 0;
            $this->sendEmail   = $this->wholeData["sendEmail"]   ?? 0;
            $this->invoiceId   = $this->wholeData["invoiceId"]   ?? 0;
            $this->incrementId = $this->wholeData["incrementId"] ?? 0;
            $this->customerToken         = $this->wholeData["customerToken"] ?? '';
            $this->shippingAmount        = $this->wholeData["shippingAmount"] ?? 0;
            $this->isVisibleOnFront      = $this->wholeData["isVisibleOnFront"] ?? 0;
            $this->adjustmentPositive    = $this->wholeData["adjustmentPositive"] ?? 0;
            $this->adjustmentNegative    = $this->wholeData["adjustmentNegative"] ?? 0;
            $this->commentCustomerNotify = $this->wholeData["commentCustomerNotify"] ?? 0;
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

    public function arrayMerge($arr1, $arr2)
    {
        return array_merge($arr1, $arr2);
    }

    /**
     * Initialize creditmemo model instance.
     *
     * @param \Magento\Sales\Model\Order $order      order
     * @param array                      $refundData refundData
     * @param integer                    $sellerId   sellerId
     *
     * @return Magento\Sales\Model\Order\Creditmemo
     */
    private function _initOrderCreditmemo($order, $refundData, $sellerId)
    {
        $creditmemo = false;
        $orderId = $order->getId();
        $invoice = $this->_initCreditmemoInvoice($order, $refundData['invoice_id']);
        $items = [];
        $itemsarray = [];
        $shippingAmount = 0;
        $codcharges = 0;
        $paymentCode = '';
        $paymentMethod = '';
        if ($order->getPayment()) {
            $paymentCode = $order->getPayment()->getMethod();
        }
        $trackingsdata = $this->marketplaceOrders
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['eq' => $orderId]
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            );
        foreach ($trackingsdata as $tracking) {
            $shippingAmount = $tracking->getShippingCharges();
            if ($paymentCode == 'mpcashondelivery') {
                $codcharges = $tracking->getCodCharges();
            }
        }
        $codCharges = 0;
        $tax = 0;
        $collection = $this->marketplaceSaleList
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['eq' => $orderId]
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            );
        foreach ($collection as $saleproduct) {
            if ($paymentCode == 'mpcashondelivery') {
                $codCharges = $codCharges + $saleproduct->getCodCharges();
            }
            $tax = $tax + $saleproduct->getTotalTax();
            array_push($items, $saleproduct['order_item_id']);
        }
        $savedData = $this->_getItemData($order, $items, $refundData);
        $qtys = [];
        foreach ($savedData as $orderItemId => $itemData) {
            if (isset($itemData['qty']) && $itemData['qty']) {
                $qtys[$orderItemId] = $itemData['qty'];
            }
            if (isset($refundData['creditmemo']['items'][$orderItemId]['back_to_stock'])) {
                $backToStock[$orderItemId] = true;
            }
        }
        if (empty($refundData['creditmemo']['shipping_amount'])) {
            $refundData['creditmemo']['shipping_amount'] = 0;
        }
        if (empty($refundData['creditmemo']['adjustment_positive'])) {
            $refundData['creditmemo']['adjustment_positive'] = 0;
        }
        if (empty($refundData['creditmemo']['adjustment_negative'])) {
            $refundData['creditmemo']['adjustment_negative'] = 0;
        }
        if (!$shippingAmount >= $refundData['creditmemo']['shipping_amount']) {
            $refundData['creditmemo']['shipping_amount'] = 0;
        }
        $refundData['creditmemo']['qtys'] = $qtys;
        if ($invoice) {
            $creditmemo = $this->creditmemoFactory->createByInvoice(
                $invoice,
                $refundData['creditmemo']
            );
        } else {
            $creditmemo = $this->creditmemoFactory->createByOrder(
                $order,
                $refundData['creditmemo']
            );
        }
        /*
        * Process back to stock flags
        */
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            $orderItem = $creditmemoItem->getOrderItem();
            $parentId = $orderItem->getParentItemId();
            if (isset($backToStock[$orderItem->getId()])) {
                $creditmemoItem->setBackToStock(true);
            } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                $creditmemoItem->setBackToStock(true);
            } elseif (empty($savedData)) {
                $creditmemoItem->setBackToStock(
                    $this->stockConfiguration->isAutoReturnEnabled()
                );
            } else {
                $creditmemoItem->setBackToStock(false);
            }
        }
        $this->coreRegistry->register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }

    /**
     * Function to initialize creditmemo invoice
     *
     * @param \Magento\Sales\Model\Order $order     order
     * @param integer                    $invoiceId invoiceId
     *
     * @return $this|bool
     */
    private function _initCreditmemoInvoice($order, $invoiceId)
    {
        if ($invoiceId) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            $invoice->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }
        return false;
    }

    /**
     * Get requested items qtys.
     *
     * @param \Magento\Sales\Model\Order $order      orderInstance
     * @param array                      $items      items
     * @param array                      $refundData refund Data
     *
     * @return array $qtys
     */
    private function _getItemData($order, $items, $refundData)
    {
        $data['items'] = [];
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)
                && isset($refundData['creditmemo']['items'][$item->getItemId()]['qty'])
            ) {
                $data['items'][$item->getItemId()]['qty'] =
                    (int)$refundData['creditmemo']['items'][$item->getItemId()]['qty'];

                $_item = $item;
                // for bundle product
                $bundleitems = $this->arrayMerge([$_item], $_item->getChildrenItems());
                if ($_item->getParentItem()) {
                    continue;
                }
            } else {
                if (!$item->getParentItemId()) {
                    $data['items'][$item->getItemId()]['qty'] = 0;
                }
            }
        }
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = [];
        }
        return $qtys;
    }
}
