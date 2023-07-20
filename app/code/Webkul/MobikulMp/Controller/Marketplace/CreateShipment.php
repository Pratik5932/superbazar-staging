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
 * Class CreateShipment controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class CreateShipment extends AbstractMarketplace
{
    /**
     * Execute function for class CreateShipment
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
            $order    = $this->order->loadByIncrementId($this->incrementId);
            $orderId  = $order->getId();
            $sellerId = $this->customerId;
            if (empty($orderId)) {
                $this->returnArray["message"] = __('Can not create shipment as order did not found.');
            }
            $marketplaceOrder = $this->marketplaceOrders->getCollection()
                ->addFieldToFilter("order_id", $orderId)
                ->addFieldToFilter("seller_id", $this->customerId);
            $trackingid = '';
            $carrier = '';
            $trackingData = [];
            $data = [];
            if (!empty($trackingId)) {
                $trackingid = $trackingId;
                $trackingData[1]['number'] = $trackingid;
                $trackingData[1]['carrier_code'] = 'custom';
            }
            if (!empty($carrier)) {
                $carrier = $carrier;
                $trackingData[1]['title'] = $carrier;
            }
            if (!empty($apiShipment)) {
                $this->_eventManager->dispatch(
                    'generate_api_shipment',
                    [
                        'api_shipment' => $apiShipment,
                        'order_id' => $orderId,
                    ]
                );
                $shipmentData = $this->customerSession->getData('shipment_data');
                $apiName = $shipmentData['api_name'];
                $trackingid = $shipmentData['tracking_number'];
                $trackingData[1]['number'] = $trackingid;
                $trackingData[1]['carrier_code'] = 'custom';
                $this->customerSession->unsetData('shipment_data');
            }
            if (empty($apiShipment) || $trackingid != '') {
                if ($order->canUnhold()) {
                    $this->returnArray["message"] = __('Can not create shipment as order is in HOLD state');
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Can not create shipment as order is in HOLD state')
                    );
                } else {
                    $items = [];
                    $shippingAmount = 0;
                    $trackingsdata = $this->marketplaceOrders
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $orderId
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        );
                    foreach ($trackingsdata as $tracking) {
                        $shippingAmount = $tracking->getShippingCharges();
                    }
                    $collection = $this->marketplaceSaleList
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $orderId
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        );
                    foreach ($collection as $saleproduct) {
                        array_push($items, $saleproduct['order_item_id']);
                    }
                    $itemsarray = $this->_getShippingItemQtys($order, $items);
                    if (count($itemsarray) > 0) {
                        $shipment = false;
                        $shipmentId = 0;
                        $shipment = $this->checkShipment($order, $shipmentId, $orderId, $itemsarray, $trackingData);
                        if ($shipment) {
                            $isNeedCreateLabel = $this->createShipment(
                                $orderId,
                                $sellerId,
                                $shipment,
                                $data,
                                $trackingid,
                                $carrier
                            );
                            $this->shipmentSender->send($shipment);
                            $shipmentCreatedMessage = __('The shipment has been created.');
                            $labelMessage = __('The shipping label has been created.');
                            $message = $isNeedCreateLabel ? $shipmentCreatedMessage.' '.$labelMessage
                                : $shipmentCreatedMessage;
                            $this->returnArray["shipmentId"] = $shipment->getId();
                            $this->returnArray["message"] = $message;
                            $status = 1;
                            $this->returnArray["success"]   = true;
                        }
                    }
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

    public function checkShipment($order, $shipmentId, $orderId, $itemsarray, $trackingData)
    {
        if ($shipmentId) {
            $shipment = $this->objectManager->create(
                \Magento\Sales\Model\Order\Shipment::class
            )->load($shipmentId);
            return $shipment;
        } elseif ($orderId) {
            if ($order->getForcedDoShipmentWithInvoice()) {
                $this->returnArray["message"] = __(
                    'Cannot do shipment for the order separately from invoice.'
                );
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot do shipment for the order separately from invoice.')
                );
            }
            if (!$order->canShip()) {
                $this->returnArray["message"] = __('Cannot do shipment for the order.');
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Cannot do shipment for the order.')
                );
            }

            $shipment = $this->_prepareShipment(
                $order,
                $itemsarray['data'],
                $trackingData
            );
            return $shipment;
        }
    }

    public function createShipment($orderId, $sellerId, $shipment, $data, $trackingid, $carrier)
    {
        $comment = '';
        $shipment->getOrder()->setCustomerNoteNotify(
            !empty($data['send_email'])
        );
        $shippingLabel = '';
        if (!empty($data['create_shipping_label'])) {
            $shippingLabel = $data['create_shipping_label'];
        }
        $isNeedCreateLabel=!empty($shippingLabel) && $shippingLabel;
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = $this->transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        );
        $transactionSave->save();
        $shipmentId = $shipment->getId();
        $courrier = 'custom';
        $sellerCollection = $this->marketplaceOrders
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['eq' => $orderId]
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            );
        foreach ($sellerCollection as $row) {
            if ($shipment->getId() != '') {
                $row->setShipmentId($shipment->getId());
                $row->setTrackingNumber($trackingid);
                $row->setCarrierName($carrier);
                $row->save();
            }
        }

        return $isNeedCreateLabel;
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
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->incrementId   = $this->wholeData["incrementId"]   ?? 0;
            $this->carrier       = $this->wholeData["carrier"]       ?? '';
            $this->trackingId    = $this->wholeData["trackingId"]    ?? '';
            $this->apiShipment   = $this->wholeData["apiShipment"]   ?? '';
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
     * Prepare the shipment
     *
     * @param \Magento\Sales\Model\Order $order        order
     * @param array                      $items        items
     * @param array                      $trackingData trackingData
     *
     * @return void
     */
    public function _prepareShipment($order, $items, $trackingData)
    {
        $shipment = $this->shipmentFactory->create(
            $order,
            $items,
            $trackingData
        );
        if (!$shipment->getTotalQty()) {
            $this->returnArray["message"] = __("Cannot do shipment for the order.");
            return false;
        }
        return $shipment->register();
    }

    /**
     * Prepare the shipment
     *
     * @param \Magento\Sales\Model\Order $order order
     * @param array                      $items items
     *
     * @return void
     */
    public function _getShippingItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)) {
                $data[$item->getItemId()] = (int) $item->getQtyOrdered() - $item->getQtyShipped();
                $_item = $item;
                // for bundle product
                $bundleitems = $this->arrayMerge([$_item], $_item->getChildrenItems());
                if ($_item->getParentItem()) {
                    continue;
                }
                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] = (int) $_bundleitem->getQtyOrdered() -
                                $item->getQtyShipped();
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
