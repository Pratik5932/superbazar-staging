<?php
/**
 * Webkul Hello CustomPrice Observer
 *
 * @category    Webkul
 * @package     Webkul_Hello
 * @author      Webkul Software Private Limited
 *
 */
namespace Ced\Wallet\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class Reorder implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\Order $salesOrder
    ){
        $this->_request = $request;
        $this->_salesOrder = $salesOrder;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $orderId = $this->_request->getParam('order_id');
        $productPrice = $this->_request->getParam('price');
        
        if($orderId){
            $order = $this->_salesOrder->load($orderId);
            $allItems = $order->getAllVisibleItems();
            $isWallet = false;
            $customPrice = 0;
            foreach($allItems as $key=>$item){
                if($item->getSku() == "wallet_product"){
                    $isWallet = true;
                    $customPrice = $item->getPrice();
                    break;
                }
            }
            
            if($isWallet){
                $item = $observer->getEvent()->getData('quote_item');         
                $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
                $item->setCustomPrice($customPrice);
                $item->setOriginalCustomPrice($customPrice);
                $item->getProduct()->setIsSuperMode(true);
            }
        }
        
        $item = $observer->getEvent()->getData('quote_item');
		if($item->getProduct()->getSku() == 'wallet_product' && $productPrice)
		{
		    $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
    		$item->setCustomPrice($productPrice);
    		$item->setOriginalCustomPrice($productPrice);
    		$item->getProduct()->setIsSuperMode(true);
		}
    }
}