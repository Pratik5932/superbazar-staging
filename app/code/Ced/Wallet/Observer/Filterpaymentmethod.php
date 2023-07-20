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
use Magento\Framework\DataObject;
use Magento\Framework\Event\ObserverInterface;

class Filterpaymentmethod implements ObserverInterface
{
    protected $_objectManager;
    
    public $_datahelper;
   
    public function __construct(
      \Magento\Framework\ObjectManagerInterface $ob,
      \Ced\Wallet\Helper\Data $helperdata
    ) {
        $this->_objectManager = $ob;
        $this->_datahelper = $helperdata;
        
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $method = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();

        $store = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $cid = $this->_objectManager->get('Magento\Customer\Model\Session')->getId();
       
        if(!$this->_objectManager->create('Magento\Customer\Model\Customer')->load($cid)->getEnableWalletSystem())
        {
          if($method->getCode()=='wallet' )
            {
                $result->setData('is_available',false);
            } 
        }else if($store->getValue('ced_wallet/active/enable'))
        {
          
          $quote = $this->_objectManager->get('\Magento\Checkout\Model\Session')->getQuote()->getAllItems();
          $flag = false;
          foreach ($quote as $item)
          {
            if($item->getSku() == "wallet_product")
            {
                $flag = true;
            }
          }
          $noPaymethods = false;
          $value = 'payment/'.$method->getCode().'/group'; 
          $enableOfflinePayments = $this->_datahelper->enableOfflinePayments();
          $allowedMethods = $this->_datahelper->allowedpaymentmethods();  
          if(!is_array($allowedMethods)){
               $noPaymethods = true;
          }
        
          $group = $this->_datahelper->getStoreConfig($value);
    
          if($flag)
          {
            if($noPaymethods){
                $result->setData('is_available',false);
            }else{

            if(!in_array($method->getCode(),$allowedMethods))
            {
                $result->setData('is_available',false);
            }
                        
            if($method->getCode()=='wallet' )
            {
                $result->setData('is_available',false);
            }

            }
          }
           
    
        }else{
          
            if($method->getCode()=='wallet' )
            {
                $result->setData('is_available',false);
            }
        }

	     
	    }


}
