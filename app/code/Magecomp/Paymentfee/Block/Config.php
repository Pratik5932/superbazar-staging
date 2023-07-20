<?php
namespace Magecomp\Paymentfee\Block;
use Magento\Framework\View\Element\Template;
use Magecomp\Paymentfee\Helper\Data as DataHelper;
use Magecomp\Paymentfee\Helper\Tax as TaxHelper;

class Config extends Template
{
    private $taxHelper;
    private $helper;

    public function __construct(
        Template\Context $context,
        TaxHelper $taxHelper,
        DataHelper $helper,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->taxHelper = $taxHelper;
        $this->helper = $helper;
    }

    public function getConfigJson()
    {
          $displayExclTax = $this->taxHelper->displayExclTax();
          $displayInclTax = $this->taxHelper->displayInclTax();
     $config = [
            'isEnabled' => $this->helper->isEnabled(),
            'isTaxEnabled' => $this->taxHelper->isTaxEnabled(),
            'displayInclTax' => $this->taxHelper->displayInclTax(),
            'displayExclTax' => $this->taxHelper->displayExclTax(),
            'displayBoth' => ($displayExclTax && $displayInclTax),
            'exclTaxPostfix' => __('Excl. Tax'),
            'inclTaxPostfix' => __('Incl. Tax'),
            'applyMethodUrl' => $this->getApplyMethodUrl(),
		];
        return json_encode($config);
    }

    public function getApplyMethodUrl()
    {
        return $this->_urlBuilder->getUrl('magecomp_paymentfee/checkout/applyPaymentMethod');
    }
}
