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
 * Class ViewOrder controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class ViewOrder extends AbstractMarketplace
{
    /**
     * Execute function for class ViewOrder
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "VIEWORDER".$this->storeId.$this->width.$this->sellerId;
            $cacheString .= $this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customerSession->setCustomerId($this->customerId);
            $this->order   = $this->order->loadByIncrementId($this->incrementId);
            $orderId = $this->order->getId();
            $orderStatusLabel = $this->order->getStatusLabel();
            $orderCollection  = $this->marketplaceOrders->getCollection()
                ->addFieldToFilter("order_id", $orderId)
                ->addFieldToFilter("seller_id", $this->customerId);
            $this->paymentCode    = "";
            $paymentMethod = "";
            if ($orderCollection->getSize()) {
                $this->dashboardHelper->sellerId = $this->customerId;
                if ($this->order->getPayment()) {
                    $this->paymentCode = $this->order->getPayment()->getMethod();
                    $paymentMethod = $this->order->getPayment()->getMethodInstance()->getTitle();
                }
                $tracking = $this->dashboardHelper->getOrderinfo($orderId);
                if ($tracking != "") {
                    if ($this->paymentCode == "mpcashondelivery") {
                        $this->returnArray["mpCODAvailable"] = true;
                    }
                }
                $isCanceled = $tracking->getIsCanceled();
                if ($isCanceled) {
                    $orderStatusLabel = "Canceled";
                }
                $this->returnArray["statusLabel"] = $orderStatusLabel;
                $this->returnArray["incrementId"] = $this->order->getRealOrderId();
                $this->returnArray["orderDate"] = $this->viewTemplate->formatDate(
                    $this->order->getCreatedAt(),
                    \IntlDateFormatter::MEDIUM,
                    true,
                    $this->viewTemplate->getTimezoneForStore($this->order->getStore())
                );
                $this->itemBlock = $this->orderItemRenderer;
                $this->priceBlock = $this->priceRenderer;
                $this->orderDetailsnTotals();
                
                // getting order information /////////////////////////////////////////////////
                if ($this->marketplaceHelper->getIsOrderManage()) {
                    $this->returnArray['invoiceId'] = $tracking->getInvoiceId();
                    $this->returnArray['shipmentId'] = $tracking->getShipmentId();
                    $this->returnArray["manageOrder"] = true;
                    if ($isCanceled!="1" && $this->order->canCancel() && !$tracking->getInvoiceId()) {
                        $this->returnArray['cancelButton'] = true;
                    }
                    if ($isCanceled!="1" && !$this->order->isCanceled()) {
                        $this->returnArray['sendEmailButton'] = true;
                    }
                    $creditmemoId = $tracking->getCreditmemoId();
                    if ($creditmemoId && !$this->orderViewBlock->isOrderCanShip($this->order)) {
                        $shippingamount=$tracking->getShippingCharges();
                        $refundedShippingAmount = $tracking->getRefundedShippingCharges();
                        if ($shippingamount-$refundedShippingAmount == 0) {
                            $itemRefundStatus = 'Refunded';
                        } else {
                            $itemRefundStatus = '';
                        }
                    } else {
                        $itemRefundStatus = '';
                    }
                    if (!$tracking->getInvoiceId() && $this->order->canInvoice() && $isCanceled!="1") {
                        $this->returnArray['invoiceButton'] = true;
                    } elseif ($tracking->getInvoiceId() &&
                        $itemRefundStatus!="Refunded" &&
                        $this->order->canCreditmemo() &&
                        $isCanceled!="1"
                    ) {
                        if ($this->paymentCode == 'mpcashondelivery' && !$this->getAdminPayStatus($orderId, $customerId)
                        ) {
                            $this->returnArray['payCommissionButton'] = true;
                            $this->returnArray['creditMemoButton'] = true;
                        } elseif ($this->paymentCode != 'mpcashondelivery') {
                            $this->returnArray['creditMemoButton'] = true;
                        }
                    }
                    if ($this->order->hasCreditmemos()) {
                        $this->returnArray['creditMemoTab'] = true;
                    }

                    if (!$tracking->getShipmentId() && $itemRefundStatus!="Refunded"
                        && $isCanceled!="1"
                        && $this->orderViewBlock->isOrderCanShip($this->order)
                    ) {
                        $this->returnArray['shipmentButton'] = true;
                    }
                }
            }
            // getting buyer information /////////////////////////////////
            if ($this->marketplaceHelper->getSellerProfileDisplayFlag()) {
                $this->returnArray["showBuyerInformation"] = true;
                $this->returnArray["buyerName"]  = $this->order->getCustomerName();
                $this->returnArray["buyerEmail"] = $this->order->getCustomerEmail();
            }
            // getting order information /////////////////////////////////
            if ($this->marketplaceHelper->getSellerProfileDisplayFlag()) {
                $this->returnArray["showAddressInformation"] = true;
                if ($this->orderViewBlock->isOrderCanShip($this->order)) {
                    $this->returnArray["canShip"] = true;
                    $this->returnArray["shippingAddress"] = $this->orderViewBlock->getFormattedAddress(
                        $this->order->getShippingAddress()
                    );
                }
                $this->returnArray["billingAddress"] = $this->orderViewBlock->getFormattedAddress(
                    $this->order->getBillingAddress()
                );
            }
            if ($this->order->getShippingDescription()) {
                $this->returnArray["shippingMethod"] = $this->viewTemplate->escapeHtml(
                    $this->order->getShippingDescription()
                );
            } else {
                $this->returnArray["shippingMethod"] = __("No shipping information available");
            }

            // credit memolist section
            $this->returnArray['subHeading']          = __('Creditmemo List');
            $this->returnArray['mainHeading']         = __('View All Memos');
            $this->returnArray['statusHeading']       = __('Status');
            $this->returnArray['amountHeading']       = __('Amount');
            $this->returnArray['actionHeading']       = __('Action');
            $this->returnArray['createdAtHeading']    = __('Created At');
            $this->returnArray['creditMemoIdHeading'] = __('Credit Memos #');
            $order   = $this->order->loadByIncrementId($this->incrementId);
            $orderId = $order->getId();
            $creditMemoLists = [];
            $collection = $this->getMemoCollection($orderId);
            foreach ($collection as $creditmemo) {
                $oneMemoData = [];
                $oneMemoData['entityId'] = $creditmemo['entity_id'];
                $oneMemoData['incrementId'] = $creditmemo['increment_id'];
                $oneMemoData['billToName'] = $order->getCustomerName();
                $oneMemoData['createdAt'] = $creditmemo->getCreatedAt();
                $oneMemoData['status'] = __('Refunded');
                $oneMemoData['amount'] = $this->helperCatalog->stripTags($order->formatPrice(
                    $creditmemo->getGrandTotal()
                ));
                $creditMemoLists[] = $oneMemoData;
            }
            $this->returnArray['creditMemoList'] = $creditMemoLists;
            // credit memolist section
            $this->returnArray["paymentMethod"] = $paymentMethod;
            $this->returnArray["success"]   = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            $this->checkNGenerateEtag($cacheString);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function to get Memo Collection
     *
     * @param int \Magento\Sales\Model\Order $orderId order
     *
     * @return bool|\Magento\Sales\Model\Order\Creditmemo\Collection
     */
    public function getMemoCollection($orderId)
    {
        $tracking = $this->marketplaceOrderhelper->getOrderinfo($orderId);
        $creditmemo = [];
        if ($tracking) {
            $creditmemoIds = [];
            $creditmemoIds = explode(',', $tracking->getCreditmemoId());
            $creditmemo = $this->creditmemo->getCollection()
                ->addFieldToFilter(
                    'entity_id',
                    ['in' => $creditmemoIds]
                );
        }
        return $creditmemo;
    }

    public function arrayMerge($arr1, $arr2)
    {
        return array_merge($arr1, $arr2);
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
            $this->eTag          = $this->wholeData["eTag"]          ?? "";
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->width         = $this->wholeData["width"]         ?? 1000;
            $this->sellerId      = $this->wholeData["sellerId"]      ?? 0;
            $this->incrementId   = $this->wholeData["incrementId"]   ?? 0;
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
     * Function to get admin payment Status
     *
     * @param int $orderId  order id
     * @param int $sellerId sellerId
     *
     * @return string
     */
    public function getAdminPayStatus($orderId, $sellerId)
    {
        $adminPayStatus = 0;
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter(
                'order_id',
                ['eq' => $orderId]
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            );
        foreach ($collection as $saleproduct) {
            $adminPayStatus = $saleproduct->getAdminPayStatus();
        }
        return $adminPayStatus;
    }

    /**
     * Function orderDetailsnTotals
     *
     * Set order data in return array
     *
     * @return null
     */
    protected function orderDetailsnTotals()
    {
        $this->loadedOrder = $this->order;
        $orderId = $this->order->getId();
        $items = $this->loadedOrder->getItemsCollection();
        $itemList = [];
        $orderData = [];
        $subtotal         = 0;
        $totaltax         = 0;
        $couponamount     = 0;
        $adminSubtotal   = 0;
        $shippingamount   = 0;
        $vendorSubtotal  = 0;
        $codchargesTotal = 0;
        $itemList         = [];
        foreach ($items as $item) {
            $eachItem = [];
            $row_total              = 0;
            $itemPrice              = 0;
            $couponcharges          = 0;
            $shippingcharges        = 0;
            $sellerItemCost       = 0;
            $totaltaxPeritem       = 0;
            $codchargesPeritem     = 0;
            $availableSellerItem  = 0;
            $sellerItemCommission = 0;
            $sellerOrderslist      = $this->orderViewBlock->getSellerOrdersList(
                $orderId,
                $item->getProductId(),
                $item->getItemId()
            );
            foreach ($sellerOrderslist as $sellerItem) {
                $itemPrice              = $sellerItem->getMageproPrice();
                $totalamount            = $sellerItem->getTotalAmount();
                $couponcharges          = $sellerItem->getAppliedCouponAmount();
                $shippingcharges        = $sellerItem->getShippingCharges();
                $sellerItemCost       = $sellerItem->getActualSellerAmount();
                $eachItem['sellerItemCost'] = $sellerItemCost;
                $eachItem['formattedSellerItemCost'] = $this->loadedOrder->formatPriceTxt($sellerItemCost);
                $totaltaxPeritem       = $sellerItem->getTotalTax();
                $eachItem['totalTaxPerItem'] = $totaltaxPeritem;
                $eachItem['formattedTotalTaxPerItem'] = $this->loadedOrder->formatPriceTxt($totaltaxPeritem);
                $availableSellerItem  = 1;
                $sellerItemCommission = $sellerItem->getTotalCommission();
                $eachItem['adminItemCommission'] = $sellerItemCommission;
                $eachItem['formattedAdminItemCommission'] = $this->loadedOrder->formatPriceTxt($sellerItemCommission);
                if ($this->paymentCode == "mpcashondelivery") {
                    $codchargesPeritem = $sellerItem->getCodCharges();
                }
            }
            if ($availableSellerItem == 1) {
                $rowTotal        = $itemPrice*$item->getQtyOrdered();
                $vendorSubtotal  = $vendorSubtotal+$sellerItemCost;
                $subtotal        = $subtotal+$rowTotal;
                $adminSubtotal   = $adminSubtotal+$sellerItemCommission;
                $totaltax        = $totaltax+$totaltaxPeritem;
                $codchargesTotal = $codchargesTotal+$codchargesPeritem;
                $shippingamount  = $shippingamount+$shippingcharges;
                $couponamount    = $couponamount+$couponcharges;
                $result          = [];
                if ($options = $item->getProductOptions()) {
                    if (isset($options["options"])) {
                        $result = $this->arrayMerge($result, $options["options"]);
                    }
                    if (isset($options["additional_options"])) {
                        $result = $this->arrayMerge($result, $options["additional_options"]);
                    }
                    if (isset($options["attributes_info"])) {
                        $result = $this->arrayMerge($result, $options["attributes_info"]);
                    }
                }
            }
            $this->itemBlock->setItem($item);
            $this->priceBlock->setItem($item);
            if ($item->getParentItem()) {
                continue;
            }
            $eachItem["name"] = htmlspecialchars_decode($item->getName());
            $eachItem["productId"] = $item->getProductId();
            if ($options = $this->itemBlock->getItemOptions()) {
                foreach ($options as $option) {
                    $eachOption = [];
                    $eachOption["label"] = htmlspecialchars_decode($option["label"]);
                    if (is_array($option["value"])) {
                        $eachOption["value"] = $option["value"];
                    } else {
                        $eachOption["value"][] = $this->helperCatalog->stripTags($option["value"]);
                    }
                    $eachItem["option"][] = $eachOption;
                }
            } else {
                $eachItem["option"] = [];
            }
            $eachItem["sku"] = $this->itemBlock->prepareSku($this->itemBlock->getSku());
            if ($this->priceBlock->displayPriceInclTax() || $this->priceBlock->displayBothPrices()) {
                $eachItem["price"] = $this->loadedOrder->formatPriceTxt($this->itemBlock->getUnitDisplayPriceInclTax());
            }
            if ($this->priceBlock->displayPriceExclTax() || $this->priceBlock->displayBothPrices()) {
                $eachItem["price"] = $this->loadedOrder->formatPriceTxt(
                    $this->priceBlock->getUnitDisplayPriceExclTax()
                );
            }
            $eachItem["qty"]["Ordered"] = $this->itemBlock->getItem()->getQtyOrdered()*1;
            $eachItem["qty"]["Shipped"] = $this->itemBlock->getItem()->getQtyShipped()*1;
            $eachItem["qty"]["Canceled"] = $this->itemBlock->getItem()->getQtyCanceled()*1;
            $eachItem["qty"]["Refunded"] = $this->itemBlock->getItem()->getQtyRefunded()*1;
            if (($this->priceBlock->displayPriceInclTax() || $this->priceBlock->displayBothPrices()) &&
                !$item->getNoSubtotal()
            ) {
                $eachItem["subTotal"] = $this->loadedOrder->formatPriceTxt(
                    $this->priceBlock->getRowDisplayPriceInclTax()
                );
            }
            if ($this->priceBlock->displayPriceExclTax() || $this->priceBlock->displayBothPrices()) {
                $eachItem["subTotal"] = $this->loadedOrder->formatPriceTxt(
                    $this->priceBlock->getRowDisplayPriceExclTax()
                );
            }
            
            if ($availableSellerItem == 1) {
                $itemList[] = $eachItem;
            }
        }
        $itemList = $this->addProductImages($itemList);
        $orderData["itemList"] = $itemList;
        $totals = [];
        $totalsBlock = $this->orderTotals;
        $totalsBlock->setOrder($this->loadedOrder);
        $totalsBlock->_initTotals();
        foreach ($totalsBlock->getTotals() as $total) {
            if ($total->getCode() == "grand_total") {
                $this->returnArray["orderTotal"] = $this->loadedOrder->formatPriceTxt($rowTotal+$shippingamount);
            }
        }
        $eachTotal = [];
        $eachTotal["code"] = "subtotal";
        $eachTotal["label"] = __("Subtotal");
        $eachTotal["value"] = $subtotal;
        $eachTotal["formattedValue"] = $this->loadedOrder->formatPriceTxt($subtotal);
        $totals[] = $eachTotal;
        $eachTotal = [];
        $eachTotal["code"] = "shipping";
        $eachTotal["label"] = __("Shipping");
        $eachTotal["value"] = $shippingamount;
        $eachTotal["formattedValue"] = $this->loadedOrder->formatPriceTxt($shippingamount);
        $totals[] = $eachTotal;
        $eachTotal = [];
        $eachTotal["code"] = "grand_total";
        $eachTotal["label"] = __("Grand Total");
        $eachTotal["value"] = $rowTotal;
        $eachTotal["formattedValue"] = $this->loadedOrder->formatPriceTxt($subtotal+$shippingamount);
        $totals[] = $eachTotal;
        $eachTotal = [];
        $eachTotal["code"] = "tax";
        $eachTotal["label"] = __("Tax");
        $eachTotal["value"] = $this->loadedOrder->getTaxAmount();
        $eachTotal["formattedValue"] = $this->loadedOrder->formatPriceTxt($this->loadedOrder->getTaxAmount());
        $totals[] = $eachTotal;
        $vendorTotal = [];
        $vendorTotal["code"] = "vendorSubtotal";
        $vendorTotal["label"] = __("Vendor Sub Total");
        $vendorTotal["value"] = $vendorSubtotal;
        $vendorTotal["formattedValue"] = $this->loadedOrder->formatPriceTxt($vendorSubtotal);
        $totals[] = $vendorTotal;
        $adminTotal = [];
        $adminTotal["code"] = "adminSubtotal";
        $adminTotal["label"] = __("Admin Sub Total");
        $adminTotal["value"] = $adminSubtotal;
        $adminTotal["formattedValue"] = $this->loadedOrder->formatPriceTxt($adminSubtotal);
        $totals[] = $adminTotal;
        $orderData["totals"] = $totals;
        $this->returnArray["orderData"] = $orderData;
        $this->returnArray["state"] = $this->order->getState();
    }

    /**
     * Function to add ProductImage to the Item
     *
     * @param object $items items
     *
     * @return array
     */
    protected function addProductImages($items)
    {
        $productIds = [];
        foreach ($items as $key => $item) {
            $productIds[$key] = $item["productId"];
        }
        $collection = $this->productFactory->create()->getCollection()
            ->AddAttributeToSelect("image")
            ->addFieldToFilter("entity_id", ["in"=>$productIds]);
        foreach ($collection as $coll) {
            foreach ($items as $key => $item) {
                if ($item["productId"] == $coll->getEntityId()) {
                    $items[$key]["image"] = $this->helper->getUrl("media")."catalog/product".$coll->getImage();
                    continue;
                }
            }
        }
        return $items;
    }
}
