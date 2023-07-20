<?php
namespace Magecomp\Paymentfee\Model\Sales\Total\Creditmemo;

use Magecomp\Paymentfee\Helper\Data;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
class Paymentfee extends AbstractTotal
{
    private $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function collect(Creditmemo $creditmemo)
    {
        $creditmemo->setMcPaymentfeeAmount(0);
        $creditmemo->setBaseMcPaymentfeeAmount(0);
        $creditmemo->setBaseMcPaymentfeeTaxAmount(0);
        $creditmemo->setBasePaymentFeeTaxAmount(0);

        $fee = $creditmemo->getOrder()->getMcPaymentfeeAmount();
        $baseFee = $creditmemo->getOrder()->getMcPaymentfeeAmount();
        $feeTax = $creditmemo->getOrder()->getMcPaymentfeeTaxAmount();
        $baseFeeTax = $creditmemo->getOrder()->getBaseMcPaymentfeeTaxAmount();
        $title = $creditmemo->getOrder()->getMcPaymentfeeDescription();
        $storeId = $creditmemo->getOrder()->getStoreId();
        if ($fee != 0 && $this->helper->canRefundFees($storeId)) {
            $creditmemo->setMcPaymentfeeAmount($fee);
            $creditmemo->setBaseMcPaymentfeeAmount($baseFee);
            $creditmemo->setMcPaymentfeeTaxAmount($feeTax);
            $creditmemo->setBaseMcPaymentfeeTaxAmount($baseFeeTax);
            $creditmemo->setMcPaymentfeeDescription($title);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $fee);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFee);
        }
     return $this;
    }
}
