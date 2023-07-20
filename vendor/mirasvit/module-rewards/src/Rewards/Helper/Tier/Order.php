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



namespace Mirasvit\Rewards\Helper\Tier;

use Magento\Store\Model\Website;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

class Order
{
    /**
     * @var \Mirasvit\Rewards\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    public function __construct(
        \Mirasvit\Rewards\Model\Config            $rewardsConfig,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->rewardsConfig = $rewardsConfig;
        $this->resource      = $resource;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param int                              $days
     *
     * @return float
     */
    public function getSumForLastDays($customer, $days)
    {
        $customerId = $customer->getId();
        $website    = $customer->getStore()->getWebsite();
        $fields     = $this->getFields($website);

        $resource     = $this->resource;
        $table        = $resource->getTableName('sales_order');
        $rewardsTable = $resource->getTableName('mst_rewards_purchase');

        $selectedStatuses = $this->rewardsConfig->getTierCalcForOrderInStatuses($website);
        $statuses         = [];

        foreach ($selectedStatuses as $status) {
            $statuses[] = "'" . $status . "'";
        }

        $sql = "SELECT SUM(" . implode('+', $fields) . ") " .
            "FROM $table " .
            "WHERE customer_id = ? AND status IN (" . implode(',', $statuses) . ")";

        $orderIds = "SELECT entity_id FROM $table " .
            "WHERE customer_id = ? AND status IN (" . implode(',', $statuses) . ")";

        $params = [
            (int)$customerId,
        ];

        if ($days) {
            $sql      .= ' AND DATEDIFF(CURDATE(), created_at) <= ?';
            $orderIds .= ' AND DATEDIFF(CURDATE(), created_at) <= ?';
            $params[] = (int)$days;
        }

        $entireAmount = (float)$resource->getConnection()->fetchOne($sql, $params);
        $mstDiscount  = 0;

        if ($this->rewardsConfig->getAdvancedSpendingCalculationMethod() != Method::METHOD_ITEMS &&
            $this->rewardsConfig->getTierCalcForOrderInclDiscount($website)) {

            $rewardsSalesDiscountSql = "SELECT SUM(spend_amount) " .
                "FROM $rewardsTable " .
                "WHERE order_id IN (" . $orderIds . ")";

            $mstDiscount = (float)$resource->getConnection()->fetchOne($rewardsSalesDiscountSql, $params);
        }

        $sumForLastDays = $entireAmount + $mstDiscount;

        return $sumForLastDays;
    }

    /**
     * @param Website|bool $website
     *
     * @return array
     */
    private function getFields($website)
    {
        $selectedStatuses = $this->rewardsConfig->getTierCalcForOrderInStatuses($website);

        if (in_array('processing', $selectedStatuses)) {
            $fields = ['base_subtotal'];
            if ($this->rewardsConfig->getTierCalcForOrderInclDiscount($website)) {
                $fields[] = 'base_discount_amount';
            }
            if ($this->rewardsConfig->getTierCalcForOrderInclShipping($website)) {
                $fields[] = 'base_shipping_amount';
            }
            if ($this->rewardsConfig->getTierCalcForOrderInclTax($website)) {
                $fields[] = 'base_tax_amount';
            }
        } else {
            $fields = ['base_subtotal_invoiced'];
            if ($this->rewardsConfig->getTierCalcForOrderInclDiscount($website)) {
                $fields[] = 'base_discount_invoiced';
            }
            if ($this->rewardsConfig->getTierCalcForOrderInclShipping($website)) {
                $fields[] = 'base_shipping_invoiced';
            }
            if ($this->rewardsConfig->getTierCalcForOrderInclTax($website)) {
                $fields[] = 'base_tax_invoiced';
            }
        }

        return $fields;
    }
}
