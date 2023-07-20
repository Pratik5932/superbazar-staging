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

use Magento\Framework\Event\ObserverInterface;

class ChangeStatus implements ObserverInterface {

	protected $_objectManager;

	public $_helper;

    public $date;

	public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Framework\Registry $registry, 
        \Ced\Wallet\Helper\Data $cashbackhelper,
        \Magento\Framework\ObjectManagerInterface $objectmanager
	) {
		$this->date = $date;
		$this->_coreRegistry = $registry;
		$this->_helper = $cashbackhelper;
		$this->scopeConfig = $scopeConfig;
		$this->_objectManager = $objectmanager;
	}
	/**
	 * custom event handler
	 *
	 * @param \Magento\Framework\Event\Observer $observer        	
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $observer) {
		try {
			if($this->_coreRegistry->registry('wallet_observer')){
				return $this;
			}
			$this->_coreRegistry->register('wallet_observer',1);
			
			$invoice = $observer->getEvent()->getInvoice ();
			$order = $invoice->getOrder ();
			if(!$order->getCustomerId())
			   return $this;
			$customerId = $order->getCustomerId();
            $id = $order->getId ();
			$status = $order->getStatus ();
			
			$isCashbackAllowed = true;
			foreach ( $order->getAllItems () as $item ) {
			    if ($item->getSku () == "wallet_product") {
			        $isCashbackAllowed = false;
			        break;
			    }
			}
			
			if($order->getWalletPayment()!=$order->getGrandTotal()){
			     $isCashbackAllowed = false;
			}
			
			if(count($order->getAllItems()) == 1){
                
				foreach ( $order->getAllItems () as $item ) {

					if ($item->getSku () == "wallet_product") {


                        $transactionCollection = $this->_objectManager->create ( 'Ced\Wallet\Model\Transaction' )
                                                    ->getCollection()
                                                    ->addFieldToFilter('customer_id', $order->getCustomerId())
                                                    ->addFieldToFilter('order_id', $order->getIncrementId())
                                                    ->addFieldToFilter('is_walletrecharge', 1);
                        $checkCount  = $transactionCollection->count();

                        if($checkCount == 0){
                            $customer = $this->_objectManager->create ( 'Magento\Customer\Model\Customer' )->load ( $customerId );
                            $amount = $customer->getAmountWallet ();
                            $price = $item->getBaseRowTotalInclTax();
                        
                            $updatedWallet = $price + $amount;
                            $customerData = $customer->getDataModel();
                            $customerData->setCustomAttribute ('amount_wallet', $updatedWallet);
                            $customer->updateData ( $customerData );
                            $customer->save ();
                            
                            $transaction = $this->_objectManager->create ( 'Ced\Wallet\Model\Transaction' );
                            $transaction->setData ( 'order_id', $order->getIncrementId () );
                            $transaction->setData ( 'amount', $price );
                            $transaction->setData ( 'customer_id', $order->getCustomerId () );
                            $transaction->setData ( 'action', \Ced\Wallet\Model\Transaction::CREDIT );
                            $transaction->setData ( 'created_at', $this->_objectManager->get ( '\Magento\Framework\Stdlib\DateTime\DateTime' )->gmtDate() );
                            $transaction->setData('is_walletrecharge',1);
                            $transaction->setData('comment', __('Wallet Recharge'));
                            $transaction->save ();
                        }
					}
				}

				if($isCashbackAllowed){
				    if(!$order->canInvoice()){
    				    $this->setCasback($order);
    				}
				}
			}else{
			    if($isCashbackAllowed){
                    if(!$order->canInvoice()){
                        $this->setCasback($order);
                    }
			    }
			}
		}catch(\Exception $e ) {
		  throw new \Magento\Framework\Exception\LocalizedException( __ ( $e->getMessage () ) );
		}
	}


	public function setCasback($order){
		
	  $baseToOrderRate = $order->getBaseToOrderRate();
      if($this->_helper->enableProductCashback()){
      $cashbackproducts = json_decode($this->_helper->cashbackproducts(),true);
      if(is_array($cashbackproducts)){
        $data = [];$skus = [];
      foreach ($cashbackproducts as $k => $v) {
        $skus[] = $v['cashback'];
        $data[$v['cashback']] = ['mode'=>$v['mode'],'amount'=>$v['amount'],'max'=>$v['max']];
      }

      foreach ( $order->getAllVisibleItems() as $item) {

           if(in_array($item->getSku(),$skus)){

               if($data[$item->getSku()]['mode'] == "fixed"){
                 $cashbackAmount = $data[$item->getSku()]['amount'];
               }else{
                $cashbackAmount = ($item->getBaseRowTotalInclTax()*$data[$item->getSku()]['amount'])/100;
               }
               if($data[$item->getSku()]['max'] && $cashbackAmount > $data[$item->getSku()]['max']){
                    $cashbackAmount =  $data[$item->getSku()]['max'];
               }
               $msg = "Cashbask for Product ".$item->getName();
               $walletUpdate = $this->_helper->updateCustomerWallet($order->getIncrementId(), $order->getCustomerId(), $cashbackAmount, $msg, \Ced\Wallet\Model\Transaction::CREDIT);
              if($walletUpdate['error']){
                 throw new \Magento\Framework\Exception\LocalizedException (__($walletUpdate['msg']));
              }
           }
        }
      }
    }
     if($this->_helper->enableCashback()){
           if($this->_helper->enableCashback() == 'first_order'){
               $checkFirstOrder = $this->getFirstOrder($order);
               if(!$checkFirstOrder){
               	return false;
               }
          }
          $orderTotal = $order->getBaseGrandTotal();
          $interval = $this->_helper->cashbackinterval();
          $minOrder = $this->_helper->minOrder();
          $maxCashBack = $this->_helper->maxcashback();
          $cashbackpercent =  $this->_helper->cashbackpercent();
        
          if($orderTotal >= $minOrder) {
              $cashback = ($orderTotal * $cashbackpercent) / 100;
              if ($maxCashBack && $cashback > $maxCashBack) {
                  $cashback = $maxCashBack;
              }
              $cashbackPayment = $this->_helper->cashbackpayment();

              if ($cashbackPayment == "interval"){
                  $cashback_timeDurations = $this->_helper->cashbackdaysInterval();
                  $amount = $cashback / $interval;
                 // $amount = $amount/$baseToOrderRate;
                  if (is_array($cashback_timeDurations)) {
                      foreach ($cashback_timeDurations as $key => $value) {
                          $status = \Ced\Wallet\Model\Cashback::PENDING;
                          if ($value['time'] == 0) {
                              $updatecount = $key + 1;
                              $msg = "interval " . $updatecount . " cashback for order ".$order->getIncrementId();
                              $walletUpdate = $this->_helper->updateCustomerWallet($order->getIncrementId(), $order->getCustomerId(), $amount, $msg, \Ced\Wallet\Model\Transaction::CREDIT);

                              if ($walletUpdate ['error']) {
                                  throw new \Magento\Framework\Exception\LocalizedException (__($walletUpdate['msg']));
                              } else {
                                  $status = \Ced\Wallet\Model\Cashback::PAID;
                              }
                          }

                          $Days = "+" . $value['time'] . " days";
                          $cashbackModel = $this->_objectManager
                              ->create('Ced\Wallet\Model\Cashback');
                          $cashbackModel->setOrderId($order->getIncrementId());
                          $cashbackModel->setCustomerId($order->getCustomerId());
                          $cashbackModel->setStatus($status);
                          $cashbackModel->setAmount($amount);
                          $cashbackModel->setCount($key + 1);
                          $cashbackModel->setScheduledAt(date('Y-m-d h:i:s', strtotime($Days)));
                          $cashbackModel->save();
                      }
                  }
              }else{
                  $msg = "Cashback for Order ".$order->getIncrementId();
                  $walletUpdate = $this->_helper->updateCustomerWallet($order->getIncrementId(), $order->getCustomerId(), $cashback, $msg, \Ced\Wallet\Model\Transaction::CREDIT);
                  if ($walletUpdate ['error']) {
                      throw new \Magento\Framework\Exception\LocalizedException (__($walletUpdate['msg']));
                  } else {
                      $status = \Ced\Wallet\Model\Cashback::PAID;
                  }
                 // $cashback = $cashback / $baseToOrderRate;
                  $cashbackModel = $this->_objectManager
                      ->create('Ced\Wallet\Model\Cashback');
                  $cashbackModel->setOrderId($order->getIncrementId());
                  $cashbackModel->setCustomerId($order->getCustomerId());
                  $cashbackModel->setStatus($status);
                  $cashbackModel->setAmount($cashback);
                  $cashbackModel->setCount(1);
                  $cashbackModel->setScheduledAt(date('Y-m-d h:i:s'));
                  $cashbackModel->save();
              }
          }
       }
}

    public function getFirstOrder($order){
        $emailId = $order->getCustomerEmail();
        $walletrechargeIds = $this->_objectManager->create('Ced\Wallet\Model\Transaction')->getCollection()->addFieldToFilter('is_walletrecharge',1)->getColumnValues('order_id');
        $cfirstOrderorder = $this->_objectManager->create('Magento\Sales\Model\Order')->getCollection()
                ->addFieldToFilter('customer_email',array('eq'=>$emailId))->addFieldToFilter('increment_id',['nin'=>$walletrechargeIds])->getFirstItem();

        if($cfirstOrderorder->getId() == $order->getId())
        {
        	return true;
        }else{
        	return false;
        }
   }
}
