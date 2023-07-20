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
 * Class CreditMemoFormData controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class CreditMemoFormData extends AbstractMarketplace
{
    private $paymentCode = '';
    private $subtotal = 0;
    private $totaltax = 0;
    private $codcharges_total = 0;
    /**
     * Execute function for class CreditMemoFormData
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
            $order   = $this->order->loadByIncrementId($this->incrementId);
            $orderId = $order->getId();
            $this->returnArray['mainHeading'] = __('New Credit Memo');
            $this->returnArray['subHeading'] = __('Order #%1', $order->getRealOrderId());
            $paymentCode = '';
            $paymentMethod = '';
            $orderStatusLabel = $order->getStatusLabel();
            if ($order->getPayment()) {
                $this->paymentCode = $paymentCode = $order->getPayment()->getMethod();
                $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
            }
            $tracking = $this->marketplaceOrderhelper->getOrderinfo($orderId);
            $invoiceId = $tracking->getInvoiceId();
            if ($tracking != "" && $paymentCode == 'mpcashondelivery') {
                $codcharges = $tracking->getCodCharges();
            }
            $isCanceled = $tracking->getIsCanceled();
            if ($isCanceled) {
                $orderStatusLabel='Canceled';
            }
            $this->returnArray['status'] = $orderStatusLabel;
            $this->returnArray['orderDate'] = $this->viewTemplate->formatDate(
                $order->getCreatedAt(),
                \IntlDateFormatter::MEDIUM,
                true,
                $this->viewTemplate->getTimezoneForStore($order->getStore())
            );

            if ($this->marketplaceHelper->getSellerProfileDisplayFlag()) {
                $this->returnArray['buyerInfoHeading'] = __('Buyer Information');
                $this->returnArray['customerNameHeading'] = __('Customer Name');
                $this->returnArray['customerName'] = $order->getCustomerName();
                $this->returnArray['customerEmailHeading'] = __('Email');
                $this->returnArray['customerEmail'] = $order->getCustomerEmail();
            }
            $this->returnArray['orderInfoHeading'] = __('Order Information');
            if ($this->marketplaceHelper->getSellerProfileDisplayFlag()) {
                if ($this->orderViewBlock->isOrderCanShip($order)) {
                    $this->returnArray['shippingAddressHeading'] = __('Shipping Address');
                    $this->returnArray["shippingAddress"] = $this->orderViewBlock->getFormattedAddress(
                        $order->getShippingAddress()
                    );
                    $this->returnArray['shippingMethodHeading'] = __('Shipping Method');
                    if ($order->getShippingDescription()) {
                        $this->returnArray["shippingMethod"] = $this->viewTemplate->escapeHtml(
                            $order->getShippingDescription()
                        );
                    } else {
                        $this->returnArray["shippingMethod"] = __("No shipping information available");
                    }
                    $this->returnArray['billingAddressHeading'] = __('Billing Address');
                    $this->returnArray["billingAddress"] = $this->orderViewBlock->getFormattedAddress(
                        $order->getBillingAddress()
                    );
                    
                }
            }
            $orderCollection  = $this->marketplaceOrders->getCollection()
                ->addFieldToFilter("order_id", $orderId)
                ->addFieldToFilter("seller_id", $this->customerId);
            if ($orderCollection->getSize() > 0) {
                $this->returnArray['paymentMethodHeading'] = __('Payment Method');
                $this->returnArray['paymentMethod'] = $paymentMethod;
                $this->returnArray['itemsRefundHeading'] = __('ITEMS TO REFUND');
                $this->returnArray['itemsProductNameHeading'] = __('Product Name');
                $this->returnArray['itemsPiceHeading'] = __('Price');
                $this->returnArray['itemsQtyHeading'] = __('Qty');
                $this->returnArray['itemsReturnToStockHeading'] = __('Return to Stock');
                $this->returnArray['itemsQtyToRefundHeading'] = __('Qty to Refund');
                $this->returnArray['itemsSubtotalHeading'] = __('Subtotal');
                $this->returnArray['itemsCodHeading'] = __('COD Charges');
                $this->returnArray['itemsTaxAmtHeading'] = __('Tax Amount');
                $this->returnArray['itemsDiscountAmtHeading'] = __('Discount Amount');
                $this->returnArray['itemsRowTotalHeading'] = __('Row Total');
                $this->getOrderItemsList($order);
                //Totals data
                $this->returnArray["paidAmountHeading"] = __('Paid Amount');
                $this->returnArray["refundAmountHeading"] = __('Refund Amount');
                $this->returnArray["shippingAmountHeading"] = __('Shipping Amount');
                $this->returnArray["shippingRefundHeading"] = __('Shipping Refund');
                $this->returnArray["orderGrandTotalHeading"] = __('Order Grand Total');
                $taxToSeller = $this->marketplaceHelper->getConfigTaxManage();
                $totalTaxAmount             = 0;
                $totalCouponAmount          = 0;
                $refundedShippingAmount     = 0;
                foreach ($orderCollection as $orderdata) {
                    $taxToSeller            = $orderdata["tax_to_seller"];
                    $totalTaxAmount         = $orderdata->getTotalTax();
                    $shippingamount         = $orderdata->getShippingCharges();
                    $totalCouponAmount      = $orderdata->getCouponAmount();
                    $refundedShippingAmount = $orderdata->getRefundedShippingCharges();
                }
                $creditmemoIds = [];
                $creditmemoTotalAmount = 0;
                $creditmemoIds = explode(',', $tracking->getCreditmemoId());
                $creditmemoCollection = $this->orderViewBlock->getOrderCreditmemo($creditmemoIds);
                foreach ($creditmemoCollection as $creditmemo) {
                    $creditmemoTotalAmount = $creditmemoTotalAmount + $creditmemo['grand_total'];
                }
                $invoice = $this->orderViewBlock->getOrderInvoice($invoiceId);
                $invoicePaidAmount = $invoice->getGrandTotal();
                $this->returnArray["paidAmount"] = $this->helperCatalog->stripTags(
                    $order->formatPrice($invoicePaidAmount)
                );
                $this->returnArray["refundAmount"] = $this->helperCatalog->stripTags(
                    $order->formatPrice($creditmemoTotalAmount)
                );
                $this->returnArray["shippingAmount"] = $this->helperCatalog->stripTags(
                    $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $shippingamount))
                );
                $this->returnArray["shippingRefund"] = $this->helperCatalog->stripTags(
                    $order->formatPrice(
                        $this->dashboardHelper->getOrderedPricebyorder($order, $refundedShippingAmount)
                    )
                );
                $this->returnArray["orderGrandTotal"] = $this->helperCatalog->stripTags(
                    $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $invoicePaidAmount))
                );
            }
            $this->returnArray["creditMemoCommentHeading"] = __('Credit Memo Comments');
            $this->returnArray["refundTotalHeading"] = __('Refund Totals');
            $this->returnArray["subtotalHeading"] = __('Subtotal');
            $this->returnArray["subTotal"] = $this->helperCatalog->stripTags($order->formatBasePrice($this->subtotal));
            $this->returnArray["discountHeading"] = __('Discount');
            $this->returnArray["discount"] = $this->helperCatalog->stripTags(
                $order->formatBasePrice($totalCouponAmount)
            );
            $this->returnArray["totalTaxHeading"] = __('Total Tax');
            $this->returnArray["totalTax"] = $this->helperCatalog->stripTags($order->formatBasePrice($this->totaltax));
            $this->returnArray["refundShippingHeading"] = __('Refund Shipping');
            $this->returnArray["adjustmentRefundHeading"] = __('Adjustment Refund');
            $this->returnArray["adjustmentFeeHeading"] = __('Adjustment Fee');
            $this->returnArray["grandTotalHeading"] = __('Grand Total');
            $this->returnArray["grandTotal"] = $this->helperCatalog->stripTags(
                $order->formatBasePrice(
                    $this->subtotal+
                    $shippingamount+
                    $this->codcharges_total+
                    $this->totaltax-
                    $refundedShippingAmount-
                    $totalCouponAmount
                )
            );
            $this->returnArray["invoiceId"] = $invoiceId;
            $this->returnArray["appendCommentsHeading"] = __('Append Comments');
            $this->returnArray["visibleOnFrontendHeading"] = __('Visible on Frontend');
            $this->returnArray["emailCopyHeading"] = __('Email Copy of Credit Memo');
            $this->returnArray["refundOnlineButtonHeading"] = __('Refund');
            $this->returnArray["refundOfflineButtonHeading"] = __('Refund Offline');
            if ($invoice && $invoice->getTransactionId()) {
                $this->returnArray["refundOnlineEnableFlag"] = true;
            }
            $this->returnArray["success"]   = true;
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

    public function arrayMerge($arr1, $arr2)
    {
        return array_merge($arr1, $arr2);
    }

    /**
     * Function to get Order Items List
     *
     * @param \Magento\Sales\Model\order $order order
     *
     * @return array item list
     */
    public function getOrderItemsList($order)
    {
        $items           = $order->getItemsCollection();
        $count           = $items->count();
        $this->subtotal  = 0;
        $this->totaltax  = 0;
        $couponamount     = 0;
        $admin_subtotal   = 0;
        $shippingamount   = 0;
        $vendor_subtotal  = 0;
        $this->codcharges_total = 0;
        $itemList         = [];
        $orderId          = $order->getId();
        foreach ($items as $item) {
            $eachItem     = [];
            if ($item->getParentItem()) {
                continue;
            }
            $row_total              = 0;
            $itemPrice              = 0;
            $couponcharges          = 0;
            $shippingcharges        = 0;
            $seller_item_cost       = 0;
            $this->totaltax_peritem = 0;
            $codcharges_peritem     = 0;
            $available_seller_item  = 0;
            $seller_item_commission = 0;
            $seller_orderslist      = $this->orderViewBlock->getSellerOrdersList(
                $orderId,
                $item->getProductId(),
                $item->getItemId()
            );
            
            foreach ($seller_orderslist as $seller_item) {
                $itemPrice              = $seller_item->getMageproPrice();
                $totalamount            = $seller_item->getTotalAmount();
                $couponcharges          = $seller_item->getAppliedCouponAmount();
                $shippingcharges        = $seller_item->getShippingCharges();
                $seller_item_cost       = $seller_item->getActualSellerAmount();
                $this->totaltax_peritem       = $seller_item->getTotalTax();
                $available_seller_item  = 1;
                $seller_item_commission = $seller_item->getTotalCommission();
                if ($this->paymentCode == "mpcashondelivery") {
                    $codcharges_peritem = $seller_item->getCodCharges();
                }
            }
            if ($available_seller_item == 1) {
                $seller_item_qty = $item->getQtyToRefund();
                if ($item->getProductType()!='bundle') {
                    $row_total=$itemPrice*$seller_item_qty;
                } else {
                    $row_total=$totalamount;
                }
                $vendor_subtotal  = $vendor_subtotal+$seller_item_cost;
                $this->subtotal         = $this->subtotal+$row_total;
                $admin_subtotal   = $admin_subtotal+$seller_item_commission;
                $this->totaltax         = $this->totaltax+$this->totaltax_peritem;
                $this->codcharges_total = $this->codcharges_total+$codcharges_peritem;
                $shippingamount   = $shippingamount+$shippingcharges;
                $couponamount     = $couponamount+$couponcharges;
                $result           = [];
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
                // for bundle product //////////////////////////////////////////////////////////////////////////////////
                $bundleitems  = $this->arrayMerge([$item], $item->getChildrenItems());
                $_count       = count($bundleitems);
                $_index       = 0;
                $prevOptionId = "";
                if ($item->getProductType() != "bundle") {
                    $eachItem = $this->getBundleItems(
                        $item,
                        $eachItem,
                        $result,
                        $order,
                        $row_total,
                        $seller_item_commission,
                        $seller_item_cost,
                        $codcharges_peritem,
                        $couponamount
                    );
                } else {
                    $eachItem = $this->getProductItems(
                        $bundleitems,
                        $eachItem,
                        $result,
                        $order,
                        $row_total,
                        $seller_item_commission,
                        $seller_item_cost,
                        $codcharges_peritem,
                        $couponamount
                    );
                }
            }
            if (!empty($eachItem)) {
                $itemList[] = $eachItem;
            }
        }
        $this->returnArray["itemList"] = $itemList;
    }

    public function getBundleItems(
        $item,
        $eachItem,
        $result,
        $order,
        $row_total,
        $seller_item_commission,
        $seller_item_cost,
        $codcharges_peritem,
        $couponamount
    ) {
        $eachItem["productName"]             = $this->viewTemplate->escapeHtml($item->getName());
        $eachItem["customOption"]            = [];
        $eachItem["downloadableOptionLable"] = "";
        $eachItem["downloadableOptionValue"] = [];
        if ($item->getProductType() == "downloadable") {
            if ($options = $result) {
                $customOption = [];
                foreach ($options as $option) {
                    $eachOption = [];
                    $eachOption["label"] = $this->viewTemplate->escapeHtml($option["label"]);
                    if (!$this->viewTemplate->getPrintStatus()) {
                        $formatedOptionValue = $this->orderViewBlock->getFormatedOptionValue($option);
                        if (isset($formatedOptionValue["full_view"])) {
                            $eachOption["value"] = $formatedOptionValue["full_view"];
                        } else {
                            $eachOption["value"] = $formatedOptionValue["value"];
                        }
                    } else {
                        $eachOption["value"] = $this->viewTemplate->escapeHtml(
                            (isset($option["print_value"]) ? $option["print_value"] : $option["value"])
                        );
                    }
                    $customOption[] = $eachOption;
                }
                $eachItem["customOption"] = $customOption;
            }
            // downloadable ////////////////////////////////////////////////////////////////////////////////
            if ($links = $this->orderViewBlock->getDownloadableLinks($item->getId())) {
                $eachItem["downloadableOptionLable"] = $this->orderViewBlock->getLinksTitle($item->getId());
                foreach ($links->getPurchasedItems() as $link) {
                    $eachItem["downloadableOptionValue"][] = $this->viewTemplate->escapeHtml(
                        $link->getLinkTitle()
                    );
                }
            }
        } else {
            if ($options = $result) {
                $customOption = [];
                foreach ($options as $option) {
                    $eachOption = [];
                    $eachOption["label"] = $this->viewTemplate->escapeHtml($option["label"]);
                    if (!$this->viewTemplate->getPrintStatus()) {
                        $formatedOptionValue = $this->orderViewBlock->getFormatedOptionValue($option);
                        if (isset($formatedOptionValue["full_view"])) {
                            $eachOption["value"] = $formatedOptionValue["full_view"];
                        } else {
                            $eachOption["value"] = $formatedOptionValue["value"];
                        }
                    } else {
                        $eachOption["value"] = $this->viewTemplate->escapeHtml(
                            (isset($option["print_value"]) ? $option["print_value"] : $option["value"])
                        );
                    }
                    $customOption[] = $eachOption;
                }
                $eachItem["customOption"] = $customOption;
            }
        }
        $eachItem["sku"]   = $item->getSku();
        $eachItem["price"] = $this->helperCatalog->stripTags($order->formatPrice($item->getPrice()));
        $product = $item->getProduct();
        $brandAttributeCode = $this->helper->getConfigData('amshopby_brand/general/attribute_code');
        $brandValue = $product->getResource()->getAttribute($brandAttributeCode);
        if (isset($brandValue) && $brandValue) {
            $brandValue = $brandValue->getFrontend()->getValue($product);
        }
        $eachItem["brandName"] = $brandValue ? $brandValue : "";
        $itemQtys = [];
        if ($item->getQtyOrdered() > 0) {
            $orderedQty          = [];
            $orderedQty["label"] = __("Ordered");
            $orderedQty["value"] = $item->getQtyOrdered()*1;
            $itemQtys[]          = $orderedQty;
        }
        if ($item->getQtyInvoiced() > 0) {
            $invoicedQty          = [];
            $invoicedQty["label"] = __("Invoiced");
            $invoicedQty["value"] = $item->getQtyInvoiced()*1;
            $itemQtys[]           = $invoicedQty;
        }
        if ($item->getQtyShipped() > 0) {
            $shippedQty          = [];
            $shippedQty["label"] = __("Shipped");
            $shippedQty["value"] = $item->getQtyShipped()*1;
            $itemQtys[]          = $shippedQty;
        }
        if ($item->getQtyCanceled() > 0) {
            $canceledQty          = [];
            $canceledQty["label"] = __("Canceled");
            $canceledQty["value"] = $item->getQtyCanceled()*1;
            $itemQtys[]           = $canceledQty;
        }
        if ($item->getQtyRefunded() > 0) {
            $refundedQty          = [];
            $refundedQty["label"] = __("Refunded");
            $refundedQty["value"] = $item->getQtyRefunded()*1;
            $itemQtys[]           = $refundedQty;
        }
        $eachItem["qty"]        = $itemQtys;
        $eachItem["totalPrice"] = $this->helperCatalog->stripTags(
            $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $row_total))
        );
        $eachItem["mpcodprice"] = "";
        if ($this->paymentCode == "mpcashondelivery") {
            $eachItem["mpcodprice"] = $this->helperCatalog->stripTags(
                $order->formatPrice(
                    $this->dashboardHelper->getOrderedPricebyorder($order, $codcharges_peritem)
                )
            );
        }
        $eachItem["adminCommission"] = $this->helperCatalog->stripTags(
            $order->formatPrice(
                $this->dashboardHelper->getOrderedPricebyorder($order, $seller_item_commission)
            )
        );
        $eachItem["vendorTotal"] = $this->helperCatalog->stripTags(
            $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $seller_item_cost))
        );
        $eachItem["subTotal"] = $this->helperCatalog->stripTags(
            $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $row_total))
        );
        $eachItem["totalTax"] = $this->helperCatalog->stripTags(
            $order->formatPrice(
                $this->dashboardHelper->getOrderedPricebyorder($order, $this->totaltax_peritem)
            )
        );
        $eachItem["rowTotal"] = $this->helperCatalog->stripTags($order->formatPrice(
            $this->dashboardHelper->getOrderedPricebyorder(
                $order,
                $row_total+$this->totaltax_peritem+$codcharges_peritem-$couponamount
            )
        ));
        $eachItem["discount"] = $this->helperCatalog->stripTags(
            $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $couponamount))
        );
        $eachItem["itemId"]   = $item->getItemId();
        return $eachItem;
    }

    public function getProductItems(
        $bundleitems,
        $eachItem,
        $result,
        $order,
        $row_total,
        $seller_item_commission,
        $seller_item_cost,
        $codcharges_peritem,
        $couponamount
    ) {
        foreach ($bundleitems as $_bundleitem) {
            $attributes_option = $block->getSelectionAttribute($_bundleitem);
            if ($_bundleitem->getParentItem()) {
                $attributes = $attributes_option;
                if ($prevOptionId != $attributes["option_id"]) {
                    $eachItem["productName"] = $attributes["option_label"];
                    $prevOptionId = $attributes["option_id"];
                }
            }
            if (!$_bundleitem->getParentItem()) {
                $eachItem["productName"] = $this->viewTemplate->escapeHtml($_bundleitem->getName());
                $eachItem["sku"] = $_bundleitem->getSku();
                $eachItem["price"] = $order->formatPrice($item->getPrice());
                $itemQtys = [];
                if ($item->getQtyOrdered() > 0) {
                    $orderedQty          = [];
                    $orderedQty["label"] = __("Ordered");
                    $orderedQty["value"] = $item->getQtyOrdered()*1;
                    $itemQtys[]          = $orderedQty;
                }
                $eachItem["qty"]         = $itemQtys;
            } else {
                $row_total              = 0;
                $itemPrice              = 0;
                $couponcharges          = 0;
                $shippingcharges        = 0;
                $seller_item_cost       = 0;
                $this->totaltax_peritem = 0;
                $codcharges_peritem     = 0;
                $available_seller_item  = 0;
                $seller_item_commission = 0;
                $seller_orderslist      = $block->getSellerOrdersList(
                    $orderId,
                    $_bundleitem->getProductId(),
                    $_bundleitem->getItemId()
                );
                foreach ($seller_orderslist as $seller_item) {
                    $available_seller_item  = 1;
                    $totalamount            = $seller_item->getTotalAmount();
                    $seller_item_cost       = $seller_item->getActualSellerAmount();
                    $seller_item_commission = $seller_item->getTotalCommission();
                    $shippingcharges        = $seller_item->getShippingCharges();
                    $couponcharges          = $seller_item->getAppliedCouponAmount();
                    $itemPrice              = $seller_item->getMageproPrice();
                    $this->totaltax_peritem = $seller_item->getTotalTax();
                    if ($this->paymentCode == "mpcashondelivery") {
                        $codcharges_peritem = $seller_item->getCodCharges();
                    }
                }
                if ($available_seller_item == 1) {
                    $seller_item_qty  = $item->getQtyToRefund();
                    if ($item->getProductType()!='bundle') {
                        $row_total=$itemPrice*$seller_item_qty;
                    } else {
                        $row_total=$totalamount;
                    }
                    $vendor_subtotal  = $vendor_subtotal+$seller_item_cost;
                    $this->subtotal   = $this->subtotal+$row_total;
                    $admin_subtotal   = $admin_subtotal+$seller_item_commission;
                    $this->totaltax   = $this->totaltax+$this->totaltax_peritem;
                    $this->codcharges_total = $this->codcharges_total+$codcharges_peritem;
                    $shippingamount   = $shippingamount+$shippingcharges;
                    $couponamount     = $couponamount+$couponcharges;
                    $eachItem["productName"] = $this->orderViewBlock->getValueHtml($_bundleitem);
                    // $addInfoBlock = $block->getOrderItemAdditionalInfoBlock();
                    // if ($addInfoBlock)
                    //     $addInfoBlock->setItem($_bundleitem)->toHtml();
                    $eachItem["sku"] = $_bundleitem->getSku();
                    $eachItem["price"] = $order->formatPrice($_bundleitem->getPrice());
                    $itemQtys = [];
                    if ($_bundleitem->getQtyOrdered() > 0) {
                        $orderedQty          = [];
                        $orderedQty["label"] = __("Ordered");
                        $orderedQty["value"] = $_bundleitem->getQtyOrdered()*1;
                        $itemQtys[]          = $orderedQty;
                    }
                    if ($_bundleitem->getQtyInvoiced() > 0) {
                        $invoicedQty          = [];
                        $invoicedQty["label"] = __("Invoiced");
                        $invoicedQty["value"] = $_bundleitem->getQtyInvoiced()*1;
                        $itemQtys[]           = $invoicedQty;
                    }
                    if ($_bundleitem->getQtyShipped() > 0) {
                        $shippedQty          = [];
                        $shippedQty["label"] = __("Shipped");
                        $shippedQty["value"] = $_bundleitem->getQtyShipped()*1;
                        $itemQtys[]          = $shippedQty;
                    }
                    if ($_bundleitem->getQtyCanceled() > 0) {
                        $canceledQty          = [];
                        $canceledQty["label"] = __("Canceled");
                        $canceledQty["value"] = $_bundleitem->getQtyCanceled()*1;
                        $itemQtys[]           = $canceledQty;
                    }
                    if ($_bundleitem->getQtyRefunded() > 0) {
                        $refundedQty          = [];
                        $refundedQty["label"] = __("Refunded");
                        $refundedQty["value"] = $_bundleitem->getQtyRefunded()*1;
                        $itemQtys[]           = $refundedQty;
                    }
                    $eachItem["qty"]             = $itemQtys;
                    $eachItem["qtyToRefund"]     = $item->getQtyToRefund();
                    $eachItem["totalPrice"]      = $this->helperCatalog->stripTags(
                        $order->formatPrice(
                            $this->dashboardHelper->getOrderedPricebyorder($order, $row_total)
                        )
                    );
                    $eachItem["mpcodprice"]      = "";
                    $eachItem["vendorTotal"]     = $this->helperCatalog->stripTags(
                        $order->formatPrice(
                            $this->dashboardHelper->getOrderedPricebyorder($order, $seller_item_cost)
                        )
                    );
                    if ($this->paymentCode == "mpcashondelivery") {
                        $eachItem["mpcodprice"]  = $this->helperCatalog->stripTags(
                            $order->formatPrice(
                                $this->dashboardHelper->getOrderedPricebyorder($order, $codcharges_peritem)
                            )
                        );
                    }
                    $eachItem["adminCommission"] = $this->helperCatalog->stripTags(
                        $order->formatPrice(
                            $this->dashboardHelper->getOrderedPricebyorder($order, $seller_item_commission)
                        )
                    );
                    $eachItem["subTotal"]        = $this->helperCatalog->stripTags(
                        $order->formatPrice(
                            $this->dashboardHelper->getOrderedPricebyorder($order, $row_total)
                        )
                    );
                    $eachItem["totalTax"]        = $this->helperCatalog->stripTags(
                        $order->formatPrice(
                            $this->dashboardHelper->getOrderedPricebyorder($order, $this->totaltax_peritem)
                        )
                    );
                    $eachItem["rowTotal"]        = $this->helperCatalog->stripTags(
                        $order->formatPrice(
                            $this->dashboardHelper->getOrderedPricebyorder(
                                $order,
                                $row_total+$this->totaltax_peritem+$codcharges_peritem-$couponamount
                            )
                        )
                    );
                    $eachItem["discount"]        = $this->helperCatalog->stripTags(
                        $order->formatPrice(
                            $this->dashboardHelper->getOrderedPricebyorder($order, $couponamount)
                        )
                    );
                    $eachItem["itemId"]          = $_bundleitem->getItemId();
                }
            }
        }
        return $eachItem;
    }
}
