<?php
namespace Magecomp\Paymentfee\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
class Tax extends AbstractHelper
{
    const XML_PATH_TAX_ENABLED = 'magecomp_paymentfee/tax/enable';
    const XML_PATH_TAX_CLASS   = 'magecomp_paymentfee/tax/tax_class';
    const XML_PATH_TAX_DISPLAY = 'magecomp_paymentfee/tax/display';

    public function isTaxEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_TAX_ENABLED, 'store');
    }

    public function getTaxClassId()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_TAX_CLASS, 'store');
    }

    public function getTaxDisplay()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_TAX_DISPLAY, 'store');
    }

    public function displayInclTax()
    {
        return in_array($this->getTaxDisplay(), [2,3]);
    }

    public function displayExclTax()
    {
        return in_array($this->getTaxDisplay(), [1,3]);
    }

    public function displaySuffix()
    {
        return ($this->getTaxDisplay() == 3);
    }
}
