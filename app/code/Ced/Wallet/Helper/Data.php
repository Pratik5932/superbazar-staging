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
namespace Ced\Wallet\Helper;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $_objectManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    )
    {
        $this->_objectManager = $objectManager;
        $this->_quoteFactory = $quoteFactory;
        $this->_priceCurrency = $priceCurrency;      
        parent::__construct($context);
    }

    public function enabled()
    {
        return $this->scopeConfig->getValue('payment/wallet/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    }
    
    
    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue('ced_wallet/active/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    }

    public function getStoreConfig($value)
    {
        return $this->scopeConfig->getValue($value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    }

    public function validateMinAmount($amount){

        $minAmount = $this->scopeConfig->getValue('ced_wallet/active/min_amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            
        if($amount >= $this->convert($minAmount)){
            return ['error'=>false,'min_amount'=>$this->formatPrice($minAmount)];
        }else{
            return ['error'=>true,'min_amount'=>$this->formatPrice($minAmount)];
        }

    }
    /**
     * to convert into current currency
     */
    public function convert($amount){
        $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\PriceCurrencyInterface');
        $walletAmount = $priceHelper->convert($amount);
        return $walletAmount;
    }

    public function enableCashback(){
        return $this->getStoreConfig('ced_wallet/cashback/enable_cashback');
    }

    public function minOrder(){
        return $this->getStoreConfig('ced_wallet/cashback/order_amount');
    }

    public function maxcashback(){
        return $this->getStoreConfig('ced_wallet/cashback/max_cashback');
    }

    public function cashbackpercent(){
        return $this->getStoreConfig('ced_wallet/cashback/cashback_percentage');
    } 

    public function cashbacktime(){
        return $this->getStoreConfig('ced_wallet/cashback/cashback_time');
    }

    public function cashbackinterval(){
        return $this->getStoreConfig('ced_wallet/cashback/cashback_interval');
    }

    public function startcashback(){
        return $this->getStoreConfig('ced_wallet/cashback/start_cashback');
    }

    public function cashbackpayment(){
        return $this->getStoreConfig('ced_wallet/cashback/cashback_payment');
    }

    public function cashbackexpiration(){
        return $this->getStoreConfig('ced_wallet/cashback/cashback_expire');
    }

    public function cashbackproducts(){
        return $this->getStoreConfig('ced_wallet/cashback/product_cashback');
    }
    
    public function enableProductCashback(){
        return $this->getStoreConfig('ced_wallet/cashback/enable_product_cashback');
    }
    
     public function enableOfflinePayments(){
        return $this->getStoreConfig('ced_wallet/active/enable_offlinepayments');
    }

    public function allowedpaymentmethods(){
        $allowedMethods =  $this->getStoreConfig('ced_wallet/active/allowed_payment_methods');
        if($allowedMethods)
            return explode(',', $allowedMethods);


    }
    
    public function formatPrice($amount){
        $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data');
        $walletAmount = $priceHelper->currency($amount, true, false);
        return $walletAmount;
    }


    public function cashbackdaysInterval(){
         $interval = $this->cashbackinterval();
         $amount = $this->cashbackinterval()/ $this->cashbacktime();
         $intervalArr = [];
         $startCashback = $this->startcashback();
         if(!$startCashback){
             $startCashback = 0;
         }
         $time = $this->cashbacktime()-$startCashback;
         $extra = $time % ($interval-1);

         $value = ($time-$extra)/($interval-1);
         for($i=1;$i<= $interval ;$i++){
             
            if($i == 1){
              $intervalArr[] =  ['time'=>$startCashback] ;
            } 
            else if($i != $interval){
                $intervalArr[] =  ['time'=>$value] ;
            }else{
                $intervalArr[] = ['time'=>$value+$extra];
            }
         }
        
       return $intervalArr;
         
    }

  public function getTrasferredAmount(){
     $customerId = $this->getCustomerId();
     $transferedData = $this->_objectManager->create('Ced\Wallet\Model\Transaction')->getCollection()->addFieldToFilter('action',\Ced\Wallet\Model\Transaction::DEBIT)->addFieldToFilter('customer_id',$customerId)
          ->addFieldToFilter( 'transaction_with', array('neq' => ''));
     
     $amount = 0;
     foreach ($transferedData as $key => $value) {
       $amount = $amount +$value->getAmount();
     }
     return $amount;
   }


   public function getRequestedAmount(){
     $customerId = $this->getCustomerId();
     $transferedData = $this->_objectManager->create('Ced\Wallet\Model\Request')->getCollection()->addFieldToFilter('status',\Ced\Wallet\Model\Request::PENDING)->addFieldToFilter('customer_id',$customerId);
     $amount = 0;
     foreach ($transferedData as $key => $value) {
       $amount = $amount +$value->getAmount();
     }
     return $amount;
   }

    public function updateCustomerWallet($orderId,$customerId,$amount,$msg,$action,$cashback=1){
        if($customerId){
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
        }else{
            return ['error'=>true,'msg'=>"Customer Doesn't exist"];
        }

        try{
            
            if($cashback != 1){
               $cashback = 0;
            }
           
            $expirationData = null;
           
            if($cashback && $this->cashbackexpiration()){
              $cashbackExpiration = $this->cashbackexpiration();
              $Days = "+" . $cashbackExpiration . " days";
              $expirationData =  date('Y-m-d h:i:s', strtotime($Days));  
            }
           
            $walletAmount = $customer->getAmountWallet();
            if($action == \Ced\Wallet\Model\Transaction::CREDIT){
                $total =  $walletAmount + $amount;
            }else{
                $expirationData = null;
                $cashback = 0;
                if($walletAmount >= $amount){
                    $total = $walletAmount - $amount;
                 }
                 else{
                   return ['error'=>true,'msg'=>"Wallet does not have suffient amount"]; 
                 }
             }
            
            $customerData = $customer->getDataModel();
            $customerData->setCustomAttribute('amount_wallet', $total);
            $customer->updateData($customerData);
            $customer->save();
            $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
            
            $currentTimestamp = $time->timestamp(time());
            $date = date('Y-m-d H:i:s', $currentTimestamp);
            $transaction = $this->_objectManager->create('Ced\Wallet\Model\Transaction');
                
            $transaction->setData('order_id',$orderId);
            $transaction->setData('action',$action); //0 for credit
            $transaction->setData('customer_id',$customerId);
            $transaction->setData('amount',floatval($amount));
            $transaction->setData('comment',$msg);
            $transaction->setData('created_at',$date);
            $transaction->setData('is_cashback',$cashback);
            $transaction->setData('expiration_time',$expirationData);
            $transaction->save();

            return ['error'=>false];
        } catch (\Exception $e){
            
        return ['error'=>true,'msg'=>$e->getMessage()];
        }
      }

      /*
     * get amount in customer wallet
    * @return float
    */
    public function getWalletAmount()
    {
       $customerId = $this->_objectManager->create('Magento\Customer\Model\Session')->getId();

        $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
        $amount = $customer->getAmountWallet();
        return $amount;
    }

    public function getCustomerId(){
        return $this->_objectManager->get('Magento\Customer\Model\Session')->getId();
    }

    /**
     * Get formatted by price and currency
     *
     * @param   $price
     * @param   $currency
     * @return  array || float
     */
    public function getFormatedPrice($price, $currency)
    {
        $precision = 2;
        return $this->_priceCurrency->format(
            $price,
            $includeContainer = true,
            $precision,
            $scope = null,
            $currency
        );
    }
}