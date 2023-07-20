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

namespace Webkul\MobikulMp\Observer;

class OrderStatusNotification extends AbstractObserver
{
    /**
     * Execute function for Observer OrderStatusNotification
     *
     * @param \Magento\Framework\Event\Observer $observer contatins all the dispatched data
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        if ($order->getState() != "") {
            $canReorder = 0;
            if ($this->helper->canReorder($order) == 1) {
                $canReorder = $this->helper->canReorder($order);
            }
            $message = [
                "id"               => $order->getId()+1,
                "body"             => __("Order status is ").$order->getStatusLabel(),
                "title"            => __("An order has been placed!"),
                "sound"            => "default",
                "message"          => __("Order status is ").$order->getStatusLabel(),
                "canReorder"       => $canReorder,
                "incrementId"      => $order->getIncrementId(),
                "notificationType" => "sellerOrder"
            ];
            $url     = "https://fcm.googleapis.com/fcm/send";
            $authKey = $this->helper->getConfigData("mobikul/notification/apikey");
            $headers = [
                "Authorization" => "key=".$authKey,
                "Content-Type" => "application/json"
            ];
            // getting product ids from order /////////////////////////////////////////////////////////////
            $productIds = [];
            foreach ($order->getItemsCollection() as $item) {
                $productIds[] = $item->getProductId();
            }
            $mpProductCollection = $this->marketplaceProduct->getCollection()->addFieldToFilter(
                "mageproduct_id",
                [
                    "in"=>$productIds
                ]
            );
            $websiteId = $this->storeManager->getStore(true)->getWebsite()->getId();
            $joinTable = $this->sellerCollection->getTable("customer_grid_flat");
            if ($this->helper->getConfigData("customer/account_share/scope")) {
                $mpProductCollection->getSelect()->join(
                    $joinTable." as cgf",
                    "main_table.seller_id=cgf.entity_id AND website_id=".$websiteId
                );
            } else {
                $mpProductCollection->getSelect()->join($joinTable." as cgf", "main_table.seller_id=cgf.entity_id");
            }
            $mpProductCollection->getSelect()->group('cgf.entity_id');
            $this->manageToken($mpProductCollection, $message, $url, $headers, $authKey);
        }
    }

    public function manageToken($mpProductCollection, $message, $url, $headers, $authKey)
    {
        foreach ($mpProductCollection as $eachProduct) {
            $sellerId = $eachProduct["seller_id"];
            if ($authKey != "") {
                $tokenCollection = $this->deviceToken->getCollection()->addFieldToFilter("customer_id", $sellerId);
                foreach ($tokenCollection as $eachToken) {
                    $fields = [
                        "to"                => $eachToken->getToken(),
                        "data"              => $message,
                        "priority"          => "high",
                        "notification"      => $message,
                        "time_to_live"      => 30,
                        "delay_while_idle"  => true,
                        "content_available" => true
                    ];
                    $this->curl->setHeaders($headers);
                    $this->curl->post($url, $this->jsonHelper->jsonEncode($fields));
                    $result = $this->curl->getBody();
                    if ($this->isJson($result)) {
                        $result = $this->jsonHelper->jsonDecode($result);
                        if ($result["success"] == 0 && $result["failure"] == 1) {
                            $eachToken->delete();
                        }
                    }
                }
            }
        }
    }
}
