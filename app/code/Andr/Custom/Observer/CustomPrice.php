<?php
	/**
	 * Webkul Hello CustomPrice Observer
	 *
	 * @category    Webkul
	 * @package     Webkul_Hello
	 * @author      Webkul Software Private Limited
	 *
	 */
	namespace Andr\Custom\Observer;

	use Magento\Framework\Event\ObserverInterface;
	use Magento\Framework\App\RequestInterface;

	class CustomPrice implements ObserverInterface
	{
        protected $_request;
        protected $_helper;
        protected $cart;
        public function __construct(\Magento\Framework\App\RequestInterface $request,\Magento\Checkout\Model\Cart $cart)//\TW\Product\Helper\Data $helper,
        {
            $this->_request = $request;
            //$this->_helper = $helper;
            $this->cart=  $cart;
        }
        
		public function execute(\Magento\Framework\Event\Observer $observer) {

			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$dir = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
			$base = $dir->getRoot();
		
			$custom_pricing_path = $base.'/custom_pricing.csv';

			$item = $observer->getEvent()->getData('quote_item');			
			$item = ( $item->getParentItem() ? $item->getParentItem() : $item );

			if(file_exists($custom_pricing_path)) {
				$pricing_c = file($custom_pricing_path);
				//sku,code,price
				foreach($pricing_c as $row) {
					$row = str_replace('"','',$row);
					$row_ex = explode(',',$row);
					if(trim($row_ex[0]==$item->getSku())) {
						$codes_a = explode(';',$row_ex[1]);
						if(count($codes_a) > 1) {
							foreach($codes_a as $c1) {
								$custom_price[trim($c1)] = trim($row_ex[2]);
							}
						} else {
						$custom_price[trim($row_ex[1])] = trim($row_ex[2]);
						}
					}
				}
			}


			if(isset($custom_price)) {
				$price = $custom_price[$_COOKIE['zipcode']]; //set your price here
				$item->setCustomPrice($price);
				$item->setOriginalCustomPrice($price);
				$item->getProduct()->setIsSuperMode(true);
			}

		}

	}