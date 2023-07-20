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
 * Class AddTrackingInfo contrpller
 */
class AddTrackingInfo extends AbstractMarketplace
{
    /**
     * Execute function for class AddTrackingInfo
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
            $order   = $this->order->loadByIncrementId($this->incrementId);
            $orderId = $order->getId();
            if (!$this->marketplaceHelper->isSeller()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
            if (empty($this->carrier)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please specify a carrier.')
                );
            }
            if (empty($this->number)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please enter a tracking number.')
                );
            }
            $shipmentDetails = $this->_initShipment($this->shipmentId, $order);
            if ($shipmentDetails['success']) {
                $tracking = $shipmentDetails['tracking'];
                $shipment = $shipmentDetails['shipment'];
                $track = $this->track
                    ->setNumber(
                        $this->number
                    )->setCarrierCode(
                        $this->carrier
                    )->setTitle(
                        $this->title
                    );
                $shipment->addTrack($track)->save();
                $trackId  = $track->getId();
                if ($track->isCustom()) {
                    $numberclass = 'display';
                    $numberclasshref = 'no-display';
                    $trackingPopupUrl = '';
                } else {
                    $numberclass = 'no-display';
                    $numberclasshref = 'display';
                    $trackingPopupUrl = $this->shipmentHelper->getTrackingPopupUrlBySalesModel($track);
                }
                $this->returnArray['title']            = $this->title;
                $this->returnArray['number']           = $this->number;
                $this->returnArray['trackId']          = $trackId;
                $this->returnArray['carrier']          = $this->orderViewBlock->getCarrierTitle($this->carrier);
                $this->returnArray["success"]          = true;
                $this->returnArray['numberclass']      = $numberclass;
                $this->returnArray['numberclasshref']  = $numberclasshref;
                $this->returnArray['trackingUrlHash']  = $trackingPopupUrl;

                $hashArr = explode('?hash=', $trackingPopupUrl);
                if (!empty($hashArr) && isset($hashArr[1])) {
                    if (strpos($hashArr[1], '%') !== false) {
                        $hashArr[1] = explode('%', $hashArr[1])[0];
                    }
                    $this->returnArray['trackingUrlHash']  = $hashArr[1];
                }
                $this->returnArray['message'] = __('Tracking information has been successfully saved.');
            } else {
                $this->returnArray['message'] = __(
                    'We can\'t initialize shipment for adding tracking number.'
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
            $this->storeId       = $this->wholeData["storeId"]         ?? 0;
            $this->incrementId   = $this->wholeData["incrementId"]     ?? 0;
            $this->shipmentId    = $this->wholeData["shipmentId"]      ?? 0;
            $this->carrier       = $this->wholeData["carrier"]         ?? '';
            $this->number        = $this->wholeData["number"]          ?? '';
            $this->title         = $this->wholeData["title"]           ?? '';
            $this->customerToken = $this->wholeData["customerToken"]   ?? '';
            $this->customerId    = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
