<?php 
namespace Ced\Wallet\Model\Source;
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
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Payment\Model\Config;

class Paymethods implements \Magento\Framework\Option\ArrayInterface
{
    protected $_appConfigScopeConfigInterface;

    protected $_paymentModelConfig;

    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Config $paymentModelConfig
    ) {
    
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_paymentModelConfig = $paymentModelConfig;
    }

    public function toOptionArray()
    {
        $payments = $this->_paymentModelConfig->getActiveMethods();
        $methods = array();
        foreach ($payments as $paymentCode=>$paymentModel)
        {
            $paymentTitle = $this->_appConfigScopeConfigInterface->getValue('payment/'.$paymentCode.'/title');
            if($paymentCode == 'free'){
                continue;
            }elseif($paymentCode == 'wallet'){
                continue;
            }
            else{
                $methods[$paymentCode] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode
                );
            }
        }
        return $methods;
    }
}
