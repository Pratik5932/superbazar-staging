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


class AddToCart implements ObserverInterface
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
    	$items = $this->_objectManager->get('Magento\Checkout\Model\Cart')->getItems();
    	$cart = $this->_objectManager->create('Magento\Checkout\Model\Cart');
    	
    	foreach($items as $key=>$item){
    		
    		if($item->getSku() == "wallet_product"){
    			$cart->removeItem($item->getId());
    		}
    	}  
    	
    	return $this;
    	
    }
}