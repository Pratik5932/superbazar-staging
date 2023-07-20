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
 * Class ViewInvoice controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class ViewInvoice extends AbstractMarketplace
{
    /**
     * Execute function for class ViewInvoice
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "VIEWINVOICE".$this->storeId.$this->incrementId.$this->invoiceId;
            $cacheString .= $this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customerSession->setCustomerId($this->customerId);
            $order = $this->order->loadByIncrementId($this->incrementId);
            $orderId = $order->getId();
            $invoiceDetails = $this->_initInvoice($this->invoiceId, $order);
            if ($invoiceDetails["success"]) {
                $tracking = $invoiceDetails["tracking"];
                $invoice = $invoiceDetails["invoice"];
                $invoiceStatus = "";
                if ($invoice->getState() == 1) {
                    $invoiceStatus = __("Pending");
                } elseif ($invoice->getState() == 2) {
                    $invoiceStatus = __("Paid");
                } elseif ($invoice->getState() == 3) {
                    $invoiceStatus = __("Canceled");
                }
                $paymentCode = "";
                $paymentMethod = "";
                if ($order->getPayment()) {
                    $paymentCode = $order->getPayment()->getMethod();
                    $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
                }
                $this->returnArray["mainHeading"] = __("View Invoice Details");
                $this->returnArray["subHeading"] = __("Invoice #%1", $invoice->getIncrementId());
                $this->returnArray["invoiceStatus"] = $invoiceStatus;
                $this->returnArray["invoiceDate"] = $this->viewTemplate->formatDate(
                    $invoice->getCreatedAt(),
                    \IntlDateFormatter::MEDIUM,
                    true,
                    $this->viewTemplate->getTimezoneForStore($order->getStore())
                );
                $this->returnArray["actionButtons"] = [
                    [
                        "label"      => __("Send Email"),
                        "title"      => __("Send Email To Customer"),
                        "confirmMsg" => __("Are you sure you want to send invoice email to customer?")
                    ],
                    [
                        "label" => __("Print Invoice"),
                        "title" => __("Invoice Slip")
                    ]
                ];
                $this->returnArray["orderInfoHeading"] = __("Order Information");
                $this->returnArray["orderHeading"] = __("Order # %1", $order->getIncrementId());
                $this->returnArray["orderStatusHeading"] = __("Order Status");
                $this->returnArray["orderStatus"] = $order->getStatus();
                $this->returnArray["orderDateHeading"] = __("Order Date");
                $this->returnArray["orderDate"] = $this->viewTemplate->formatDate(
                    $order->getCreatedAt(),
                    \IntlDateFormatter::MEDIUM,
                    true,
                    $this->viewTemplate->getTimezoneForStore($order->getStore())
                );
                //Buyer Information
                if ($this->marketplaceHelper->getSellerProfileDisplayFlag()) {
                    $this->returnArray["showBuyerInformation"]   = true;
                    $this->returnArray["buyerInfoHeading"] = __("Buyer Information");
                    $this->returnArray["customerNameHeading"] = __("Customer Name");
                    $this->returnArray["customerName"] = $order->getCustomerName();
                    $this->returnArray["customerEmailHeading"] = __("Email");
                    $this->returnArray["customerEmail"] = $order->getCustomerEmail();
                }
                //Address Information
                if ($this->marketplaceHelper->getSellerProfileDisplayFlag()) {
                    $this->returnArray["addressinfoHeading"] = __("Address Information");
                    $this->returnArray["showAddressInformation"] = true;
                    $this->returnArray["billingAddressHeading"] = __("Billing Address");
                    $this->returnArray["billingAddress"] = $this->orderViewBlock->getFormattedAddress(
                        $order->getBillingAddress()
                    );
                    if ($this->orderViewBlock->isOrderCanShip($order)) {
                        $this->returnArray["shippingAddressHeading"] = __("Shipping Address");
                        $this->returnArray["shippingAddress"] = $this->orderViewBlock->getFormattedAddress(
                            $order->getShippingAddress()
                        );
                    }
                }
                //Payment & Shipping Method
                $this->returnArray["paymentAndShippingHeading"] = __("Payment & Shipping Method");
                $this->returnArray["paymentInfoHeading"] = __("Payment Information");
                $this->returnArray["paymentMethod"] = $paymentMethod;
                //Shipping Information
                if ($this->orderViewBlock->isOrderCanShip($order)) {
                    $this->returnArray["shippingInfoHeading"] = __("Shipping and Tracking Information");
                    if ($order->getShippingDescription()) {
                        $this->returnArray["shippingMethod"] = $this->viewTemplate->escapeHtml(
                            $order->getShippingDescription()
                        );
                    } else {
                        $this->returnArray["shippingMethod"] = __("No shipping information available");
                    }
                }
                $this->returnArray["itemInvoicedHeading"] = __("Items Invoiced");
                $orderCollection  = $this->marketplaceOrders->getCollection()
                    ->addFieldToFilter("order_id", $orderId)
                    ->addFieldToFilter("seller_id", $this->customerId);
                //Invoiced Items Details
                $this->itemBlock = $this->orderItemRenderer;
                $this->priceBlock = $this->priceRenderer;
                $items           = $order->getItemsCollection();
                $count           = $items->count();
                $subtotal         = 0;
                $totaltax         = 0;
                $couponamount     = 0;
                $admin_subtotal   = 0;
                $shippingamount   = 0;
                $vendor_subtotal  = 0;
                $codcharges_total = 0;
                $itemList         = [];
                $itemList = $this->getItemList(
                    $order,
                    $orderId,
                    $paymentCode
                );
                $this->returnArray["itemList"] = $itemList;
                $this->getInvoiceTotalsInformation();
                
                // getting totals data ///////////////////////////
                $adminTotalTax = $this->getTotalsData(
                    $orderCollection,
                    $order,
                    $totaltax,
                    $vendor_subtotal,
                    $codcharges_total,
                    $couponamount
                );
                $adminBaseCommission = '';
                if ($order->isCurrencyDifferent()) {
                    $adminBaseCommission = $order->formatBasePrice($admin_subtotal+$adminTotalTax);
                }
                $adminCommission = $this->helperCatalog->stripTags(
                    $order->formatPrice(
                        $this->dashboardHelper->getOrderedPricebyorder($order, ($admin_subtotal+$adminTotalTax))
                    )
                );
                $adminCommissionAmount = [
                    'code' => 'adminCommission',
                    'label' => __("Admin Commission"),
                    'value' => $this->dashboardHelper->getOrderedPricebyorder($order, ($admin_subtotal+$adminTotalTax)),
                    'formattedValue' => $adminCommission,
                    'formattedBaseTotal' => $adminBaseCommission
                ];
                $this->returnArray['totals'][] = $adminCommissionAmount;
                $this->returnArray["success"]   = true;
            } else {
                $this->returnArray["success"]   = false;
                $this->returnArray["message"]   = $shipmentDetails["message"]??"";
            }
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

    public function getItemList($order, $orderId, $paymentCode)
    {
        $items           = $order->getItemsCollection();
        $count           = $items->count();
        $subtotal         = 0;
        $totaltax         = 0;
        $couponamount     = 0;
        $admin_subtotal   = 0;
        $shippingamount   = 0;
        $vendor_subtotal  = 0;
        $codcharges_total = 0;
        $itemList         = [];
        foreach ($items as $_item) {
            $eachItem     = [];
            if ($_item->getParentItem()) {
                continue;
            }
            $row_total              = 0;
            $itemPrice              = 0;
            $couponcharges          = 0;
            $shippingcharges        = 0;
            $seller_item_cost       = 0;
            $totaltax_peritem       = 0;
            $codcharges_peritem     = 0;
            $available_seller_item  = 0;
            $seller_item_commission = 0;
            $seller_orderslist      = $this->orderViewBlock->getSellerOrdersList(
                $orderId,
                $_item->getProductId(),
                $_item->getItemId()
            );
            foreach ($seller_orderslist as $seller_item) {
                $itemPrice              = $seller_item->getMageproPrice();
                $totalamount            = $seller_item->getTotalAmount();
                $couponcharges          = $seller_item->getAppliedCouponAmount();
                $shippingcharges        = $seller_item->getShippingCharges();
                $seller_item_cost       = $seller_item->getActualSellerAmount();
                $totaltax_peritem       = $seller_item->getTotalTax();
                $available_seller_item  = 1;
                $seller_item_commission = $seller_item->getTotalCommission();
                if ($paymentCode == "mpcashondelivery") {
                    $codcharges_peritem = $seller_item->getCodCharges();
                }
            }
            if ($available_seller_item == 1) {
                $row_total        = $itemPrice*$_item->getQtyOrdered();
                $vendor_subtotal  = $vendor_subtotal+$seller_item_cost;
                $subtotal         = $subtotal+$row_total;
                $admin_subtotal   = $admin_subtotal+$seller_item_commission;
                $totaltax         = $totaltax+$totaltax_peritem;
                $codcharges_total = $codcharges_total+$codcharges_peritem;
                $shippingamount   = $shippingamount+$shippingcharges;
                $couponamount     = $couponamount+$couponcharges;
                $result           = [];
                if ($options = $_item->getProductOptions()) {
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
                // for bundle product //////////////////////////////////////////////////////////////////////////
                $bundleitems  = $this->arrayMerge([$_item], $_item->getChildrenItems());
                $_count       = count($bundleitems);
                $_index       = 0;
                $prevOptionId = "";
                if ($_item->getProductType() != "bundle") {
                    $eachItem["productName"]             = $this->viewTemplate->escapeHtml($_item->getName());
                    $eachItem["customOption"]            = [];
                    $eachItem["downloadableOptionLable"] = "";
                    $eachItem["downloadableOptionValue"] = [];
                    if ($_item->getProductType() == "downloadable") {
                        $eachItem = $this->getDownloadableItems(
                            $eachItem,
                            $result,
                            $_item,
                            $order,
                            $row_total,
                            $codcharges_peritem,
                            $seller_item_commission,
                            $seller_item_cost,
                            $paymentCode
                        );
                    } else {
                        $eachItem = $this->getProductItems(
                            $eachItem,
                            $result,
                            $_item,
                            $order,
                            $row_total,
                            $codcharges_peritem,
                            $seller_item_commission,
                            $seller_item_cost,
                            $paymentCode
                        );
                    }
                } else {
                    $eachItem = $this->getBondleItems(
                        $bundleitems,
                        $eachItem,
                        $order,
                        $_item,
                        $orderId,
                        $paymentCode
                    );
                }
            }
            if (!empty($eachItem)) {
                $itemList[] = $eachItem;
            }
        }
        return $itemList;
    }

    public function getTotalsData(
        $orderCollection,
        $order,
        $totaltax,
        $vendor_subtotal,
        $codcharges_total,
        $couponamount
    ) {
        $taxToSeller                = $this->marketplaceHelper->getConfigTaxManage();
        $totalTaxAmount             = 0;
        $totalCouponAmount          = 0;
        $refundedShippingAmount     = 0;
        foreach ($orderCollection as $tracking) {
            $taxToSeller            = $tracking["tax_to_seller"];
            $totalTaxAmount         = $tracking->getTotalTax();
            $shippingamount         = $tracking->getShippingCharges();
            $totalCouponAmount      = $tracking->getCouponAmount();
            $refundedShippingAmount = $tracking->getRefundedShippingCharges();
        }

        $shipping = $this->helperCatalog->stripTags(
            $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $shippingamount))
        );
        $shippingAmount = [
            'code' => 'shipping',
            'label' => __("Shipping"),
            'value' => $this->dashboardHelper->getOrderedPricebyorder($order, $shippingamount),
            'formattedValue' => $shipping
        ];
        $this->returnArray['totals'][] = $shippingAmount;
        $tax = $this->helperCatalog->stripTags(
            $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $totaltax))
        );
        $adminTotalTax  = 0;
        $vendorTotalTax = 0;
        if (!$taxToSeller) {
            $adminTotalTax = $totaltax;
        } else {
            $vendorTotalTax = $totaltax;
        }
        $taxAmount = [
            'code' => 'tax',
            'label' => __("Tax"),
            'value' => $this->dashboardHelper->getOrderedPricebyorder($order, $totaltax),
            'formattedValue' => $tax,
        ];
        $this->returnArray['totals'][] = $taxAmount;
        $this->returnArray['adminTotalTax'][] = $adminTotalTax;
        $this->returnArray['vendorTotalTax'][] = $vendorTotalTax;
        
        $vendorBaseTotal = "";
        if ($order->isCurrencyDifferent()) {
            $vendorBaseTotal = $order->formatPrice(
                $vendor_subtotal+
                $shippingamount+
                $codcharges_total+
                $vendorTotalTax-
                $refundedShippingAmount-
                $couponamount
            );
        }
        $vendorTotal = $this->helperCatalog->stripTags(
            $order->formatPrice(
                $this->dashboardHelper->getOrderedPricebyorder(
                    $order,
                    (
                        $vendor_subtotal+
                        $shippingamount+
                        $codcharges_total+
                        $vendorTotalTax-
                        $refundedShippingAmount-
                        $couponamount
                    )
                )
            )
        );
        $vendorTotalAmount = [
            'code' => 'vendorTotal',
            'label' => __("Vendor Total"),
            'value' => $this->dashboardHelper->getOrderedPricebyorder(
                $order,
                (
                    $vendor_subtotal+
                    $shippingamount+
                    $codcharges_total+
                    $vendorTotalTax-
                    $refundedShippingAmount-
                    $couponamount
                )
            ),
            'formattedValue' => $vendorTotal,
            'formattedBaseTotal' => $vendorBaseTotal
        ];
        $this->returnArray['totals'][] = $vendorTotalAmount;
        return $adminTotalTax;
    }

    public function getDownloadableItems(
        $eachItem,
        $result,
        $_item,
        $order,
        $row_total,
        $codcharges_peritem,
        $seller_item_commission,
        $seller_item_cost,
        $paymentCode
    ) {
        if ($options = $result) {
            $customOption = [];
            foreach ($options as $option) {
                $eachOption = [];
                $eachOption["label"] = $this->viewTemplate->escapeHtml($option["label"]);
                if (!$this->viewTemplate->getPrintStatus()) {
                    $formatedOptionValue = $this->orderViewBlock->getFormatedOptionValue(
                        $option
                    );
                    if (isset($formatedOptionValue["full_view"])) {
                        $eachOption["value"] = $formatedOptionValue["full_view"];
                    } else {
                        $eachOption["value"] = $formatedOptionValue["value"];
                    }
                } else {
                    $eachOption["value"] = $this->viewTemplate->escapeHtml(
                        (isset(
                            $option["print_value"]
                        ) ? $option["print_value"] : $option["value"])
                    );
                }
                $customOption[] = $eachOption;
            }
            $eachItem["customOption"] = $customOption;
        }
        // downloadable ////////////////////////////////////////////////////////////////////////
        if ($links = $this->orderViewBlock->getDownloadableLinks($_item->getId())) {
            $eachItem["downloadableOptionLable"] = $this->orderViewBlock->getLinksTitle(
                $_item->getId()
            );
            foreach ($links->getPurchasedItems() as $link) {
                $eachItem["downloadableOptionValue"][] = $this->viewTemplate->escapeHtml(
                    $link->getLinkTitle()
                );
            }
        }

        $eachItem["sku"]   = $_item->getSku();
        $eachItem["price"] = $this->helperCatalog->stripTags($order->formatPrice(
            $_item->getPrice()
        ));
        $itemQtys = [];
        if ($_item->getQtyOrdered() > 0) {
            $orderedQty          = [];
            $orderedQty["label"] = __("Ordered");
            $orderedQty["value"] = $_item->getQtyOrdered()*1;
            $itemQtys[]          = $orderedQty;
        }
        if ($_item->getQtyInvoiced() > 0) {
            $invoicedQty          = [];
            $invoicedQty["label"] = __("Invoiced");
            $invoicedQty["value"] = $_item->getQtyInvoiced()*1;
            $itemQtys[]           = $invoicedQty;
        }
        if ($_item->getQtyShipped() > 0) {
            $shippedQty          = [];
            $shippedQty["label"] = __("Shipped");
            $shippedQty["value"] = $_item->getQtyShipped()*1;
            $itemQtys[]          = $shippedQty;
        }
        if ($_item->getQtyCanceled() > 0) {
            $canceledQty          = [];
            $canceledQty["label"] = __("Canceled");
            $canceledQty["value"] = $_item->getQtyCanceled()*1;
            $itemQtys[]           = $canceledQty;
        }
        if ($_item->getQtyRefunded() > 0) {
            $refundedQty          = [];
            $refundedQty["label"] = __("Refunded");
            $refundedQty["value"] = $_item->getQtyRefunded()*1;
            $itemQtys[]           = $refundedQty;
        }
        $eachItem["qty"]        = $itemQtys;
        $eachItem["totalPrice"] = $this->helperCatalog->stripTags(
            $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $row_total))
        );
        $eachItem["mpcodprice"] = "";
        if ($paymentCode == "mpcashondelivery") {
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
            $order->formatPrice(
                $this->dashboardHelper->getOrderedPricebyorder($order, $seller_item_cost)
            )
        );
        $eachItem["subTotal"] = $this->helperCatalog->stripTags(
            $order->formatPrice(
                $this->dashboardHelper->getOrderedPricebyorder($order, $row_total)
            )
        );
        return $eachItem;
    }

    public function getProductItems(
        $eachItem,
        $result,
        $_item,
        $order,
        $row_total,
        $codcharges_peritem,
        $seller_item_commission,
        $seller_item_cost,
        $paymentCode
    ) {
        if ($options = $result) {
            $customOption = [];
            foreach ($options as $option) {
                $eachOption = [];
                $eachOption["label"] = $this->viewTemplate->escapeHtml($option["label"]);
                if (!$this->viewTemplate->getPrintStatus()) {
                    $formatedOptionValue = $this->orderViewBlock->getFormatedOptionValue(
                        $option
                    );
                    if (isset($formatedOptionValue["full_view"])) {
                        $eachOption["value"] = $formatedOptionValue["full_view"];
                    } else {
                        $eachOption["value"] = $formatedOptionValue["value"];
                    }
                } else {
                    $eachOption["value"] = $this->viewTemplate->escapeHtml(
                        (isset(
                            $option["print_value"]
                        ) ? $option["print_value"] : $option["value"])
                    );
                }
                $customOption[] = $eachOption;
            }
            $eachItem["customOption"] = $customOption;
        }

        $eachItem["sku"]   = $_item->getSku();
        $eachItem["price"] = $this->helperCatalog->stripTags($order->formatPrice(
            $_item->getPrice()
        ));
        $itemQtys = [];
        if ($_item->getQtyOrdered() > 0) {
            $orderedQty          = [];
            $orderedQty["label"] = __("Ordered");
            $orderedQty["value"] = $_item->getQtyOrdered()*1;
            $itemQtys[]          = $orderedQty;
        }
        if ($_item->getQtyInvoiced() > 0) {
            $invoicedQty          = [];
            $invoicedQty["label"] = __("Invoiced");
            $invoicedQty["value"] = $_item->getQtyInvoiced()*1;
            $itemQtys[]           = $invoicedQty;
        }
        if ($_item->getQtyShipped() > 0) {
            $shippedQty          = [];
            $shippedQty["label"] = __("Shipped");
            $shippedQty["value"] = $_item->getQtyShipped()*1;
            $itemQtys[]          = $shippedQty;
        }
        if ($_item->getQtyCanceled() > 0) {
            $canceledQty          = [];
            $canceledQty["label"] = __("Canceled");
            $canceledQty["value"] = $_item->getQtyCanceled()*1;
            $itemQtys[]           = $canceledQty;
        }
        if ($_item->getQtyRefunded() > 0) {
            $refundedQty          = [];
            $refundedQty["label"] = __("Refunded");
            $refundedQty["value"] = $_item->getQtyRefunded()*1;
            $itemQtys[]           = $refundedQty;
        }
        $eachItem["qty"]        = $itemQtys;
        $eachItem["totalPrice"] = $this->helperCatalog->stripTags(
            $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $row_total))
        );
        $eachItem["mpcodprice"] = "";
        if ($paymentCode == "mpcashondelivery") {
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
            $order->formatPrice(
                $this->dashboardHelper->getOrderedPricebyorder($order, $seller_item_cost)
            )
        );
        $eachItem["subTotal"] = $this->helperCatalog->stripTags(
            $order->formatPrice(
                $this->dashboardHelper->getOrderedPricebyorder($order, $row_total)
            )
        );

        return $eachItem;
    }

    public function getBondleItems($bundleitems, $eachItem, $order, $_item, $orderId, $paymentCode)
    {
        foreach ($bundleitems as $_bundleitem) {
            $attributes_option = $this->orderViewBlock->getSelectionAttribute($_bundleitem);
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
                $eachItem["price"] = $order->formatPrice($_item->getPrice());
                $itemQtys = [];
                if ($_item->getQtyOrdered() > 0) {
                    $orderedQty          = [];
                    $orderedQty["label"] = __("Ordered");
                    $orderedQty["value"] = $_item->getQtyOrdered()*1;
                    $itemQtys[]          = $orderedQty;
                }
                $eachItem["qty"]         = $itemQtys;
            } else {
                $row_total              = 0;
                $itemPrice              = 0;
                $couponcharges          = 0;
                $shippingcharges        = 0;
                $seller_item_cost       = 0;
                $totaltax_peritem       = 0;
                $codcharges_peritem     = 0;
                $available_seller_item  = 0;
                $seller_item_commission = 0;
                $seller_orderslist      = $this->orderViewBlock->getSellerOrdersList(
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
                    $totaltax_peritem       = $seller_item->getTotalTax();
                    if ($paymentCode == "mpcashondelivery") {
                        $codcharges_peritem = $seller_item->getCodCharges();
                    }
                }
                $seller_item_qty  = $_bundleitem->getQtyOrdered();
                $row_total        = $itemPrice*$seller_item_qty;
                $vendor_subtotal  = $vendor_subtotal+$seller_item_cost;
                $subtotal         = $subtotal+$row_total;
                $admin_subtotal   = $admin_subtotal+$seller_item_commission;
                $totaltax         = $totaltax+$totaltax_peritem;
                $codcharges_total = $codcharges_total+$codcharges_peritem;
                $shippingamount   = $shippingamount+$shippingcharges;
                $couponamount     = $couponamount+$couponcharges;
                $eachItem["productName"] = $this->orderViewBlock->getValueHtml($_bundleitem);
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
                $eachItem["totalPrice"]      = $this->helperCatalog->stripTags(
                    $order->formatPrice(
                        $this->dashboardHelper->getOrderedPricebyorder($order, $row_total)
                    )
                );
                $eachItem["mpcodprice"]      = "";
                if ($paymentCode == "mpcashondelivery") {
                    $eachItem["mpcodprice"]  = $this->helperCatalog->stripTags(
                        $order->formatPrice(
                            $this->dashboardHelper->getOrderedPricebyorder(
                                $order,
                                $codcharges_peritem
                            )
                        )
                    );
                }
                $eachItem["adminCommission"] = $this->helperCatalog->stripTags(
                    $order->formatPrice(
                        $this->dashboardHelper->getOrderedPricebyorder(
                            $order,
                            $seller_item_commission
                        )
                    )
                );
                $eachItem["vendorTotal"]     = $this->helperCatalog->stripTags(
                    $order->formatPrice(
                        $this->dashboardHelper->getOrderedPricebyorder($order, $seller_item_cost)
                    )
                );
                $eachItem["subTotal"]        = $this->helperCatalog->stripTags(
                    $order->formatPrice(
                        $this->dashboardHelper->getOrderedPricebyorder($order, $row_total)
                    )
                );
            }
        }
        return $eachItem;
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
            $this->incrementId   = $this->wholeData["incrementId"]   ?? 0;
            $this->invoiceId     = $this->wholeData["invoiceId"]     ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
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
     * Initialize invoice model instance.
     *
     * @param int   $invoiceId invoice id
     * @param order $order     order
     *
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface|false
     */
    private function _initInvoice($invoiceId, $order)
    {
        $data = [];
        $data["success"] = false;
        if (!$invoiceId) {
            return $data;
        }
        $invoice = $this->invoiceRepository->get($invoiceId);
        if (!$invoice) {
            return $data;
        }
        $tracking = $this->marketplaceOrderhelper->getOrderinfo($order->getId());
        if ($tracking && $tracking->getId()) {
            if ($tracking->getInvoiceId() == $invoiceId) {
                if (!$invoiceId) {
                    $data["message"] = __("The invoice no longer exists.");
                    throw new \NoSuchEntityException($data['message']);
                }
            } else {
                $data["message"] = __("You are not authorize to view this invoice.");
                throw new \InputException($data['message']);
            }
        } else {
            $data["message"] = __("You are not authorize to view this invoice.");
            throw new \InputException($data['message']);
        }
        $this->coreRegistry->register("sales_order", $order);
        $this->coreRegistry->register("current_order", $order);
        $this->coreRegistry->register("current_invoice", $invoice);
        $data["success"] = true;
        $data["invoice"] = $invoice;
        $data["tracking"] = $tracking;
        return $data;
    }

    /**
     * Function to get totals information
     *
     * @return void
     */
    public function getInvoiceTotalsInformation()
    {
        $this->invoiceTotals->_initTotals();
        $footerTotals = $this->invoiceTotals->getTotals('footer');
        if ($footerTotals) {
            foreach ($footerTotals as $total) {
                $eachItem["code"] = $this->invoiceTotals->escapeHtml($total->getCode());
                $eachItem["label"] = $this->invoiceTotals->escapeHtml($total->getLabel());
                $eachItem["value"] = $total->getValue();
                $eachItem["formattedValue"] = strip_tags($this->invoiceTotals->formatValue($total));
                $this->returnArray["totals"][] = $eachItem;
            }
        }
        $invoiceTotals = $this->invoiceTotals->getTotals("");
        if ($invoiceTotals) {
            foreach ($invoiceTotals as $total) {
                $label = $total->getLabel();
                $eachItem["code"] = $this->invoiceTotals->escapeHtml($total->getCode());
                $eachItem["label"] = $this->invoiceTotals->escapeHtml($label);
                $eachItem["value"] = $total->getValue();
                $eachItem["formattedValue"] = strip_tags($this->invoiceTotals->formatValue($total));
                $this->returnArray["totals"][] = $eachItem;
                if ($label == "Grand Total") {
                    $this->returnArray["cartTotal"] = strip_tags($this->invoiceTotals->formatValue($total));
                }
            }
        }
    }
}
