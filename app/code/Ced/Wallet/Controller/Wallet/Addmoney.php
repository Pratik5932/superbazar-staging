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

namespace Ced\Wallet\Controller\Wallet;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Addmoney extends \Magento\Customer\Controller\AbstractAccount
{
	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;

	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	public function __construct(
		Context $context,
		\Ced\Wallet\Helper\Data $helper,
		PageFactory $resultPageFactory
	) {
		parent::__construct($context);
		$this->helper = $helper;
		$this->resultPageFactory = $resultPageFactory;
	}

	/**
	 *
	 * @return \Magento\Framework\View\Result\Page
	 */
	public function execute()
	{
        $isEnabled = $this->helper->isModuleEnabled();
        if (!$isEnabled) {
            $this->_redirect("*/*");
        }

        
		$checkoutSession = $this->_objectManager->get('Magento\Checkout\Model\Session');
	    $allItems = $checkoutSession->getQuote()->getAllVisibleItems();
	    $cart = $this->_objectManager->create('Magento\Checkout\Model\Cart'); 
	    foreach ($allItems as $item) {
	        $itemId = $item->getItemId();//item id of particular item
	        $cart->removeItem($item->getId()); 
	    }

		$postValue = $this->getRequest()->getParams();

		if(floatval($postValue['price'])>0){
			$resultPage = $this->resultPageFactory->create();
			$params = array(
					'qty'   =>1,
			);
			

			$quote = $this->_objectManager->get('Magento\Checkout\Model\Session')->getQuote();
			$storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
			$baseCurrencyCode = $this->_objectManager->get('Magento\Directory\Helper\Data')->getBaseCurrencyCode();
			$priceCurrencyObject = $this->_objectManager->get('Magento\Framework\Pricing\PriceCurrencyInterface');
			
			$baseToQuoteRate = $this->_objectManager->create('\Magento\Directory\Helper\Data')->currencyConvert(1, $baseCurrencyCode);
			$price = floatval($postValue['price'])/$baseToQuoteRate;
			

			$productId = $this->_objectManager->get('Magento\Catalog\Model\Product')->loadByAttribute('sku','wallet_product')->getId();
			$_product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
			$_product->setPrice($price);
			
			if(count($quote->getAllItems()) == 0)
			{
			    $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart')->addProduct($_product, $params);
				$cart->save();
				$quote->save();
			}
			
			$this->_redirect("checkout/index/index", ['price'=>$price]);
		}else{
			$this->messageManager->addErrorMessage(__('Minimum amount not meet.'));
			$this->_redirect("wallet/walletaddmoney/");
		}
		
		return $resultPage;
	}
}