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
 * Class CreateInvoice conreoller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class CreateInvoice extends AbstractMarketplace
{
    /**
     * Execute function for class CreateInvoice
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
            $order    = $this->order->loadByIncrementId($this->incrementId);
            $orderId  = $order->getId();
            $sellerId = $this->customerId;
            if ($order->canUnhold()) {
                $this->returnArray['message'] = __('Can not create invoice as order is in HOLD state');
            } else {
                $data = [];
                $data['send_email'] = 1;
                $marketplaceOrder = $this->marketplaceOrders;
                $model = $marketplaceOrder
                    ->getCollection()
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    )
                    ->addFieldToFilter(
                        'order_id',
                        $orderId
                    );
                foreach ($model as $tracking) {
                    $marketplaceOrder = $tracking;
                }
    
                $invoiceId = $marketplaceOrder->getInvoiceId();
                if (!$invoiceId) {
                    $items = [];
                    $itemsarray = [];
                    $shippingAmount = 0;
                    $codcharges = 0;
                    $paymentCode = '';
                    $paymentMethod = '';
                    if ($order->getPayment()) {
                        $paymentCode = $order->getPayment()->getMethod();
                    }
                    $shippingAmount = $marketplaceOrder->getShippingCharges();
                    if ($paymentCode == 'mpcashondelivery') {
                        $codcharges = $marketplaceOrder->getCodCharges();
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
                    if ($collection->getSize() == 0) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('you are not authorize to create invoice')
                        );
                    }
                    foreach ($collection as $saleproduct) {
                        if ($paymentCode == 'mpcashondelivery') {
                            $codCharges = $codCharges + $saleproduct->getCodCharges();
                        }
                        $tax = $tax + $saleproduct->getTotalTax();
                        array_push($items, $saleproduct['order_item_id']);
                    }
                    $itemsarray = $this->_getItemQtys($order, $items);
                    if (count($itemsarray) > 0 && $order->canInvoice()) {
                        $invoice = $this->invoiceService->prepareInvoice($order, $itemsarray['data']);
                        if (!$invoice) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('We can\'t save the invoice right now.')
                            );
                        }
                        if (!$invoice->getTotalQty()) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('You can\'t create an invoice without products.')
                            );
                        }
                        if (!empty($data['capture_case'])) {
                            $invoice->setRequestedCaptureCase(
                                $data['capture_case']
                            );
                        }
                        if (!empty($data['comment_text'])) {
                            $invoice->addComment(
                                $data['comment_text'],
                                isset($data['comment_customer_notify']),
                                isset($data['is_visible_on_front'])
                            );
                            $invoice->setCustomerNote($data['comment_text']);
                            $invoice->setCustomerNoteNotify(
                                isset($data['comment_customer_notify'])
                            );
                        }
                        $invoice->setShippingAmount($shippingAmount);
                        $invoice->setBaseShippingInclTax($shippingAmount);
                        $invoice->setBaseShippingAmount($shippingAmount);
                        $invoice->setSubtotal($itemsarray['subtotal']);
                        $invoice->setBaseSubtotal($itemsarray['baseSubtotal']);
                        if ($paymentCode == 'mpcashondelivery') {
                            $invoice->setMpcashondelivery($codCharges);
                        }
                        $invoice->setGrandTotal(
                            $itemsarray['subtotal'] +
                                $shippingAmount +
                                $codcharges +
                                $tax
                        );
                        $invoice->setBaseGrandTotal(
                            $itemsarray['subtotal'] + $shippingAmount + $codcharges + $tax
                        );
                        $invoice->register();
                        $invoice->getOrder()->setCustomerNoteNotify(
                            !empty($data['send_email'])
                        );
                        $invoice->getOrder()->setIsInProcess(true);
                        $transactionSave = $this->transaction
                            ->addObject(
                                $invoice
                            )->addObject(
                                $invoice->getOrder()
                            );
                        $transactionSave->save();
                        $invoiceId = $invoice->getId();
                        $this->invoiceSender->send($invoice);
                        $this->returnArray['invoiceId'] = $invoiceId;
                        $this->returnArray['message'] = __('Invoice has been created for this order.');
                        $this->returnArray['success'] = true;
                    } else {
                        $this->returnArray['message'] = __('You cannot create invoice for this order.');
                    }
                    /*update mpcod table records*/
                    if ($invoiceId != '') {
                        $this->updateTableRecords($paymentCode, $orderId, $sellerId, $invoiceId);
                    }
                } else {
                    $this->returnArray['message'] = __('Cannot create Invoice for this order.');
                }
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

    public function updateTableRecords($paymentCode, $orderId, $sellerId, $invoiceId)
    {
        if ($paymentCode == 'mpcashondelivery') {
            $saleslistColl = $this->marketplaceSaleList
                ->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    $orderId
                )
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
            foreach ($saleslistColl as $saleslist) {
                $saleslist->setCollectCodStatus(1);
                $saleslist->save();
            }
        }

        $trackingcol1 = $this->marketplaceOrders
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                $orderId
            )
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
        foreach ($trackingcol1 as $row) {
            $row->setInvoiceId($invoiceId);
            $row->save();
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
            $this->storeId       = $this->wholeData["storeId"]     ?? 0;
            $this->incrementId   = $this->wholeData["incrementId"] ?? 0;
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

    /**
     * Function to get Items Qty
     *
     * @param \Magento\Sales\Model\order $order order
     * @param array                      $items items
     *
     * @return array
     */
    private function _getItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)) {
                $data[$item->getItemId()] = (int) $item->getQtyOrdered() - $item->getQtyInvoiced();

                $_item = $item;

                // for bundle product
                $bundleitems = $this->arrayMerge([$_item], $_item->getChildrenItems());

                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] =
                                (int) $_bundleitem->getQtyOrdered() - $item->getQtyInvoiced();
                        }
                    }
                }
                $subtotal += $_item->getRowTotal();
                $baseSubtotal += $_item->getBaseRowTotal();
            } else {
                if (!$item->getParentItemId()) {
                    $data[$item->getItemId()] = 0;
                }
            }
        }
        return ['data' => $data,'subtotal' => $subtotal,'baseSubtotal' => $baseSubtotal];
    }

    public function arrayMerge($arr1, $arr2)
    {
        return array_merge($arr1, $arr2);
    }
}
