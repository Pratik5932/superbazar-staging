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


class CreditMemo implements ObserverInterface
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
        $order = $observer->getDataObject();
        if ($order && $order->getCustomerId()) {
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($order->getCustomerId());
            if ($customer && $customer->getId()) {
                if ($customer->getEnableWalletSystem()) {
                    $walletAmount = 0;
                    $creditMemoAmount = $observer->getCreditmemo()->getGrandTotal();
                    $walletAmount = $customer->getAmountWallet();
                    $walletAmount = $walletAmount + $creditMemoAmount;
                    $customerData = $customer->getDataModel();
                    $customerData->setCustomAttribute('amount_wallet', $walletAmount);
                    $customer->updateData($customerData);
                    $customer->save();
                    $transaction = $this->_objectManager->create('Ced\Wallet\Model\Transaction');
                    $transaction->setData('order_id',$order->getIncrementId());
                    $transaction->setData('amount',$creditMemoAmount);
                    $transaction->setData('customer_id',$customer->getId());
                    $transaction->setData('action','Credit');
                    $transaction->setData('created_at',$this->_objectManager->get('\Magento\Framework\Stdlib\DateTime\DateTime')->gmtDate());
                    $transaction->save();
                }
            }
        }
        return true;       
    }
}
