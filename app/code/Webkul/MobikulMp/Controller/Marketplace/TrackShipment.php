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
 * Class TrackShipment for tracking shipment
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class TrackShipment extends AbstractMarketplace
{
    /**
     * Execute function for class TrackShipment
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customerSession->setCustomerId($this->customerId);
            $order   = $this->order->loadByIncrementId($this->incrementId);
            $orderId = $order->getId();
            $shippingInfoModel = $this->shippingInfoFactory->create()->loadByHash($this->hashValue);
            $this->returnArray['mainHeading'] = __('Tracking Information');
            if (count($shippingInfoModel->getTrackingInfo()) == 0) {
                $this->returnArray['message'] = __("There is no tracking available.");
            }
            $trackingData = [];
            foreach ($shippingInfoModel->getTrackingInfo() as $shipId => $result) {
                $result['subHeading'] = __('Shipment #').$shipId;
                if (!empty($result)) {
                    foreach ($result as $counter => $track) {
                        $oneTrackingData = $this->getTrackingData($counter, $track);
                        if (!empty($oneTrackingData)) {
                            $trackingData[] = $oneTrackingData;
                        }
                    }
                } else {
                    $this->returnArray['message'] = __("There is no tracking available for this shipment.");
                }
            }
            $this->returnArray['trackingData'] = $trackingData;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function getTrackingData($counter, $track)
    {
        $oneTrackingData = [];
        $shipmentBlockIdentifier = $shipId . '.' . $counter;
        $email = $this->shippingPopupBlock->getStoreSupportEmail();
        $fields = [
            'Status' => 'getStatus',
            'Signed by' => 'getSignedby',
            'Delivered to' => 'getDeliveryLocation',
            'Shipped or billed on' => 'getShippedDate',
            'Service Type' => 'getService',
            'Weight' => 'getWeight',
        ];

        // $number = is_object($track) ? $track->getTracking() : $track['number'];
        if (is_object($track)) {
            $number = $track->getTracking();
        } elseif (isset($track['number'])) {
            $number = $track['number'];
        }
        if (is_object($track)) {
            $oneTrackingData['trackingNumberHeading'] = __('Tracking Number:');
            $oneTrackingData['trackingNumber'] = $number;
            if ($track->getCarrierTitle()) {
                $oneTrackingData['carrierHeading'] = __('Carrier:');
                $oneTrackingData['carrier'] = $track->getCarrierTitle();
            }
            if ($track->getErrorMessage()) {
                $oneTrackingData['errorHeading'] = __('Error:');
                $oneTrackingData['errorMessage1'] = __(
                    'Tracking information is currently not available. Please '
                );
                $oneTrackingData['errorMessage2'] = __('contact us');
                $oneTrackingData['errorMessage3'] = __(' for more information or ');
                $oneTrackingData['errorMessage4'] = __('email us at ');
                $oneTrackingData['email'] = $email;
            } elseif ($track->getTrackSummary()) {
                $oneTrackingData['summaryInfoHeading'] = __('Info:');
                $oneTrackingData['summaryInfo'] = $track->getTrackSummary();
            } elseif ($track->getUrl()) {
                $oneTrackingData['trackUrlHeading'] = __('Track:');
                $oneTrackingData['trackUrlHeading'] = $track->getUrl();
            } else {
                $oneTrackingData['fieldData'] = [];
                foreach ($fields as $title => $property) {
                    $oneFieldData = [];
                    if (!empty($track->$property())) {
                        $oneFieldData['heading']  = __($title . ':');
                        $oneFieldData['value'] = $track->$property();
                    }
                    $oneTrackingData['fieldData'][] = $oneFieldData;
                }
                if ($track->getDeliverydate()) {
                    $oneTrackingData['deliveryDateHeading'] = __('Delivered on:');
                    $oneTrackingData[
                        'deliveryDate'
                    ] = $this->shippingPopupBlock->formatDeliveryDateTime(
                        $track->getDeliverydate(),
                        $track->getDeliverytime()
                    );
                }
            }
        } elseif (isset($track['title']) && isset($track['number']) && $track['number']) {
            $oneTrackingData['heading'] = $track['title'] ? $track['title'] : __('N/A');
            $oneTrackingData['value'] = isset($track['number']) ? $track['number'] : '';
        }
        return $oneTrackingData;
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
            $this->shipmentId    = $this->wholeData["shipmentId"]    ?? 0;
            $this->hashValue     = $this->wholeData["hash"]          ?? '';
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
