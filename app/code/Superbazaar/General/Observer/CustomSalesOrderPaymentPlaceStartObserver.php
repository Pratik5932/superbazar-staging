<?php
/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Superbazaar\General\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomSalesOrderPaymentPlaceStartObserver implements ObserverInterface
{
    /**
     * Execute observer method
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $payment = $observer->getEvent()->getPayment();
        $order = $payment->getOrder();

        $method = $payment->getMethodInstance();
         $methodTitle = $method->getTitle();
        // Access order details
        // $orderId = $order->getIncrementId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $mpProCollection = $objectManager->create('Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory');
            $mpHelper = $objectManager->get('Webkul\Marketplace\Helper\Data');
            
            foreach ($order->getAllItems() as $item) {
                $productId = $item->getProductId();
                $sellerId = $mpProCollection->create()
                    ->addFieldToFilter('mageproduct_id', $productId)
                    ->setPageSize(1)
                    ->getFirstItem()
                    ->getSellerId();
                    $bankDetails = $mpHelper->getSellerCollectionObj($sellerId)->setPageSize(1)
                        ->getFirstItem()
                        ->getBankDetails();
                    
                
            }
        $additionalInformation = $payment->getAdditionalInformation();

        // Modify the bank details
        if ($methodTitle == "Bank Transfer Payment") {
          
        $additionalInformation['instructions'] = $bankDetails;

        }

        $payment->setAdditionalInformation($additionalInformation);
    }
}