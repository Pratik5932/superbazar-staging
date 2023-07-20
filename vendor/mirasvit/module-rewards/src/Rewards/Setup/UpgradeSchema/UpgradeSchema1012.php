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


namespace Mirasvit\Rewards\Setup\UpgradeSchema;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema1012 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public static function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->getConnection()->dropForeignKey(
            $installer->getTable('mst_rewards_earning_rule_customer_group'),
            $installer->getFkName(
                'mst_rewards_earning_rule_customer_group',
                'customer_group_id',
                'customer_group',
                'customer_group_id'
            )
        );

        $installer->getConnection()->dropForeignKey(
            $installer->getTable('mst_rewards_earning_rule_product'),
            $installer->getFkName(
                'mst_rewards_earning_rule_product',
                'er_customer_group_id',
                'customer_group',
                'customer_group_id'
            )
        );

        $installer->getConnection()->dropForeignKey(
            $installer->getTable('mst_rewards_notification_rule_customer_group'),
            $installer->getFkName(
                'mst_rewards_notification_rule_customer_group',
                'customer_group_id',
                'customer_group',
                'customer_group_id'
            )
        );

        $installer->getConnection()->dropForeignKey(
            $installer->getTable('mst_rewards_spending_rule_customer_group'),
            $installer->getFkName(
                'mst_rewards_spending_rule_customer_group',
                'customer_group_id',
                'customer_group',
                'customer_group_id'
            )
        );
    }
}