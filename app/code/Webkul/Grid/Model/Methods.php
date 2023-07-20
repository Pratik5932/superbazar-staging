<?php
/**
* Webkul_Grid Status Options Model.
* @category    Webkul
* @author      Webkul Software Private Limited
*/
namespace Webkul\Grid\Model;

use Magento\Framework\Data\OptionSourceInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Payment\Model\Config;
class Methods implements OptionSourceInterface
{
    /**
    * Get Grid row status type labels array.
    * @return array
    */
    protected $_appConfigScopeConfigInterface;
    /**
    * @var Config
    */

    protected $_paymentModelConfig;
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Config $paymentModelConfig,
        array $data = []
    ) {
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_paymentModelConfig = $paymentModelConfig;
    }
    public function getOptionArray()
    {

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
        $payments = $this->_paymentModelConfig->getActiveMethods();
        $methods = array();
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->_appConfigScopeConfigInterface
            ->getValue('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode
            );
        }

       // $options = ['1' => __('Enabled'),'0' => __('Disabled')];
        return $methods;
    }

    /**
    * Get Grid row status labels array with empty value for option element.
    *
    * @return array
    */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }

    /**
    * Get Grid row type array for option element.
    * @return array
    */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
    * {@inheritdoc}
    */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
