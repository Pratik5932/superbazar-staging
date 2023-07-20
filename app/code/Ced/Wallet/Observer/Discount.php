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


class Discount implements ObserverInterface
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
        if($this->_objectManager->get('Magento\Framework\Registry')->registry('wallet_used')){
            return $this;
        }
        $customerId = $this->session->getId();
        if(!$this->session->getId()){
            return $this;
        }

        if( $customerId ){
            if($this->_objectManager->get('Magento\Framework\Registry')->registry('wallet_used')){
                return $this;
            }

            $this->_objectManager->get('Magento\Framework\Registry')->register('wallet_used',true);
            $order = $observer->getOrder();
            $incrementid = $order->getIncrementId();
            $payment = $observer->getOrder()->getPayment()->getMethodInstance()->getCode();
            $getstatus = $this->session->getWalletStatus();

            if($payment=='wallet')
            {
                try{
                    $customerId = $this->session->getId();
                    $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
                    $amount = $customer->getAmountWallet();
                    $ordertotal = $order->getBaseGrandTotal();
                    $baseToOrderRate = $order->getBaseToOrderRate();
                    if($amount >= $ordertotal)
                    {
                        $finalamount = $ordertotal;
                    }else
                    {
                        $finalamount = $amount;
                    }
                    $label               = 'Wallet Amount';
                    $discount            =  $finalamount;
                    $discountAmount      = -$finalamount;
                    $currenctdiscount    =  $discount*$baseToOrderRate;
                    $currenctDiscount    = -$currenctdiscount;
                    $appliedCartDiscount = 0;


                    $order->setWalletPayment($finalamount);
                    $order->save();
                }catch(\Exception $e){
                    throw new \Magento\Framework\Exception\LocalizedException( __ ( $e->getMessage () ) );
                }


            } else if($payment!= 'wallet' && $getstatus == 'select') {

                $customerId = $this->session->getId();
                $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
                $amount = $customer->getAmountWallet();
                $ordertotal = $order->getBaseGrandTotal();
                $baseToOrderRate = $order->getBaseToOrderRate();
                try{
                    if($amount < $ordertotal){
                        $label               = 'Wallet Amount';
                        $discount            =  $amount;
                        $discountAmount      = -$discount;
                        $currenctdiscount    =  $discount*$baseToOrderRate;
                        $currenctDiscount    = -$currenctdiscount;
                        $appliedCartDiscount = 0;
                        
                        $order->setDiscountDescription($label);
                        $order->setDiscountAmount($currenctDiscount);
                        $order->setBaseDiscountAmount($discountAmount);
                        $order->setSubtotalWithDiscount($order->getSubtotal() + $currenctDiscount);
                        $order->setBaseSubtotalWithDiscount($order->getBaseSubtotal() + $discountAmount);
                        $order->setGrandTotal(($order->getBaseGrandTotal()*$baseToOrderRate)-$currenctdiscount);
                        $order->setWalletPayment($discount);
                        $order->save();

                    }
                }catch(\Exception $e){
                    throw new \Magento\Framework\Exception\LocalizedException( __ ( $e->getMessage () ) );
                }
            }
        }

        return $this;
    }
}