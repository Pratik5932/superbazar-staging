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
 * Class cancelOrder conreoller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class CancelOrder extends AbstractMarketplace
{
    /**
     * Execute function for class cancelOrder
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
            $orderDetails = $this->_initOrder($order);
            $isPartner = $this->marketplaceHelper->isSeller();
            if ($isPartner && $orderDetails['success']) {
                $flag = $this->marketplaceOrderhelper->cancelorder($order, $this->customerId);
                if ($flag) {
                    $paidCanceledStatus = \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_CANCELED;
                    $paymentCode = '';
                    $paymentMethod = '';
                    if ($order->getPayment()) {
                        $paymentCode = $order->getPayment()->getMethod();
                    }
                    $collection = $this->marketplaceSaleList
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            ['eq' => $orderId]
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $this->customerId]
                        );
                    foreach ($collection as $saleproduct) {
                        $saleproduct->setCpprostatus(
                            $paidCanceledStatus
                        );
                        $saleproduct->setPaidStatus(
                            $paidCanceledStatus
                        );
                        if ($paymentCode == 'mpcashondelivery') {
                            $saleproduct->setCollectCodStatus(
                                $paidCanceledStatus
                            );
                            $saleproduct->setAdminPayStatus(
                                $paidCanceledStatus
                            );
                        }
                        $saleproduct->save();
                    }
                    $trackingcoll = $this->marketplaceOrders
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $orderId
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            $this->customerId
                        );
                    foreach ($trackingcoll as $tracking) {
                        $tracking->setTrackingNumber('canceled');
                        $tracking->setCarrierName('canceled');
                        $tracking->setIsCanceled(1);
                        $tracking->save();
                    }
                    $this->returnArray["success"] = true;
                    $this->returnArray["message"] = __('The order has been cancelled.');
                    $this->emulate->stopEnvironmentEmulation($environment);
                    $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
                    return $this->getJsonResponse($this->returnArray);
                }
            } else {
                $this->returnArray["message"]      = __("Invalid Request");
                $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
                return $this->getJsonResponse($this->returnArray);
            }
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
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
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
}
