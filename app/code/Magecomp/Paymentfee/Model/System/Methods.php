<?php

namespace Magecomp\Paymentfee\Model\System;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Config;
class Methods implements \Magento\Framework\Option\ArrayInterface
{
    protected $scopeConfig;
    protected $paymentmodelconfig;

    public function __construct(Config $paymentmodelconfig, ScopeConfigInterface $scopeConfig)
    {
        $this->paymentmodelconfig = $paymentmodelconfig;
        $this->scopeConfig = $scopeConfig;
    }

    public function toOptionArray()
    {
        $payments = $this->paymentmodelconfig->getActiveMethods();
        $methods = array();
        foreach ($payments as $paymentCode => $paymentModel)
        {
            $paymentTitle = $this->scopeConfig->getValue('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode
            );
        }
        return $methods;
    }
}