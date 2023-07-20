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
 * Class ContactSeller for Contacting seller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class ContactSeller extends AbstractMarketplace
{
    /**
     * Execute function for class ContactSeller
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->veriFyRequest();
            $environment        = $this->emulate->startEnvironmentEmulation(
                $this->storeId
            );
            $data = [];
            $data["ask"] = $this->query;
            $data["name"] = $this->name;
            $data["email"] = $this->email;
            $data["subject"] = $this->subject;
            $data["seller-id"] = $this->sellerId;
            $data["product-id"] = $this->productId;
            $this->_eventManager->dispatch("mp_send_querymail", [$data]);
            if ($this->customerId != 0) {
                $customer = $this->customer->load($this->customerId);
                $buyerName = $customer->getName();
                $buyerEmail = $customer->getEmail();
            } else {
                $buyerEmail = $this->email;
                $buyerName = $this->name;
                if (strlen($buyerName) < 2) {
                    $buyerName = "Guest";
                }
            }
            $senderInfo = [];
            $templateVars = [];
            $receiverInfo = [];
            $seller = $this->customer->load($this->sellerId);
            $templateVars["myvar1"] = $seller->getName();
            $sellerEmail = $seller->getEmail();
            if ($this->productId != 0) {
                $templateVars["myvar3"] = $this->productModel->load($this->productId)->getName();
            }
            $templateVars["myvar4"] = $this->query;
            $templateVars["myvar6"] = $this->subject;
            $templateVars["myvar5"] = $buyerEmail;
            $senderInfo = [
                "name" => $buyerName,
                "email" => $buyerEmail
            ];
            $receiverInfo = [
                "name" => $seller->getName(),
                "email" => $sellerEmail
            ];
            $this->marketplaceEmailHelper->sendQuerypartnerEmail($data, $templateVars, $senderInfo, $receiverInfo);
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Your mail has been sent.");
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
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->name = $this->wholeData["name"] ?? "";
            $this->email = $this->wholeData["email"] ?? "";
            $this->query = $this->wholeData["query"] ?? "";
            $this->subject = $this->wholeData["subject"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->sellerId = $this->wholeData["sellerId"] ?? 0;
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
