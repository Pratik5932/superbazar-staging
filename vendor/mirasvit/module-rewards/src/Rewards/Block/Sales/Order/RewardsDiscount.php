<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rewards\Block\Sales\Order;

use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

class RewardsDiscount extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Rewards\Model\Config
     */
    private $config;
    /**
     * @var \Mirasvit\Rewards\Helper\Purchase
     */
    private $purchaseHelper;

    public function __construct(
        \Mirasvit\Rewards\Helper\Purchase $purchaseHelper,
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->purchaseHelper = $purchaseHelper;
        $this->config         = $config;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Rewards\Block\Sales\Order\RewardsDiscount
     */
    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Adminhtml\Order\Invoice\Totals $parent */
        $parent = $this->getParentBlock();
        $order = $parent->getOrder();
        if (!$order || ! $order->getId()) {
            return $this;
        }
        $purchase = $this->purchaseHelper->getByOrder($order);

        if ($this->config->getAdvancedSpendingCalculationMethod() == Method::METHOD_TOTALS
        && $purchase && $purchase->getSpendAmount() > 0) {
            // Add our total information to the set of other totals
            $total = new \Magento\Framework\DataObject(
                [
                    'code'       => $this->getNameInLayout(),
                    'label'      => $this->config->getGeneralPointUnitName($order->getStore()->getId()),
                    'value'      => -$purchase->getSpendAmount(),
                    'base_value' => -$purchase->getBaseSpendAmount()
                ]
            );
            if ($this->getBeforeCondition()) {
                $parent->addTotalBefore($total, $this->getBeforeCondition());
            } else {
                $parent->addTotal($total, $this->getAfterCondition());
            }
        }
        return $this;
    }
}
