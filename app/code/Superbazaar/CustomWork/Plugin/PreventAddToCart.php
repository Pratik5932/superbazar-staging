<?php

namespace Superbazaar\CustomWork\Plugin;

use Magento\Checkout\Model\Cart;

class PreventAddToCart
{
    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        //$zipcode = $cart->getQuote()->getShippingAddress()->getPostcode();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $custId = $customerSession->getCustomer()->getId();
        $zipcode = $customerSession->getCustomer()->getDefaultShippingAddress()->getPostcode();
        $address = $objectManager->get('Webkul\MpHyperLocal\Helper\Data')->getSavedAddress();
        $profilePostCode = $address ? $address['address'] :'';

        /*$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $collection = $productCollection->create()
                      ->addAttributeToSelect('*')
                      ->addAttributeToFilter('store_location', '1')
                      ->addFieldToFilter('id', '1')
                      ->load();*/

        if (true) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Profile post code is '.$profilePostCode.' and shipping post code entered is '.$zipcode.'. Please change the shopping post code and shop again'));
        }
        return [$productInfo,$requestInfo];
    }
}