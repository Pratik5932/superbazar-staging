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

class SaveShipArea extends \Webkul\MobikulMpHyperLocal\Controller\AbstractApi  {

    public function execute()  {
        $returnArray            = [];
        $returnArray["id"]      = 0;
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
                    $addressType   = $wholeData["addressType"]   ?? "city";
                    $customerToken = $wholeData["customerToken"] ?? "";
                    $customerId    = $this->_helper->getCustomerByToken($customerToken) ?? 0;
                    $environment   = $this->_emulate->startEnvironmentEmulation($storeId);
                    $this->_customerSession->setCustomerId($customerId);
                    if ($this->_mpHelper->isSeller()) {
                        $shipArea = $this->_shipArea
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter("latitude", $latitude)
                            ->addFieldToFilter("longitude", $longitude)
                            ->addFieldToFilter("seller_id", $customerId)
                            ->setPageSize(1)
                            ->getFirstItem();
                        if (!$shipArea->getEntityId()) {
                            $shipArea = $this->_shipArea->create();
                            $data["address"]      = $location;
                            $data["latitude"]     = $latitude;
                            $data["longitude"]    = $longitude;
                            $data["seller_id"]    = $customerId;
                            $data["address_type"] = $addressType;
                            $shipArea->setData($data);
                            $shipArea->save();
                            $returnArray["id"]      = $shipArea->getEntityId();
                            $returnArray["success"] = true;
                            $returnArray["message"] = __("Ship area saved successfully.");
                        } else {
                            $returnArray["message"] = __("Ship area already exist.");
                        }
                    } else {
                        $returnArray["message"] = __("Invalid seller");
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
