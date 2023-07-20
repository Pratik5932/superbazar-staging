<?php
namespace Magecomp\Paymentfee\Block\Sales\Order;

use Magecomp\Paymentfee\Helper\Tax as TaxHelper;
use Magento\Framework\View\Element\Template;
class Paymentfee extends Template
{

    private $taxHelper;

    public function __construct(
        Template\Context $context,
        TaxHelper $taxHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->taxHelper = $taxHelper;
    }


    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();

        if ($source->getMcPaymentfeeAmount() == 0) {
            return $this;
        }

        $feeExclTax = $source->getMcPaymentfeeAmount();
        $feeInclTax = $feeExclTax + $source->getMcPaymentfeeTaxAmount();
        $title = __($source->getMcPaymentfeeDescription());


        $feeExclTaxTotal = [
            'code' => 'payment_fee',
            'strong' => false,
            'value' => $feeExclTax,
            'label' => $title,
        ];

        $feeInclTaxTotal = [
            'code' => 'payment_fee_incl_tax',
            'strong' => false,
            'value' => $feeInclTax,
            'label' => $title,
        ];

        if ($this->taxHelper->displayExclTax()
            && $this->taxHelper->displayInclTax()
        ) {
            $feeExclTaxTotal['label'] .= ' ' . __('Excl. Tax');
            $feeInclTaxTotal['label'] .= ' ' . __('Incl. Tax');
        }

        if ($this->taxHelper->displayExclTax()) {
            $parent->addTotal(
                new \Magento\Framework\DataObject($feeExclTaxTotal),
                'payment_fee'
            );
        }

        if ($this->taxHelper->displayInclTax()) {
            $parent->addTotal(
                new \Magento\Framework\DataObject($feeInclTaxTotal),
                'payment_fee_incl_tax'
            );
        }

        return $this;
    }
}
