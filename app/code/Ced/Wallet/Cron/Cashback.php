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
namespace Ced\Wallet\Cron;
 
class Cashback
{
	
 	public $_objectManager;
    
    public function __construct(\Magento\Framework\ObjectManagerInterface $ob,
       \Magento\Framework\DB\TransactionFactory $transactionFactory
                                )
	{
    $this->_objectManager = $ob;
    $this->saveTransaction = $transactionFactory->create();
	}

    public function execute()
    {
       	$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/advtransaction.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info('advtransaction');
 
        return "hello";
    }
    
  
	 public function updateCashback(){
		$cashBack = $this->_objectManager->create('Ced\Wallet\Model\Cashback')->getCollection()->addFieldToFilter('status',\Ced\Wallet\Model\Cashback::PENDING);
		foreach ($cashBack as $key => $value) {
			$date1 = date_create($value['scheduled_at']);
            $date2 = date_create();
            $diff = date_diff($date1,$date2);

            if($diff->format("%a") == 0){
            	$msg = "interval ".$value['count']." cashback for order ".$value['order_id'];
            	$walletUpdate = $this->_objectManager->get('Ced\Wallet\Helper\Data')->updateCustomerWallet($value['order_id'],$value['customer_id'],$value['amount'],$msg,\Ced\Wallet\Model\Transaction::CREDIT);
                if($walletUpdate ['error']){
              				throw new \Magento\Framework\Exception\LocalizedException ( __ ($walletUpdate['msg']) );
              	}else{
              		$status = \Ced\Wallet\Model\Cashback::PAID;
              	}
               $this->_objectManager->create('Ced\Wallet\Model\Cashback')->load($value['id'])
               ->setStatus($status)->save();
            }
		}
		
	}

    public function deductCashback(){
        try{
            $walletTransaction = $this->_objectManager->create('Ced\Wallet\Model\Transaction')->getCollection()->addFieldToFilter('expiration_time',['lt'=>date('Y-m-d h:i:s')])
                ->addFieldToFilter('expiration_status',\Ced\Wallet\Model\Transaction::EXPIRATION_PENDING);
               $totalExpiredAmount = [];
                foreach ($walletTransaction as $key => $value) {
                    $remaining[$value->getCustomerId()] = $value->getAmount() - $value->getUsedAmount();
                     if(!isset($totalExpiredAmount[$value->getCustomerId()])){
                            $totalExpiredAmount[$value->getCustomerId()] = 0;
                     }
                     $totalExpiredAmount[$value->getCustomerId()]  = $totalExpiredAmount[$value->getCustomerId()]+$remaining[$value->getCustomerId()];
                     $value->setExpirationStatus(\Ced\Wallet\Model\Transaction::EXPIRATION_COMPLETED);
                     $this->saveTransaction->addObject($value);
                }
     
                foreach ($totalExpiredAmount as $k => $v) {
                   $OrderId = "Cashback";
                   $msg = "CashBack Expired";
                   $action = \Ced\Wallet\Model\Transaction::DEBIT;
    
                   $cashback = 0;
                   $updateWalletAmount = $this->_objectManager->get('Ced\Wallet\Helper\Data')->updateCustomerWallet($OrderId,$k,$v,$msg,$action,$cashback);
    
                   if(!$updateWalletAmount['error']){
                         $this->saveTransaction->save();
                   }
    
                }
                 
        }catch(\Exception $e){
           $this->saveTransaction->_rollbackTransaction();
        }
    }

}