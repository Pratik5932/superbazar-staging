<?php
/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Superbazaar\General\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;

class CheckPostcode implements ObserverInterface
{
    protected $messageManager;
    protected $request;

    public function __construct(
        ManagerInterface $messageManager,
        RequestInterface $request
    ) {
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {   
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $order = $observer->getEvent()->getOrder();
        $quote = $objectManager->get('\Magento\Checkout\Model\Session')->getQuote();
        $items =  $quote->getAllVisibleItems();
        // $items = $order->getAllItems();
        $address = $objectManager->get('Webkul\MpHyperLocal\Helper\Data')->getSavedAddress();
        $profilePostCode = $address ? $address['address'] :'';
        if(count($items) > 0){
                 foreach ($items as $item) {
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                    $attrName = $product->getAttributeText('store_location');
                     $storeLocation =  $product->getAttributeText('store_location');
        
                         $postCodes = [];

                        $collection = $objectManager->create('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
                        ->getCollection()
                        ->addFieldToSelect('seller_id')
                        ->addFieldToFilter('address_type', 'postcode')          
                        ->addFieldToFilter('postcode', $storeLocation);
                        $sellerId = $collection->getColumnValues('seller_id');
                        if($storeLocation == "3024" ){
                            $sellerId = array("6637");
                        }
                        $collectionpostcode = $objectManager->create('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
                        ->getCollection()
                        ->addFieldToSelect('postcode')
                        ->addFieldToFilter('address_type', 'postcode')          
                        ->addFieldToFilter('seller_id', $sellerId);
                        $postCodes = $collectionpostcode->getColumnValues('postcode');
                        //$postCodes[] = $loca;
                        sort($postCodes);
                        if (!in_array($profilePostCode, $postCodes)) {
                        //  $this->messageManager->addErrorMessage("Profile post code is and some of your items in cart is for other postcode. Please clear the shopping cart and shop again.");
                        //  $cartUrl = $objectManager->get('Magento\Framework\UrlInterface')->getUrl('checkout/cart');
                        // $response = $objectManager->get('Magento\Framework\App\Response\Http');
                        // $response->setRedirect($cartUrl);
        
                         }
                        
                       
                 }                  
         }
         return $this;
    }
}