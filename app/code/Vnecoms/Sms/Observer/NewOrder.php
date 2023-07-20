<?php
namespace Vnecoms\Sms\Observer;

use Magento\Framework\Event\ObserverInterface;

class NewOrder implements ObserverInterface
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
        $orders = $observer->getOrder();
        if(!$orders) $orders = $observer->getOrders();
        
        if(!is_array($orders)) $orders = [$orders];
        $customer = false;
        foreach($orders as $order){
            /* Send notification message to admin when a new order is placed*/
            if($this->helper->canSendNewOrderMessageToAdmin($order->getStoreId())){
                $message = $this->helper->getNewOrderMessageSendToAdmin($order->getStoreId());
                $this->filter->setVariables([
                    'order' => $order,
                    'order_total' => $order->formatPriceTxt($order->getGrandTotal()),
                    'order_total_amount' => $order->getOrderCurrency()->formatTxt(
                        $order->getGrandTotal(),
                        ['display' => \Magento\Framework\Currency::NO_SYMBOL]
                    ),
                ]);
                $message = $this->filter->filter($message);
                $this->helper->sendAdminSms($message);
            }
            
            /* Send notification message to customer when a new order is placed*/
            if(
                $this->helper->canSendNewOrderMessage($order->getStoreId())
            ) {
                if($order->getCustomerId()){
                    if(!$customer) $customer = $this->customerFactory->create()
                        ->load($order->getCustomerId());
                }else{
                    $customer = $order->getIsVirtual()?$order->getBillingAddress():$order->getShippingAddress();
                    $customer->setMobilenumber($customer->getTelephone());
                }
                
                $this->sendSms($order, $customer);
            }
        }
    }
    
    /**
     * Send Sms
     * 
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\DataObject $customer
     */
    public function sendSms(
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\DataObject $customer
    ) {
        $message = $this->helper->getNewOrderMessage($order->getStoreId());
        $this->filter->setVariables([
            'order' => $order,
            'customer' => $customer,
            'order_total' => $order->formatPriceTxt($order->getGrandTotal()),
            'order_total_amount' => $order->getOrderCurrency()->formatTxt(
                $order->getGrandTotal(),
                ['display' => \Magento\Framework\Currency::NO_SYMBOL]
            ),
        ]);
        $message = $this->filter->filter($message);
        $this->helper->sendCustomerSms($customer, $message);
    }
}
