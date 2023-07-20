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
namespace Webkul\MpStripe\Plugin\Backend\Block\Widget\Button;

class Toolbar
{
    /**
     * \Webkul\Marketplace\Model\Saleslist
     *
     * @var $salesList
     */
    private $salesList;

    /**
     * @param \Webkul\Marketplace\Model\SaleslistFactory $salesList
     */
    public function __construct(
        \Webkul\Marketplace\Model\SaleslistFactory $salesList
    ) {
    
        $this->salesList = $salesList;
    }
    /**
     * Plugin to hide credit memo button when no quantity is left to refund
     *
     * @param \Magento\Backend\Block\Widget\Button\Toolbar $toolbar
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     *
     */
    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\Toolbar $toolbar,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        $quantity = 0;
        if ($context->getInvoice()) {
            $orderItems = $this->salesList->create()->getCollection()
                        ->addFieldToFilter("order_id", $context->getInvoice()->getOrderId())
                        ->getData();

            foreach ($orderItems as $items) {
                $quantity += $items["magequantity"];
            }

            if ($quantity<1) {
                $buttonList->remove("capture");
            }
        }
    }
}
