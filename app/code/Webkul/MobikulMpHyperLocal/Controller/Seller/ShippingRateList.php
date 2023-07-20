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

class ShippingRateList extends \Webkul\MobikulMpHyperLocal\Controller\AbstractApi  {

    public function execute()  {
        $returnArray                = [];
        $returnArray['success']     = false;
        $returnArray['message']     = "";
        $returnArray['rateList']    = [];
        $returnArray['totalCount']  = 0;
        $returnArray['csvFileLink'] = "";
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
                    $pageNumber    = $wholeData["pageNumber"]    ?? 1;
                    $customerToken = $wholeData["customerToken"] ?? "";
                    $customerId    = $this->_helper->getCustomerByToken($customerToken) ?? 0;
                    $environment   = $this->_emulate->startEnvironmentEmulation($storeId);
                    $this->_customerSession->setCustomerId($customerId);
                    if ($this->_mpHelper->isSeller()) {
                        $returnArray['csvFileLink'] = $this->_addRateBlock->getViewFileUrl('Webkul_MpHyperLocal::hyper-local-shiprate.csv');
                        $rateCollection = $this->_shipRate
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter('seller_id', $customerId);
                        if ($pageNumber >= 1) {
                            $returnArray["totalCount"] = $rateCollection->getSize();
                            $pageSize = $this->_helper->getConfigData("mobikul/configuration/pagesize");
                            $rateCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        $ratelist = [];
                        foreach ($rateCollection as $rate) {
                            $onertae = [];
                            $oneRate["id"]           = $rate->getEntityId();
                            $oneRate["cost"]         = strip_tags($this->_addRateBlock->getFormatedPrice($rate->getCost()));
                            $oneRate["weightTo"]     = $rate->getWeightTo();
                            $oneRate["weightFrom"]   = $rate->getWeightFrom();
                            $oneRate["distanceTo"]   = $rate->getDistanceTo();
                            $oneRate["distanceFrom"] = $rate->getDistanceFrom();
                            $ratelist[] = $oneRate;
                        }
                        $returnArray['success']  = true;
                        $returnArray["rateList"] = $ratelist;
                    } else {
                        $returnArray['message'] = __('Invalid seller');
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
