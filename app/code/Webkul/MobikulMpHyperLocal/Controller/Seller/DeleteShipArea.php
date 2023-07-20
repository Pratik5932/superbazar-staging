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

class DeleteShipArea extends \Webkul\MobikulMpHyperLocal\Controller\AbstractApi  {

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
                    $ids           = $wholeData["ids"]           ?? "";
                    $storeId       = $wholeData["storeId"]       ?? 1;
                    $customerToken = $wholeData["customerToken"] ?? "";
                    $customerId    = $this->_helper->getCustomerByToken($customerToken) ?? 0;
                    $environment   = $this->_emulate->startEnvironmentEmulation($storeId);
                    $this->_customerSession->setCustomerId($customerId);
                    if ($this->_mpHelper->isSeller()) {
                        $ids = explode(',', $ids);
                        $ids = array_filter($ids);
                        if (count($ids)) {
                            $shipAreaList = $this->_shipArea
                                ->create()
                                ->getCollection()
                                ->addFieldToFilter('seller_id', $customerId)
                                ->addFieldToFilter('entity_id', ['in' => $ids]);
                            $recordelete = 0;
                            foreach ($shipAreaList as $shipArea) {
                                $this->deleteObj($shipArea);
                                $recordelete++;
                            }
                            $returnArray["success"] = true;
                            $returnArray["message"] = __('Ship area deleted successfully.');
                        } else {
                            $returnArray["message"] = __('Invalid request.');
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
    /**
     * deleteObj
     * @param Object
     * @return void
     */
    private function deleteObj($object)
    {
        $object->delete();
    }
}
