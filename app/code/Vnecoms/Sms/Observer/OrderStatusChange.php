<?php
namespace Vnecoms\Sms\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderStatusChange implements ObserverInterface
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
     * @param \Vnecoms\Sms\Helper\Data $helper
     * @param \Magento\Email\Model\Template\Filter $filter
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Vnecoms\Sms\Helper\Data $helper,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ){
        $this->helper = $helper;
        $this->filter = $filter;
        $this->customerFactory = $customerFactory;
    }
    
    /**
     * Vendor Save After
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $originStatus = $order->getOrigData('status');
        $status = $order->getStatus();
        if(!$originStatus || !$status || $originStatus == $status){
            return;
        }
            
        /* Send notification message to customer when order status is changed*/
        if(
            $this->helper->canSendOrderStatusChangedMessage($order->getStoreId())
        ) {
            if($order->getCustomerId()){
                $customer = $this->customerFactory->create()
                    ->load($order->getCustomerId());
            }else{
                $customer = $order->getIsVirtual()?$order->getBillingAddress():$order->getShippingAddress();
                $customer->setMobilenumber($customer->getTelephone());
            }
            
            $message = $this->helper->getOrderStatusChangedMessage($order->getStoreId());
            $this->filter->setVariables([
                'order' => $order,
                'customer' => $customer,
                'order_status' => $order->getStatusLabel()
            ]);
            $message = $this->filter->filter($message);
            $this->helper->sendCustomerSms($customer, $message);
        }
        
    }
}
