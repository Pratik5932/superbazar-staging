<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MobikulMpHyperLocal\Controller\Seller;

class SaveSellerShippingOrigin extends \Webkul\MobikulMpHyperLocal\Controller\AbstractApi  {

    public function execute()  {
        $returnArray            = [];
        $returnArray["success"] = false;
        $returnArray["message"] = "";
        try  {
            $wholeData      = $this->getRequest()->getPostValue();
            $this->_headers = $this->getRequest()->getHeaders();
            $this->_helper->log(__CLASS__, "logClass", $wholeData);
            $this->_helper->log($wholeData, "logParams", $wholeData);
            $this->_helper->log($this->_headers, "logHeaders", $wholeData);
            if ($wholeData)  {
                $authKey  = $this->getRequest()->getHeader("authKey");
                $authData = $this->_helper->isAuthorized($authKey);
                if ($authData["code"] == 1)  {
                    $storeId       = $wholeData["storeId"]       ?? 1;
                    $location      = $wholeData["location"]      ?? "";
                    $latitude      = $wholeData["latitude"]      ?? 0.0;
                    $longitude     = $wholeData["longitude"]     ?? 0.0;
                    $radius        = $wholeData["radius"]        ?? 0;
                    $customerToken = $wholeData["customerToken"] ?? "";
                    $customerId    = $this->_helper->getCustomerByToken($customerToken) ?? 0;
                    $environment   = $this->_emulate->startEnvironmentEmulation($storeId);
                    $this->_customerSession->setCustomerId($customerId);
                    if ($this->_mpHelper->isSeller() && $latitude && $longitude) {
                        $sellerData = $this->sellerFactory
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter("seller_id", $customerId)
                            ->setPageSize(1)
                            ->getFirstItem();
                        if ($sellerData->getEntityId()) {
                            $sellerData->setOriginAddress($location);
                            $sellerData->setLatitude($latitude);
                            $sellerData->setLongitude($longitude);
                            $sellerData->setRadius($radius);
                            $sellerData->save();
                            $returnArray["success"] = true;
                            $returnArray["message"] = __("Your shipping origin has been successfully saved.");
                        } else {
                            $returnArray["message"] = __("Invalid seller");
                        }
                    }
                    $this->_emulate->stopEnvironmentEmulation($environment);
                    $this->_helper->log($returnArray, "logResponse", $wholeData);
                    return $this->getJsonResponse($returnArray);
                } else  {
                    return $this->getJsonResponse($returnArray, 401, $authData["token"]);
                }
            } else  {
                $returnArray["message"] = __("Invalid Request");
                $this->_helper->log($returnArray, "logResponse", $wholeData);
                return $this->getJsonResponse($returnArray);
            }
        } catch (\Exception $e)  {
            $returnArray["message"] = $e->getMessage();
            $this->_helper->printLog($returnArray, 1);
            return $this->getJsonResponse($returnArray);
        }
    }
}
