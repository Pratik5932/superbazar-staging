<?php
namespace Vnecoms\Sms\Observer;

use Magento\Framework\Event\ObserverInterface;

class NewShipment implements ObserverInterface
{
    /**
     * @var \Vnecoms\Sms\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Magento\Email\Model\Template\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    
    /**
     * @var \Vnecoms\Sms\Model\ResourceModel\Sms\CollectionFactory
     */
    protected $smsCollectionFactory;
    
    /**
     * @param \Vnecoms\Sms\Helper\Data $helper
     * @param \Magento\Email\Model\Template\Filter $filter
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Vnecoms\Sms\Model\ResourceModel\Sms\CollectionFactory $smsCollectionFactory
     */
    public function __construct(
        \Vnecoms\Sms\Helper\Data $helper,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Vnecoms\Sms\Model\ResourceModel\Sms\CollectionFactory $smsCollectionFactory
    ){
        $this->helper = $helper;
        $this->filter = $filter;
        $this->customerFactory = $customerFactory;
        $this->smsCollectionFactory = $smsCollectionFactory;
    }
    
    /**
     * Vendor Save After
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment   = $observer->getShipment();
        $order      = $shipment->getOrder();
        $additionalData = 'shipment|'.$shipment->getId();
        
        /* Check if the SMS is sent already*/
        $collection = $this->smsCollectionFactory->create()
            ->addFieldToFilter('additional_data',['like' => $additionalData]);
        if($collection->count()) return;
        
        /* Send notification message to customer when a new shipment is created*/
        if(
            $this->helper->canSendNewShipmentMessage($order->getStoreId())
        ) {
            if($order->getCustomerId()){
                $customer = $this->customerFactory->create()
                    ->load($order->getCustomerId());
            }else{
                $customer = $order->getIsVirtual()?$order->getBillingAddress():$order->getShippingAddress();
                $customer->setMobilenumber($customer->getTelephone());
            }
            
            $message = $this->helper->getNewShipmentMessage($order->getStoreId());
            $trackingCodes = [];
            foreach ($shipment->getAllTracks() as $track){
            	$trackingCodes[] = $track->getNumber();
            }
            
            $trackingCodes = implode(',', $trackingCodes);
            
            $this->filter->setVariables([
                'order' => $order,
                'shipment' => $shipment,
                'customer' => $customer,
            	'tracking_code' => $trackingCodes,
            ]);
            
            $message = $this->filter->filter($message);
            $this->helper->sendCustomerSms($customer, $message, $additionalData);
        }
        
    }
}
