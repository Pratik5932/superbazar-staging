<?php
namespace Superbazaar\General\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpHyperLocal\Model\ShipAreaFactory;
class OrderComplete implements ObserverInterface
{
    /**
    * @var ObjectManagerInterface
    */
    protected $_objectManager;

    /**
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Order $order,
        ShipAreaFactory $shipArea
    ) {
        $this->_objectManager = $objectManager;
        $this->order = $order;
        $this->shipArea = $shipArea;

    }

    /**
    *
    * @param \Magento\Framework\Event\Observer $observer
    * @return void
    */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getIncrementId();
        $shippingAddressObj = $order->getShippingAddress();
        if($shippingAddressObj){

            $shippingAddressArray = $shippingAddressObj->getData();
            $postcode = $shippingAddressArray['postcode'];
            $postCodeModel = $this->shipArea->create()->getCollection()
            ->addFieldToFilter('postcode', $postcode)
            ->addFieldToFilter('prefix', array('neq' => ""));

            if($postCodeModel->getSize()){
                $abn = $postCodeModel->getFirstItem()->getPrefix();
                $abnnumber = $postCodeModel->getFirstItem()->getAbn();
                if($abn){
                    $order->setIncrementId($abn.'-'.$orderId);
                }           
                $order->setAbn($abnnumber);
                $order->save();      
            }     
        }

    }
}