<?php
namespace Webkul\MobikulMpHyperLocal\Plugin\Controller\Catalog;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Webkul\MpHyperLocal\Helper\Data as HyperlocalHelper;

class ProductPageData
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
    
    public function afterExecute(\Webkul\MobikulApi\Controller\Catalog\ProductPageData $subject, $result)
    {
        $params = $this->request->getParams();

        $productId = $params['productId'];
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
        if ($this->validateAddress($savedAddress)) {
            $sellerIds = $this->hyperlocalHelper->getNearestSellers();
            $allowedProList = $this->hyperlocalHelper->getNearestProducts($sellerIds);
            if (!in_array($productId,$allowedProList)) {
                if ($collectionFilter == "zipcode") {
                    $data = [
                        "success" => false,
                        "message" => __("Product unavailable in selected zipcode."),
                        "otherError" => "unavailable"
                    ];
                } else {
                    $data = [
                        "success" => false,
                        "message" => __("Product unavailable in selected region."),
                        "otherError" => "unavailable"
                    ];
                }
                $resultJson  = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($data);
                return $resultJson;
            }
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