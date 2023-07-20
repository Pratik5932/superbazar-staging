<?php
namespace Magecomp\Paymentfee\Model\Sales\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
class Paymentfee extends AbstractTotal
{
    public function collect(Invoice $invoice)
    {
        $invoice->setMcPaymentfeeAmount(0);
        $invoice->setBaseMcPaymentfeeAmount(0);
        $invoice->setMcPaymentfeeTaxAmount(0);
        $invoice->setBaseMcPaymentfeeTaxAmount(0);

        $fee = $invoice->getOrder()->getMcPaymentfeeAmount();
        $baseFee = $invoice->getOrder()->getBaseMcPaymentfeeAmount();
        $feeTax = $invoice->getOrder()->getMcPaymentfeeTaxAmount();
        $baseFeeTax = $invoice->getOrder()->getBaseMcPaymentfeeTaxAmount();
        $title = __($invoice->getOrder()->getMcPaymentfeeDescription());

        if ($fee != 0) {
            $invoice->setMcPaymentfeeAmount($fee);
            $invoice->setBaseMcPaymentfeeAmount($baseFee);
            $invoice->setMcPaymentfeeTaxAmount($feeTax);
            $invoice->setBaseMcPaymentfeeTaxAmount($baseFeeTax);
            $invoice->setMcPaymentfeeDescription($title);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $fee);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseFee);
        }
        return $this;
    }
}
