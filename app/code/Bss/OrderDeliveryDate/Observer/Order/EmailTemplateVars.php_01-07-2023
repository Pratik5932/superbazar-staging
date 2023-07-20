<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\OrderDeliveryDate\Observer\Order;

class EmailTemplateVars implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * EmailTemplateVars constructor.
     * @param \Bss\OrderDeliveryDate\Helper\Data $helper
     */
    public function __construct(\Bss\OrderDeliveryDate\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return array
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getTransport();
        $order = $transport['order'];
        $transport['delivery_time_slot'] = $order->getShippingArrivalTimeslot();
        $transport['shipping_arrival_comments'] = $order->getShippingArrivalComments();
		$payment = $order->getPayment();
		$method = $payment->getMethodInstance();
		$methodCode = $method->getCode();
		if($methodCode == 'banktransfer'){
			
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
				if (!empty($sellerId)) {
					$bankDetails = $mpHelper->getSellerCollectionObj($sellerId)->setPageSize(1)
						->getFirstItem()
						->getBankDetails();
					if (!empty($bankDetails)) {
						$transport['instructions'] = $bankDetails;
					}
				}
				break;
			}
			
			#$additional = $payment->getAdditionalInformation();
			#$transport['instructions'] = $additional['instructions'];
			#echo $additional['instructions'];
		}
		
		#$objectmanager = \Magento\Framework\App\ObjectManager::getInstance();
		#$objectmanager->get('Psr\Log\LoggerInterface')->info("banktanfer-----------------------"); //Print log in var/log/system.log
	#	$objectmanager->get('Psr\Log\LoggerInterface')->info($transport['instructions']); //Print log in var/log/system.log
		
        if ($order->getShippingArrivalDate()) {
            $timestamp = strtotime($order->getShippingArrivalDate());
           # $transport['shipping_arrival_date'] = $this->helper->formatDate($order->getShippingArrivalDate());
            $transport['shipping_arrival_date'] =  date('dS F y',$timestamp);
        }

        return $transport;
    }
}
