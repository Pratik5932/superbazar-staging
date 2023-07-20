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

class UploadShippingRate extends \Webkul\MobikulMpHyperLocal\Controller\AbstractApi  {

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
                    $storeId       = $wholeData["storeId"]       ?? "";
                    $customerToken = $wholeData["customerToken"] ?? "";
                    $customerId    = $this->_helper->getCustomerByToken($customerToken) ?? 0;
                    $environment   = $this->_emulate->startEnvironmentEmulation($storeId);
                    $this->_customerSession->setCustomerId($customerId);
                    if ($this->_mpHelper->isSeller()) {
                        $uploader = $this->_fileUploaderFactory->create(
                            ["fileId" => "csv"]
                        );
                        $result = $uploader->validateFile();
                        $file = $result["tmp_name"];
                        $fileNameArray = explode(".", $result["name"]);
                        $ext = end($fileNameArray);
                        if ($file != "" && $ext == "csv") {
                            $fileHandle = fopen($file, "r");
                            $count = 0;
                            while (!feof($fileHandle)) {
                                $row = fgetcsv($fileHandle, 1024);
                                if ($this->isValidRow($row)) {
                                    $temp = [
                                        "distance_from" => $row[0],
                                        "distance_to" => $row[1],
                                        "weight_from" => $row[2],
                                        "weight_to" => $row[3],
                                        "cost" => $row[4],
                                        "seller_id" => $customerId,
                                    ];
                                    $this->saveShipRate($temp);
                                    $count++;
                                }
                            }
                            $returnArray["success"] = true;
                            $returnArray["message"] = __("Your shipping rate has been successfully updated");
                        } else {
                            $returnArray["message"] = __("Please upload Csv file");
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
     * isValidRow
     * @param array
     * @return bool
     */
    private function isValidRow($row)
    {
        for ($i = 0; $i < 4; $i++) {
            if (!is_numeric($row[$i])) {
                return false;
            }
        }
        return true;
    }


    /**
     * saveShipRate
     * @param array $temp
     * @return void
     */
    private function saveShipRate($temp)
    {
        $shipRateRecord = $this->_shipRate
            ->create()
            ->getCollection()
            ->addFieldToFilter("seller_id", $temp["seller_id"])
            ->addFieldToFilter("distance_from", $temp["distance_from"])
            ->addFieldToFilter("distance_to", $temp["distance_to"])
            ->addFieldToFilter("weight_from", $temp["weight_from"])
            ->addFieldToFilter("weight_to", $temp["weight_to"])
            ->setPageSize(1)->getFirstItem();
        if ($shipRateRecord->getEntityId()) {
            $shipRateRecord->setCost($temp["cost"]);
            $shipRateRecord->setId($shipRateRecord->getId());
            $shipRateRecord->save();
        } else {
            $shippingModel = $this->_shipRate->create();
            $shippingModel->setData($temp);
            $shippingModel->save();
        }
    }
}
