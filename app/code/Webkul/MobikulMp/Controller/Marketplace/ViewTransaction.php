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
 * Class ViewTransaction controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class ViewTransaction extends AbstractMarketplace
{
    /**
     * Execute function for class ViewTransaction
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "VIEWTRANSACTION".$this->storeId.$this->transactionId.$this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $transaction = $this->sellerTransaction->load($this->transactionId);
            $this->returnArray["transactionId"] = $transaction->getTransactionId();
            $this->returnArray["date"]          = $this->viewTemplate->formatDate(
                $transaction->getCreatedAt(),
                \IntlDateFormatter::LONG
            );
            $this->returnArray["type"]          = $transaction->getType();
            $this->returnArray["method"]        = $transaction->getMethod();
            $this->returnArray["amount"]        = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice($transaction->getTransactionAmount())
            );
            if ($transaction->getCustomNote()) {
                $this->returnArray["comment"]   = $transaction->getCustomNote();
            }
            // getting Order list /////////////////////////////////////////////////////////////////
            $orderCollection = $this->orderCollectionFactory->create()
                ->addFieldToFilter("seller_id", $this->customerId)
                ->addFieldToFilter("trans_id", $this->transactionId)
                ->addFieldToFilter("order_id", ["neq"=>0]);
            $orderList = [];
            foreach ($orderCollection as $order) {
                $sellerId      = $order->getSellerId();
                $mageorderid   = $order->getOrderId();
                $totalShipping = 0;
                if ($order->getIsShipping()) {
                    $totalShipping = $this->sellerOrderShippingAmount($sellerId, $mageorderid);
                }
                $eachOrder                = [];
                $eachOrder["qty"]         = $order->getMagequantity();
                $eachOrder["price"]       = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($order->getMageproPrice())
                );
                $eachOrder["totalTax"]    = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($order->getTotalTax())
                );
                $eachOrder["shipping"]    = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($totalShipping)
                );
                $eachOrder["totalPrice"]  = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($order->getTotalAmount())
                );
                $eachOrder["commission"]  = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($order->getTotalCommission())
                );
                $eachOrder["incrementId"] = $order->getMagerealorderId();
                $eachOrder["productName"] = $order["magepro_name"];
                $admintotaltax         = 0;
                $vendortotaltax        = 0;
                if (!$this->marketplaceHelper->getConfigTaxManage()) {
                    $admintotaltax     = $order->getTotalTax();
                } else {
                    $vendortotaltax    = $order->getTotalTax();
                }
                $eachOrder["subTotal"] = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($order->getActualSellerAmount()+$vendortotaltax+$totalShipping)
                );
                $orderList[] = $eachOrder;
            }
            $this->returnArray["orderList"] = $orderList;
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
            $this->transactionId = $this->wholeData["transactionId"] ?? 0;
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
     * Function to get order Shipping amount
     *
     * @param int $sellerId seller id
     * @param int $orderId  order id
     *
     * @return float shipping amount
     */
    public function sellerOrderShippingAmount($sellerId, $orderId)
    {
        $coll = $this->marketplaceOrderResourceCollection
            ->addFieldToFilter("seller_id", $sellerId)
            ->addFieldToFilter("order_id", $orderId);
        $shippingAmount = 0;
        foreach ($coll as $key => $value) {
            $shippingAmount = $value->getShippingCharges();
        }
        return $shippingAmount;
    }
}
