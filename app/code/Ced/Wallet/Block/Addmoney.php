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
namespace Ced\Wallet\Block;
use Magento\Customer\Model\Session;

class Addmoney extends \Magento\Framework\View\Element\Template
{
    /** @var Session */
    protected $session;

    public $_objectManager;
    protected $_gridFactory;
    
    /**
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
    	\Ced\Wallet\Model\TransactionFactory $gridFactory,
    	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
    	array $data = []
    ) {
    	$this->_gridFactory = $gridFactory;
        parent::__construct($context, $data);
        $this->session = $customerSession;
        $this->_objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->pricingHelper = $pricingHelper;
    }
    
    /*
     * @note: check if otp is required for transaction
     * */
    public function otpAllowed(){
        return $this->_objectManager->create(\Ced\Wallet\Helper\GenerateOTP::class)->otpEnabled();
    }
    /*
     * get amount in customer wallet
    * @return float
    */
      public function getWalletAmount(){
    	$customerId = $this->session->getId();
    	$customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
    	$amount = $customer->getAmountWallet();
    	return $amount;
    }
    
    public function getMinimumAmount(){
        $minAmount = $this->scopeConfig->getValue('ced_wallet/active/min_amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $minAmount;
    }
    
    public function getFromattedPrice($amount){
	    $formattedPrice = $this->pricingHelper->currency($amount, true, false);
	    return $formattedPrice;
    }
}
