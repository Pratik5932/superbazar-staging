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



namespace Mirasvit\Rewards\Model\Total\Invoice;

use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

class Discount extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    private $config;

    private $rewardsPurchase;

    public function __construct(
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Model\Config $config,
        array $data = []
    ) {
        parent::__construct($data);

        $this->config          = $config;
        $this->rewardsPurchase = $rewardsPurchase;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        parent::collect($invoice);

        if ($this->config->getAdvancedSpendingCalculationMethod() == Method::METHOD_ITEMS) {
            return $this;
        }

        $order    = $invoice->getOrder();
        $purchase = $this->rewardsPurchase->getByOrder($order);

        if (!$purchase) {
            return $this;
        }

        $invoice->setBaseTotalAmount($this->getCode(), -$purchase->getBaseSpendAmount());
        $invoice->setTotalAmount($this->getCode(), -$purchase->getSpendAmount());
        $invoice->setBaseRewardsDiscount($purchase->getBaseSpendAmount());
        $invoice->setRewardsDiscount($purchase->getSpendAmount());

        if ($invoice->getBaseGrandTotal()) {
            $discount = $purchase->getBaseSpendAmount();
            if ($discount > $invoice->getBaseGrandTotal()) {
                $discount = $invoice->getBaseGrandTotal();
            }
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $discount);
        }
        if ($invoice->getGrandTotal()) {
            $discount = $purchase->getSpendAmount();
            if ($discount > $invoice->getGrandTotal()) {
                $discount = $invoice->getGrandTotal();
            }
            $invoice->setGrandTotal($invoice->getGrandTotal() - $discount);
        }

        return $this;
    }
}
