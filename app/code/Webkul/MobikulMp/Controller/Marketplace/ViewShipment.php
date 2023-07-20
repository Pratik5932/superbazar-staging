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
 * Class ViewShipment for viewing the shipment details
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class ViewShipment extends AbstractMarketplace
{
    /**
     * Execute function for class ViewShipment
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "VIEWSHIPMENT".$this->storeId.$this->incrementId.$this->shipmentId;
            $cacheString .= $this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customerSession->setCustomerId($this->customerId);
            $order           = $this->order->loadByIncrementId($this->incrementId);
            $orderId         = $order->getId();
            $shipmentDetails = $this->_initShipment($this->shipmentId, $order);
            if ($shipmentDetails['success']) {
                $tracking = $shipmentDetails['tracking'];
                $shipment = $shipmentDetails['shipment'];
                $paymentCode = '';
                $paymentMethod = '';
                if ($order->getPayment()) {
                    $paymentCode = $order->getPayment()->getMethod();
                    $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
                }
                $this->returnArray["mainHeading"] = __('View Shipment Details');
                $this->returnArray["subHeading"] =  __('Shipment #%1', $shipment->getIncrementId());
                $this->returnArray["shipmentIncrementedId"] = $shipment->getIncrementId();
                $this->returnArray["shipmentDate"] = $this->viewTemplate->formatDate(
                    $shipment->getCreatedAt(),
                    \IntlDateFormatter::MEDIUM,
                    true,
                    $this->viewTemplate->getTimezoneForStore($order->getStore())
                );
                $this->returnArray["actionButtons"] = [
                    [
                        "label"      => __('Send Tracking Information'),
                        "title"      => __('Send Email To Customer'),
                        "confirmMsg" => __("Are you sure you want to send shipment email to customer?")
                    ],
                    [
                        "label" => __('Print Shipment'),
                        "title" => __('Shipment Slip')
                    ]
                ];
                //Order Information
                $this->returnArray["orderInfoHeading"] = __('Order Information');
                $this->returnArray["orderHeading"] = __('Order # %1', $order->getIncrementId());
                $this->returnArray["orderIncrementedId"] = $order->getIncrementId();
                $this->returnArray["orderStatusHeading"] = __('Order Status');
                $this->returnArray["orderStatus"] = $order->getStatus();
                $this->returnArray["orderDateHeading"] = __('Order Date');
                $this->returnArray["orderDate"] = $this->viewTemplate->formatDate(
                    $order->getCreatedAt(),
                    \IntlDateFormatter::MEDIUM,
                    true,
                    $this->viewTemplate->getTimezoneForStore(
                        $order->getStore()
                    )
                );
                //Buyer Information
                if ($this->marketplaceHelper->getSellerProfileDisplayFlag()) {
                    $this->returnArray["showBuyerInformation"]   = true;
                    $this->returnArray["buyerInfoHeading"] = __('Buyer Information');
                    $this->returnArray["customerNameHeading"] = __('Customer Name');
                    $this->returnArray["customerName"] = $order->getCustomerName();
                    $this->returnArray["customerEmailHeading"] = __('Email');
                    $this->returnArray["customerEmail"] = $order->getCustomerEmail();
                }
                //Address Information
                if ($this->marketplaceHelper->getSellerProfileDisplayFlag()) {
                    $this->returnArray["addressinfoHeading"] = __('Address Information');
                    $this->returnArray["showAddressInformation"] = true;
                    $this->returnArray["billingAddressHeading"] = __('Billing Address');
                    $this->returnArray["billingAddress"] = $this->orderViewBlock->getFormattedAddress(
                        $order->getBillingAddress()
                    );
                    $this->returnArray["shippingAddressHeading"] = __('Shipping Address');
                    $this->returnArray["shippingAddress"] = $this->orderViewBlock->getFormattedAddress(
                        $order->getShippingAddress()
                    );
                }
                //Payment & Shipping Method
                $this->returnArray["paymentAndShippingHeading"] = __('Payment & Shipping Method');
                $this->returnArray["paymentInfoHeading"] = __('Payment Information');
                $this->returnArray["paymentMethod"] = $paymentMethod;
                //Shipping Information
                if (!$order->getIsVirtual()) {
                    $this->returnArray["shippingInfoHeading"] = __('Shipping and Tracking Information');
                    if ($order->getShippingDescription()) {
                        $this->returnArray["shippingMethod"] = $this->viewTemplate->escapeHtml(
                            $order->getShippingDescription()
                        );
                    } else {
                        $this->returnArray["shippingMethod"] = __("No shipping information available");
                    }
                }
                //Tracking Information
                $this->getTrackingInfo($tracking, $shipment);

                $this->returnArray["itemShippedHeading"] = __('Items Shipped');
                $orderCollection  = $this->marketplaceOrders->getCollection()
                    ->addFieldToFilter("order_id", $orderId)
                    ->addFieldToFilter("seller_id", $this->customerId);
                //Shipped Items Details
                list(
                    $itemList,
                    $subtotal,
                    $totaltax,
                    $couponamount,
                    $admin_subtotal,
                    $shippingamount,
                    $vendor_subtotal,
                    $codcharges_total
                ) = $this->getShippingItemsDetails($order);
                $this->returnArray["itemList"] = $itemList;
                // getting totals data //////////////////////////////////////////////////////
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
                $this->returnArray["subTotal"] = $this->helperCatalog->stripTags(
                    $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $subtotal))
                );
                $this->returnArray["shipping"] = $this->helperCatalog->stripTags(
                    $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $shippingamount))
                );
                $this->returnArray["discount"] = $this->helperCatalog->stripTags(
                    $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $totalCouponAmount))
                );
                $this->returnArray["tax"]      = $this->helperCatalog->stripTags(
                    $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $totaltax))
                );
                $admintotaltax           = 0;
                $vendortotaltax          = 0;
                if (!$taxToSeller) {
                    $admintotaltax = $totaltax;
                } else {
                    $vendortotaltax = $totaltax;
                }
                $this->returnArray["mpcodcharge"] = "";
                if ($paymentCode == "mpcashondelivery") {
                    $this->returnArray["mpcodcharge"] = $order->formatPrice(
                        $this->dashboardHelper->getOrderedPricebyorder($order, $codcharges_total)
                    );
                }
                $this->returnArray["orderTotal"] = $this->helperCatalog->stripTags(
                    $order->formatPrice(
                        $this->dashboardHelper->getOrderedPricebyorder(
                            $order,
                            ($subtotal+$shippingamount+$codcharges_total+$totaltax-$totalCouponAmount)
                        )
                    )
                );
                $this->returnArray["orderBaseTotal"] = "";
                if ($order->isCurrencyDifferent()) {
                    $this->returnArray["orderBaseTotal"] = $order->formatBasePrice(
                        $subtotal+$shippingamount+$codcharges_total+$totaltax-$totalCouponAmount
                    );
                }
                $this->returnArray["vendorTotal"] = $this->helperCatalog->stripTags(
                    $order->formatPrice(
                        $this->dashboardHelper->getOrderedPricebyorder(
                            $order,
                            (
                                $vendor_subtotal+
                                $shippingamount+
                                $codcharges_total+
                                $vendortotaltax-
                                $refundedShippingAmount-
                                $couponamount
                            )
                        )
                    )
                );
                $this->returnArray["vendorBaseTotal"] = "";
                if ($order->isCurrencyDifferent()) {
                    $this->returnArray["vendorBaseTotal"] = $order->formatPrice(
                        $vendor_subtotal+
                        $shippingamount+
                        $codcharges_total+
                        $vendortotaltax-
                        $refundedShippingAmount-
                        $couponamount
                    );
                }
                $this->returnArray["adminCommission"] = $this->helperCatalog->stripTags(
                    $order->formatPrice(
                        $this->dashboardHelper->getOrderedPricebyorder($order, ($admin_subtotal+$admintotaltax))
                    )
                );
                $this->returnArray["adminBaseCommission"] = "";
                if ($order->isCurrencyDifferent()) {
                    $this->returnArray["adminBaseCommission"] = $order->formatBasePrice($admin_subtotal+$admintotaltax);
                }

                $this->returnArray["success"]   = true;
            } else {
                $this->returnArray["success"]   = false;
                $this->returnArray["message"]   = $shipmentDetails['message']??'';
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

    public function getShippingItemsDetails($order)
    {
        $_items           = $order->getItemsCollection();
        $orderId = $order->getId();
        $paymentCode = $order->getPayment()->getMethod();
        $_count           = $_items->count();
        $subtotal         = 0;
        $totaltax         = 0;
        $couponamount     = 0;
        $admin_subtotal   = 0;
        $shippingamount   = 0;
        $vendor_subtotal  = 0;
        $codcharges_total = 0;
        $itemList         = [];
        foreach ($_items as $_item) {
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
                // for bundle product ////////////////////////////////////////////////////////
                $bundleitems  = $this->arrayMerge([$_item], $_item->getChildrenItems());
                $_count       = count($bundleitems);
                $_index       = 0;
                $prevOptionId = "";
                if ($_item->getProductType() != "bundle") {
                    $eachItem = $this->getProductItems(
                        $result,
                        $eachItem,
                        $order,
                        $_item,
                        $orderId,
                        $row_total,
                        $paymentCode,
                        $codcharges_peritem,
                        $seller_item_commission,
                        $seller_item_cost
                    );
                } else {
                    $eachItem = $this->getBundleProductItems(
                        $bundleitems,
                        $eachItem,
                        $order,
                        $_item,
                        $orderId,
                        $paymentCode,
                        $vendor_subtotal,
                        $subtotal,
                        $admin_subtotal,
                        $totaltax,
                        $codcharges_total,
                        $shippingamount,
                        $couponamount
                    );
                }
            }
            if (!empty($eachItem)) {
                $itemList[] = $eachItem;
            }
        }
        return [
            $itemList, 
            $subtotal,
            $totaltax,
            $couponamount,
            $admin_subtotal,
            $shippingamount,
            $vendor_subtotal,
            $codcharges_total
        ];
    }

    public function getTrackingInfo($tracking, $shipment)
    {
        if ($tracking->getTrackingNumber()) {
            $url = $this->shipmentHelper->getTrackingPopupUrlBySalesModel($shipment);
            $hashArr = explode('?hash=', $url);
            if (!empty($hashArr) && isset($hashArr[1])) {
                if (strpos($hashArr[1], '%') !== false) {
                    $hashArr[1] = explode('%', $hashArr[1])[0];
                }
                $this->returnArray["hashUrlValue"] = $hashArr[1];
            }
            $this->returnArray["trackingUrlLabel"] = __('Track this shipment');
            $allCarriers = $this->orderViewBlock->getCarriers();
            foreach ($allCarriers as $key => $carrier) {
                $oneCarrier = [];
                $oneCarrier['value'] = $key;
                $oneCarrier['label'] = $carrier;
                $this->returnArray["trackingCarrier"][] = $oneCarrier;
            }
            $this->returnArray["trackingDetails"] = [];
            $trackingDetails = [];
            if ($shipment->getAllTracks()) {
                $this->getOneTrackingData($shipment);
            }
            
        }
    }

    public function getOneTrackingData($shipment)
    {
        foreach ($shipment->getAllTracks() as $track) {
            $onetrackingData = [];
            $onetrackingData['id'] = $track->getId();
            $onetrackingData['carrier'] = $this->orderViewBlock->escapeHtml(
                $this->orderViewBlock->getCarrierTitle($track->getCarrierCode())
            );
            $onetrackingData['title'] = $this->orderViewBlock->escapeHtml($track->getTitle());
            if ($track->isCustom()) {
                $onetrackingData['trackingNumber'] = $this->orderViewBlock->escapeHtml(
                    $track->getNumber()
                );
            } else {
                $onetrackingData['trackingNumber'] = $this->orderViewBlock->escapeHtml(
                    $track->getNumber()
                );
                $url = $this->shipmentHelper->getTrackingPopupUrlBySalesModel($track);
                $hashArr = explode('?hash=', $url);
                if (!empty($hashArr) && isset($hashArr[1])) {
                    if (strpos($hashArr[1], '%') !== false) {
                        $hashArr[1] = explode('%', $hashArr[1])[0];
                    }
                    $onetrackingData["trackingUrlHash"] = $hashArr[1];
                }
            }
            $trackingDetails[] = $onetrackingData;
        }
        $this->returnArray["trackingDetails"] = $trackingDetails;
    }

    public function getProductItems(
        $result,
        $eachItem,
        $order,
        $_item,
        $orderId,
        $row_total,
        $paymentCode,
        $codcharges_peritem,
        $seller_item_commission,
        $seller_item_cost
    ) {
        $eachItem["productName"] = $this->viewTemplate->escapeHtml($_item->getName());
        $eachItem["customOption"]            = [];
        $eachItem["downloadableOptionLable"] = "";
        $eachItem["downloadableOptionValue"] = [];
        if ($_item->getProductType() == "downloadable") {
            if ($options = $result) {
                $customOption = $this->getOptions($options);
                $eachItem["customOption"] = $customOption;
            }
            // downloadable /////////////////////////////////////////////////////////////
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
        } else {
            if ($options = $result) {
                $customOption = $this->getOptions($options);
                $eachItem["customOption"] = $customOption;
            }
        }
        $eachItem["sku"]   = $_item->getSku();
        $eachItem["price"] = $this->helperCatalog->stripTags(
            $order->formatPrice($_item->getPrice())
        );

        $eachItem["qty"]        = $_item->getQtyShipped()*1;
        $eachItem["totalPrice"] = $this->helperCatalog->stripTags(
            $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $row_total))
        );
        $eachItem["mpcodprice"] = "";
        if ($paymentCode == "mpcashondelivery") {
            $eachItem["mpcodprice"] = $this->helperCatalog->stripTags($order->formatPrice(
                $this->dashboardHelper->getOrderedPricebyorder($order, $codcharges_peritem)
            ));
        }
        $eachItem["adminCommission"] = $this->helperCatalog->stripTags($order->formatPrice(
            $this->dashboardHelper->getOrderedPricebyorder($order, $seller_item_commission)
        ));
        $eachItem["vendorTotal"] = $this->helperCatalog->stripTags($order->formatPrice(
            $this->dashboardHelper->getOrderedPricebyorder($order, $seller_item_cost)
        ));
        $eachItem["subTotal"] = $this->helperCatalog->stripTags(
            $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $row_total))
        );
        return $eachItem;
    }

    public function getBundleProductItems(
        $bundleitems,
        $eachItem,
        $order,
        $_item,
        $orderId,
        $paymentCode,
        $vendor_subtotal,
        $subtotal,
        $admin_subtotal,
        $totaltax,
        $codcharges_total,
        $shippingamount,
        $couponamount
    ) {
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
                $eachItem["totalPrice"]      = $this->helperCatalog->stripTags($order->formatPrice(
                    $this->dashboardHelper->getOrderedPricebyorder($order, $row_total)
                ));
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

    public function getOptions($options)
    {
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
        return $customOption;
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
            $this->shipmentId    = $this->wholeData["shipmentId"]    ?? 0;
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
     * Initialize shipment model instance.
     *
     * @param int   $shipmentId shipment id
     * @param order $order      order
     *
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _initShipment($shipmentId, $order)
    {
        $data = [];
        $data['success'] = false;
        if (!$shipmentId) {
            return $data;
        }
        $shipment = $this->shipmentFactory->create($order)->load($shipmentId);
        if (!$shipment) {
            return $data;
        }

        $tracking = $this->marketplaceOrderhelper->getOrderinfo($order->getId());
        if ($tracking && $tracking->getId()) {
            if ($tracking->getShipmentId() == $shipmentId) {
                if (!$shipmentId) {
                    $data['message'] = __('The shipment no longer exists.');
                    throw new \NoSuchEntityException($data['message']);
                }
            } else {
                $data['message'] = __('You are not authorize to view this shipment.');
                throw new \BadMethodCallException($data['message']);
            }
        } else {
            $data['message'] = __('You are not authorize to view this shipment.');
            throw new \BadMethodCallException($data['message']);
        }
        $this->coreRegistry->register('sales_order', $order);
        $this->coreRegistry->register('current_order', $order);
        $this->coreRegistry->register('current_shipment', $shipment);
        $data['success'] = true;
        $data['shipment'] = $shipment;
        $data['tracking'] = $tracking;
        return $data;
    }

    public function arrayMerge($arr1, $arr2)
    {
        return array_merge($arr1, $arr2);
    }
}
