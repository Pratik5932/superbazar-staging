<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Model\Order\Creditmemo\Total;

class Shipping extends \Magento\Sales\Model\Order\Creditmemo\Total\Shipping
{

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $reflshipping = new \ReflectionClass(\Magento\Sales\Model\Order\Creditmemo\Total\Shipping::class);
        $isSuppliedShippingAmountInclTaxMethod = $reflshipping->getMethod('isSuppliedShippingAmountInclTax');
        $isSuppliedShippingAmountInclTaxMethod->setAccessible(true);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectOfConfigurableClass = $objectManager
        ->create(\Magento\Sales\Model\Order\Creditmemo\Total\Shipping::class);

        $helper = $objectManager->create(\Webkul\MpStripe\Helper\Data::class);
        $requestData = $helper->getRequestData();
        if (isset($requestData['invoice_id'])) {
            $invoiceData = $helper->checkInvoiceHaveShipping($requestData['invoice_id']);
        } else {
            $invoiceData['shipping_amount'] = 0;
        }
        $order = $creditmemo->getOrder();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodTitle = $method->getTitle();
        // amounts without tax
        $orderShippingAmount = $order->getShippingAmount();
        $orderBaseShippingAmount = $order->getBaseShippingAmount();
        $allowedAmount = $orderShippingAmount - $order->getShippingRefunded();
        $baseAllowedAmount = $orderBaseShippingAmount - $order->getBaseShippingRefunded();

        // amounts including tax
        $orderShippingInclTax = $order->getShippingInclTax();
        $orderBaseShippingInclTax = $order->getBaseShippingInclTax();
        $allowedTaxAmount = $order->getShippingTaxAmount() - $order->getShippingTaxRefunded();
        $baseAllowedTaxAmount = $order->getBaseShippingTaxAmount() - $order->getBaseShippingTaxRefunded();
        $allowedAmountInclTax = $allowedAmount + $allowedTaxAmount;
        $baseAllowedAmountInclTax = $baseAllowedAmount + $baseAllowedTaxAmount;

        // for the credit memo
        $shippingAmount = $baseShippingAmount = $shippingInclTax = $baseShippingInclTax = 0;

        // Check if the desired shipping amount to refund was specified (from invoice or another source).
        if ($creditmemo->hasBaseShippingAmount()) {
            // For the conditional logic, we will either use amounts that always include tax -OR- never include tax.
            // The logic uses the 'base' currency to be consistent with what the user (admin) provided as input.
            $useAmountsWithTax = $isSuppliedShippingAmountInclTaxMethod->invoke($objectOfConfigurableClass, $order);

            // Since the user (admin) supplied 'desiredAmount' it already has tax -OR- does not include tax
            $desiredAmount = $this->priceCurrency->round($creditmemo->getBaseShippingAmount());

            if ($methodTitle == 'MpStripe') {
                $maxAllowedAmount = $invoiceData['shipping_amount'];
            } else {
                $maxAllowedAmount = ($useAmountsWithTax ? $baseAllowedAmountInclTax : $baseAllowedAmount);
            }
            $maxAllowedAmount = ($useAmountsWithTax ? $baseAllowedAmountInclTax : $baseAllowedAmount);
            $originalTotalAmount = ($useAmountsWithTax ? $orderBaseShippingInclTax : $orderBaseShippingAmount);

            // Note: ($x < $y + 0.0001) means ($x <= $y) for floats
            if ($desiredAmount < $this->priceCurrency->round($maxAllowedAmount) + 0.0001) {
                // since the admin is returning less than the allowed amount, compute the ratio being returned
                $ratio = 0;
                if ($originalTotalAmount > 0) {
                    $ratio = $desiredAmount / $originalTotalAmount;
                }
                // capture amounts without tax
                // Note: ($x > $y - 0.0001) means ($x >= $y) for floats
                if ($desiredAmount > $maxAllowedAmount - 0.0001) {
                    $shippingAmount = $allowedAmount;
                    $baseShippingAmount = $baseAllowedAmount;
                } else {
                    $shippingAmount = $this->priceCurrency->round($orderShippingAmount * $ratio);
                    $baseShippingAmount = $this->priceCurrency->round($orderBaseShippingAmount * $ratio);
                }
                $shippingInclTax = $this->priceCurrency->round($orderShippingInclTax * $ratio);
                $baseShippingInclTax = $this->priceCurrency->round($orderBaseShippingInclTax * $ratio);
            } else {
                $maxAllowedAmount = $order->getBaseCurrency()->format($maxAllowedAmount, null, false);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Maximum shipping amount allowed to refund is: %1', $maxAllowedAmount)
                );
            }
        } else {
            $shippingAmount = $allowedAmount;
            $baseShippingAmount = $baseAllowedAmount;
            $shippingInclTax = $this->priceCurrency->round($allowedAmountInclTax);
            $baseShippingInclTax = $this->priceCurrency->round($baseAllowedAmountInclTax);
        }

        $creditmemo->setShippingAmount($shippingAmount);
        $creditmemo->setBaseShippingAmount($baseShippingAmount);
        $creditmemo->setShippingInclTax($shippingInclTax);
        $creditmemo->setBaseShippingInclTax($baseShippingInclTax);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $shippingAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseShippingAmount);
        return $this;
    }
}
