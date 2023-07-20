<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Wallet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Wallet\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;


class Updatewallet implements ObserverInterface
{
    
    /** @var Session */
    protected $session;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    protected $_storeManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            Session $customerSession,
            \Magento\Framework\ObjectManagerInterface $objectmanager
            )
    {
        $this->scopeConfig = $scopeConfig;
        $this->session = $customerSession;
        $this->_objectManager = $objectmanager;
        $this->_storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) 
    {
      try{
        $order = $this->_objectManager->create('Magento\Sales\Model\Order');
         
        $incrementId = $this->_objectManager->get('Magento\Checkout\Model\Session')->getLastRealOrderId();
        $order->loadByIncrementId($incrementId);
       
        $payment = $order->getPayment()->getMethodInstance()->getCode();
        $getstatus = $this->session->getWalletStatus();
        if($payment =='wallet')
        {
            $amount = $order->getWalletPayment();
            $orderid = $order->getIncrementId();
            $customerId = $this->session->getId();
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
            $time = $order->getCreatedAt();
            $walletAmount = $customer->getAmountWallet();
            $action = '1'; //debit
            
            if($amount < 0){
                $amount = $amount * (-1);
            }
            $remainingAmount = $walletAmount - $amount;
           
            $customerData = $customer->getDataModel();
            $customerData->setCustomAttribute('amount_wallet', $remainingAmount);
            $customer->updateData($customerData);
            $customer->save();
            $this->updateWalletTransactions($orderid,$amount,$customerId,$action,$time);
        }
        if($payment!='wallet' && $getstatus == 'select')
        {
            $orderid = $order->getIncrementId();
            $amount = $order->getWalletPayment();
            $customerId = $this->session->getId();
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
            $walletAmount = $customer->getAmountWallet();
             
            $createdtime = $order->getCreatedAt();
            $action = \Ced\Wallet\Model\Transaction::DEBIT; 
        
            if($amount < 0){
                $amount = $amount * (-1);
            }
            $remainAmount = $walletAmount - $amount;
            $customerData = $customer->getDataModel();
            $customerData->setCustomAttribute('amount_wallet', $remainAmount);
            $customer->updateData($customerData);
            $customer->save();
             
            $this->updateWalletTransactions($orderid,$amount,$customerId,$action,$createdtime);
        } 
      }catch(\Exception $e){
        throw new \Magento\Framework\Exception\LocalizedException( __ ( $e->getMessage () ) );
      }      
        return $this;       
    }


    public function updateWalletTransactions($orderid,$amount,$customerId,$action,$time){
      
       if($amount != 0){
                $transaction = $this->_objectManager->create('Ced\Wallet\Model\Transaction');
                $transaction->setData('order_id',$orderid);
                $transaction->setData('amount',$amount);
                $transaction->setData('customer_id',$customerId);
                $transaction->setData('action',$action);
                $transaction->setData('created_at',$time);
                $transaction->save();
          
        $catshbackTransaction = $this->_objectManager->create('Ced\Wallet\Model\Transaction')->getCollection()->addFieldToFilter('is_cashback',1)
        ->addFieldToFilter('expiration_time',['gt'=>date('Y-m-d h:i:s')]);
        $catshbackTransaction->addFieldToFilter(
            'amount',
            ['gt' => new \Zend_Db_Expr('used_amount')]
        );
       
         $remainingAmount =  $amount; 
        foreach ($catshbackTransaction as $key => $value) {
            $wt = $this->_objectManager->create('Ced\Wallet\Model\Transaction')->load($value->getId());
            if($remainingAmount){
                  if($amount > $value->getAmount() ){
                   $remainingAmount = $amount-$value->getAmount();
                   $usedAmount = $value->getAmount();
                   $wt->setUsedAmount($value->getAmount());  
                }else{
                   $remainingAmount = 0;
                   $wt->setUsedAmount($amount);  
                }  
            }
            $wt->save();
        }
     }

    }
}
