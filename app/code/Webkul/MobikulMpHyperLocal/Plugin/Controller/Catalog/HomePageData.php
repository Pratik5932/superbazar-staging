<?php
namespace Webkul\MobikulMpHyperLocal\Plugin\Controller\Catalog;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Webkul\MpHyperLocal\Helper\Data as HyperlocalHelper;

class HomePageData
{
    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        HyperlocalHelper $hyperlocalHelper
    ){
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->hyperlocalHelper = $hyperlocalHelper;
    }
    
    public function afterExecute(\Webkul\MobikulApi\Controller\Catalog\HomePageData $subject, $result)
    {
        $params = $this->request->getParams();
        $response = json_decode($result->getRawData());
        $collectionFilter = $this->hyperlocalHelper->getCollectionFilter();
        if ($collectionFilter == "zipcode") {
            $savedAddress = [
                "address" => isset($params["address"]) ?? "",
            ];    
        } else {
            $savedAddress = [
                "latitude" => isset($params["latitude"]) ?? "",
                "longitude" => isset($params["longitude"]) ?? "",
                "address" => isset($params["address"]) ?? "",
                "city" => isset($params["city"]) ?? "",
                "state" => isset($params["state"]) ?? "",
                "country" => isset($params["country"]) ?? "",
            ];
        }
        if (!$this->validateAddress($savedAddress)) {
            // if ($this->hyperlocalHelper->getAddressOption() == 'redirect') {
            //     $response->isRedirect = true;
            //     $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            //     $resultJson->setData($response);
            //     return $resultJson;
            // } else {
                $response->isRedirect = false;
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($response);
                return $resultJson;
            // }
        } else {
            $response->isRedirect = false;
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($response);
            return $resultJson;
        }
        return $result;
    }

    public function validateAddress($address) {
        foreach ($address as $field) {
            if (empty($field)) {
                return false;
            }
        }
        return true;
    }
}