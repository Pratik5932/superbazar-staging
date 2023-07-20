<?php
namespace Webkul\MobikulMpHyperLocal\Controller\Customer;

class WishlistToCart extends \Webkul\MobikulApi\Controller\Customer\WishlistToCart
{
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->qty = $this->wholeData["qty"] ?? 1;
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->itemId = $this->wholeData["itemId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            }
            if(isset($this->wholeData['address']) && isset($this->wholeData['latitude']) && isset($this->wholeData['longitude'])) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $hyperlocalHelper = $objectManager->create(\Webkul\MpHyperLocal\Helper\Data::class);
                $wishlistItem = $objectManager->create(\Magento\Wishlist\Model\Item::class)->load($this->itemId);
                $this->productId = $wishlistItem->getProductId();
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