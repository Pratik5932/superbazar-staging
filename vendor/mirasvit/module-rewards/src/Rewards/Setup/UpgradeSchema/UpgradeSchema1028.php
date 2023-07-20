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

class UpgradeSchema1028 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public static function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'order_earn',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'Earned points by order',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'order_earn',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'Earned points by order',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'order_earn_cancel',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'Canceled points',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'order_refund',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'Refunded',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'order_spend',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'Spent',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'order_spend_restore',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'spend restore',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'expired_points',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'expired points',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'admin_transaction',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'added by admin',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'facebook_like',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'like',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'pinterest_pin',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'pin',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'twitter_tweet',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'tweet',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'create_rma',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'for RMA creation',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_points_aggregated_hour'),
            'signup',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'signup',
            ]
        );
    }
}