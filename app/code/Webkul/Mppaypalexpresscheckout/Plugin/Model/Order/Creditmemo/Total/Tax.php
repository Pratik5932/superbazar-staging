<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mppaypalexpresscheckout\Plugin\Model\Order\Creditmemo\Total;

/**
 * Webkul Mppaypalexpresscheckout Tax Plugin
 */
class Tax
{
    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request)
    {
        $this->request = $request;
    }

    public function afterCollect(
        \Magento\Sales\Model\Order\Creditmemo\Total\Tax $subject,
        $result,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo
    ) {
        if ($this->request->getFullActionName() == "sales_order_creditmemo_new") {
            $order = $creditmemo->getOrder();
            if ($order->getPayment()->getMethod() == "mppaypalexpresscheckout"
                && $creditmemo->getInvoice()
            ) {
                $invoice = $creditmemo->getInvoice();
                $creditmemo->setTaxAmount($invoice->getTaxAmount());
                $creditmemo->setBaseTaxAmount($invoice->getBaseTaxAmount());

                $creditmemo->setGrandTotal($invoice->getGrandTotal());
                $creditmemo->setBaseGrandTotal($invoice->getBaseGrandTotal());
            }
        }
        return $result;
    }
}
