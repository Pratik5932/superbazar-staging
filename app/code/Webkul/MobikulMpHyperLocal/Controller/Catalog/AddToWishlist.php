<?php
namespace Webkul\MobikulMpHyperLocal\Controller\Catalog;

class AddToWishlist extends \Webkul\MobikulApi\Controller\Catalog\AddToWishlist
{
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->params = $this->wholeData["params"] ?? "[]";
            $this->params = $this->jsonHelper->jsonDecode($this->params);
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            }
            if(isset($this->wholeData['address']) && isset($this->wholeData['latitude']) && isset($this->wholeData['longitude'])) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $hyperlocalHelper = $objectManager->create(\Webkul\MpHyperLocal\Helper\Data::class);
                $sellerIds = $hyperlocalHelper->getNearestSellers();
                $allowedProList = $hyperlocalHelper->getNearestProducts($sellerIds);
                if (!in_array($this->productId,$allowedProList)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Product unavailable in selected region.")
                    );
                }
            }
        } else {
            throw new \Exception(__("Invalid Request"));
        }
    }
}