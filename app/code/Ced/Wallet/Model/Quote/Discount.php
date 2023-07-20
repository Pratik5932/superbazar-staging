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
namespace Ced\Wallet\Model\Quote;

use Magento\Customer\Model\Session;
class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
	
	/** @var Session */
	protected $session;
	
    /**
     * Discount calculation object
     *
     * @var \Magento\SalesRule\Model\Validator
     */
    protected $calculator;
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    public $_objectManager;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
    	\Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\SalesRule\Model\Validator $validator,
    		Session $customerSession,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->setCode('testdiscount');
        $this->eventManager = $eventManager;
        $this->session = $customerSession;
        $this->_objectManager=$objectManager;
        $this->calculator = $validator;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
    }
    /**
     * Collect address discount amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
    	
        parent::collect($quote, $shippingAssignment, $total);
        
        $customerId = $this->session->getId();
        $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
        $amount = $customer->getAmountWallet();
        if($amount>=$total->getSubtotal()){
            $finalamount = $total->getSubtotal()+$shippingAssignment->getShipping()->getAddress()->getShippingAmount();
        }else {
        	$finalamount = $amount;
        } 
        $address             = $shippingAssignment->getShipping()->getAddress();
        $label               = 'Wallet Amount';
        $discountAmount      = -$finalamount;   
        $appliedCartDiscount = 0;
        if($total->getDiscountDescription()) {
            // If a discount exists in cart and another discount is applied, the add both discounts.
            $appliedCartDiscount = $total->getDiscountAmount();
    	    $discountAmount      = $total->getDiscountAmount()+$discountAmount;
	    $label  	         = $total->getDiscountDescription().', '.$label;
    	}    
    	
    	$total->setDiscountDescription($label);
	$total->setDiscountAmount($discountAmount);
	$total->setBaseDiscountAmount($discountAmount);
        $total->setSubtotalWithDiscount($total->getSubtotal() + $discountAmount);
        $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $discountAmount);
        
       if(isset($appliedCartDiscount)) {
	    $total->addTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
	    $total->addBaseTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
	} else {
	    $total->addTotalAmount($this->getCode(), $discountAmount);
	    $total->addBaseTotalAmount($this->getCode(), $discountAmount);
	}
            
        return $this;
    }
 
    /**
     * Add discount total information to address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $total->getDiscountAmount();
        // ONLY return 1 discount. Need to append existing
        //see app/code/Magento/Quote/Model/Quote/Address.php
       
        if ($amount != 0) { 
            $description = $total->getDiscountDescription();
            $result = [
                'code' => $this->getCode(),
                'title' => strlen($description) ? __('%1', $description) : __('Discount'),
                'value' => $amount
            ];
        }
        return $result;
    }
}

