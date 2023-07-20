<?php

namespace Webkul\MobikulMpHyperLocal\Controller\Customer;

class ReOrder extends \Webkul\MobikulApi\Controller\Customer\ReOrder
{
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->incrementId = $this->wholeData["incrementId"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
            $this->address = $this->wholeData["address"] ?? "";
            $this->latitude = $this->wholeData["latitude"] ?? "";
            $this->longitude = $this->wholeData["longitude"] ?? "";
            $this->city = $this->wholeData["city"] ?? "";
            $this->state = $this->wholeData["state"] ?? "";
            $this->country = $this->wholeData["country"] ?? "";
            if ($this->address=="") {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Please set an address.")
                );
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $hyperlocalHelper = $objectManager->get(\Webkul\MpHyperLocal\Helper\Data::class);
            $order = $this->order->loadByIncrementId($this->incrementId);
            $orderItems = $order->getAllItems();
            $nearestSellers = $hyperlocalHelper->getNearestSellers();
            foreach ($orderItems as $item) {
                $sellerId = $this->getProductSellerId($item->getProductId());
                if (!in_array($sellerId, $nearestSellers)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Seller do not provide shipping at your location.')
                    );
                }
            }
        } else {
            throw new \Exception(__("Invalid Request"));
        }
    }
    
    private function getProductSellerId($proId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $mpProduct = $objectManager->get(\Webkul\Marketplace\Model\Product::class);
        $sellerId = 0;
        $sellerPro = $mpProduct->getCollection()
                        ->addFieldToFilter('mageproduct_id', $proId)
                        ->setPageSize(1)->getFirstItem();
        if ($sellerPro->getEntityId()) {
            $sellerId =  $sellerPro->getSellerId();
        }
        return $sellerId;
    }
}