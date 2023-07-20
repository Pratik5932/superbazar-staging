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

class SellerApprovalNotification extends AbstractObserver
{

    /**
     * Execute function for Observer SellerApprovalNotification
     *
     * @param \Magento\Framework\Event\Observer $observer contatins all the dispatched data
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $seller  = $observer->getSeller();
        $message = [
            "id"               => $seller->getId(),
            "body"             => __("Congratulations, You are approved as seller."),
            "sound"            => "default",
            "title"            => __("You are approved as Seller."),
            "message"          => __("Congratulations, You are approved as seller."),
            "sellerId"         => $seller->getId(),
            "notificationType" => "sellerApproval"
        ];
        $url     = "https://fcm.googleapis.com/fcm/send";
        $authKey = $this->helper->getConfigData("mobikul/notification/apikey");
        $headers = [
            "Authorization" => "key=".$authKey,
            "Content-Type" => "application/json"
        ];
        if ($authKey != "") {
            $tokenCollection = $this->deviceToken->getCollection()->addFieldToFilter("customer_id", $seller->getId());
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
