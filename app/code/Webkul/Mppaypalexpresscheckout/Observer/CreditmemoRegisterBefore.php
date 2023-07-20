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

namespace Webkul\Mppaypalexpresscheckout\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul Mppaypalexpresscheckout CreditmemoRegisterBefore Observer Model.
 */
class CreditmemoRegisterBefore implements ObserverInterface
{
    /**
     * @param \Webkul\Mppaypalexpresscheckout\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Webkul\Mppaypalexpresscheckout\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * adminhtml_sales_order_creditmemo_register_before event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $creditmemo = $observer->getEvent()->getCreditmemo();

            if ($this->request->getFullActionName() == "sales_order_creditmemo_save") {
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
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Observer_CreditmemoRegisterBefore execute : ".$e->getMessage());
        }
        return $this;
    }
}
