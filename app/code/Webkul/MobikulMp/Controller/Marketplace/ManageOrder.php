<?php
    /**
     * Webkul Software.
     *
     * @category  Webkul
     * @package   Webkul_MobikulMp
     * @author    Webkul
     * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
     * @license   https://store.webkul.com/license.html
     */

     namespace Webkul\MobikulMp\Controller\Marketplace;

class ManageOrder extends AbstractMarketplace
{

    public function execute()
    {
        $returnArray                           = [];
        $returnArray["tax"]                    = "";
        $returnArray["date"]                   = "";
        $returnArray["success"]                = false;
        $returnArray["authKey"]                = "";
        $returnArray["message"]                = "";
        $returnArray["canShip"]                = true;
        $returnArray["itemList"]               = [];
        $returnArray["subTotal"]               = "";
        $returnArray["shipping"]               = "";
        $returnArray["discount"]               = "";
        $returnArray["buyerName"]              = "";
        $returnArray["orderTotal"]             = "";
        $returnArray["buyerEmail"]             = "";
        $returnArray["orderStatus"]            = "";
        $returnArray["incrementId"]            = "";
        $returnArray["mpcodcharge"]            = "";
        $returnArray["vendorTotal"]            = "";
        $returnArray["paymentMethod"]          = "";
        $returnArray["shippingMethod"]         = "";
        $returnArray["billingAddress"]         = "";
        $returnArray["mpCODAvailable"]         = false;
        $returnArray["orderBaseTotal"]         = "";
        $returnArray["vendorBaseTotal"]        = "";
        $returnArray["adminCommission"]        = "";
        $returnArray["shippingAddress"]        = "";
        $returnArray["adminBaseCommission"]    = "";
        $returnArray["showBuyerInformation"]   = true;
        $returnArray["showAddressInformation"] = true;
        try {
            $wholeData       = $this->getRequest()->getPostValue();
            $this->_headers  = $this->getRequest()->getHeaders();
            $this->_helper->log(__CLASS__, "logClass", $wholeData);
            $this->_helper->log($wholeData, "logParams", $wholeData);
            $this->_helper->log($this->_headers, "logHeaders", $wholeData);
            if ($wholeData) {
                $authKey     = $this->getRequest()->getHeader("authKey");
                $apiKey      = $this->getRequest()->getHeader("apiKey");
                $apiPassword = $this->getRequest()->getHeader("apiPassword");
                $authData    = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                    $returnArray["authKey"]      = $authData["authKey"];
                    $returnArray["responseCode"] = $authData["responseCode"];
                    $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                    $orderId     = $this->_helper->validate($wholeData, "orderId")    ? $wholeData["orderId"]    : 0;
                    $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                    $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                    $this->_customerSession->setCustomerId($customerId);

                    $returnArray["success"]   = true;
                    $this->_emulate->stopEnvironmentEmulation($environment);
                    $this->_helper->log($returnArray, "logResponse", $wholeData);
                    return $this->getJsonResponse($returnArray);
                } else {
                    $returnArray["message"]      = $authData["message"];
                    $returnArray["responseCode"] = $authData["responseCode"];
                    $this->_helper->log($returnArray, "logResponse", $wholeData);
                    return $this->getJsonResponse($returnArray);
                }
            } else {
                $returnArray["message"]      = __("Invalid Request");
                $returnArray["responseCode"] = 0;
                $this->_helper->log($returnArray, "logResponse", $wholeData);
                return $this->getJsonResponse($returnArray);
            }
        } catch (\Exception $e) {
            $returnArray["message"] = __($e->getMessage());
            $this->_helper->printLog($returnArray, 1);
            return $this->getJsonResponse($returnArray);
        }
    }
}
