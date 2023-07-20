<?php
namespace Magecomp\Paymentfee\Model\Sales\Total\Pdf;

use Magecomp\Paymentfee\Helper\Tax;
class Paymentfee extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    private $feeTaxHelper;

    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        Tax $feeTaxHelper,
        array $data = []
    ) {
        $this->feeTaxHelper = $feeTaxHelper;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    public function getTotalsForDisplay()
    {
        $fee = $this->getSource()->getMcPaymentfeeAmount();
        $feeTax = $this->getSource()->getMcPaymentfeeTaxAmount();

        $amount = $this->getOrder()->formatPriceTxt($fee);
        $amountInclTax = $this->getOrder()->formatPriceTxt($fee + $feeTax);

        $title = $this->getSource()->getOrder()->getMcPaymentfeeDescription();
        $defaultLabel = __($title);
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        $totals = [];
        if ($this->feeTaxHelper->displayExclTax()) {
            $label = $defaultLabel;
            if ($this->feeTaxHelper->displaySuffix()) {
                $label .= ' ' . __('(Excl. Tax)');
            }
            $totals[] = [
                'amount' => $this->getAmountPrefix() . $amount,
                'label' => $label . ':',
                'font_size' => $fontSize
            ];
        }

        if ($this->feeTaxHelper->displayInclTax()) {
            $label = $defaultLabel;
            if ($this->feeTaxHelper->displaySuffix()) {
                $label .= ' ' . __('(Incl. Tax)');
            }
            $totals[] = [
                'amount' => $this->getAmountPrefix() . $amountInclTax,
                'label' => $label . ':',
                'font_size' => $fontSize
            ];
        }
        return $totals;
    }
}
