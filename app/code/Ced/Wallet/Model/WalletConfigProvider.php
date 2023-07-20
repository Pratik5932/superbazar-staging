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
namespace Ced\Wallet\Model;


use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;

use Magento\Payment\Helper\Data as PaymentHelper;
use \Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;


class WalletConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
	
	public $_objectManager;
	
    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Config
     */
    protected $config;
    
    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $_storeManager;
    
    protected $formLaybuy;
    protected $helperForm;
    const CODE = 'wallet';
    /**
     * @param ConfigFactory $configFactory
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param PaypalHelper $paypalHelper
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
       ResolverInterface $localeResolver,
       PaymentHelper $paymentHelper,
       ScopeConfig $scopeConfig,
       \Magento\Framework\ObjectManagerInterface $objectManager,
       \Magento\Store\Model\StoreManagerInterface $storeManager,
       UrlInterface $urlBuilder,
       \Magento\Directory\Model\Currency $currency,
       CurrentCustomer $currentCustomer,
       \Magento\Customer\Model\Customer $customerModel,
       \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     ) {
	
    	$this->_storeManager = $storeManager;
        $this->localeResolver = $localeResolver;
        $this->currentCustomer = $currentCustomer;
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->_objectManager=$objectManager;
        $this->_currency = $currency;
        $this->customerModel = $customerModel;
        $this->priceCurrency = $priceCurrency;

    }

    public function getConfig()
    {
       
        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode(); 
        $currency = $this->_currency->load($currencyCode)->getCurrencySymbol(); 
        $walleturl = $this->_storeManager->getStore()->getUrl('wallet/wallet/setwallet');
    	$customerid = $this->currentCustomer->getCustomerId();
    	$customer = $this->customerModel->load($customerid);
    	$amount = $customer->getAmountWallet();
    	
        $store = $this->_storeManager->getStore()->getStoreId(); //get current store id if store id not get passed
        $amount = $this->priceCurrency->convert($amount, $store);
    	
    	$status = false;
        $amount = number_format((float)$amount, 2, '.', '');

    	$config = [
    	'payment' => [
    			'wallet' => [
                   	    'amount' => $amount,
    					'walleturl' => $walleturl,
    					'status' => $status,
                        'currency' =>$currency
    			]
    ]
    	];
    
    	return $config;
    }
   
}
