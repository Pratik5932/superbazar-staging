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

class UpgradeSchema1016 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public static function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_earning_rule'),
            'tiers_serialized',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'default'  => null,
                'length'   => '4K',
                'comment'  => 'Tier Settings',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_spending_rule'),
            'tiers_serialized',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'default'  => null,
                'length'   => '4K',
                'comment'  => 'Tier Settings',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rewards_transaction'),
            'tier_id',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'default'  => null,
                'comment'  => 'Tier ID',
            ]
        );
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_rewards_tier')
        )
            ->addColumn(
                'tier_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
                'Tier ID')
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2k',
                ['unsigned' => false, 'nullable' => false],
                'Name')
            ->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2k',
                ['unsigned' => false, 'nullable' => true],
                'Description')
            ->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => 1],
                'Is Active')
            ->addColumn(
                'min_earn_points',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => 0],
                'Minimum points number to reach the tier')
            ->addColumn(
                'template_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2k',
                ['unsigned' => true, 'nullable' => true],
                'Email Template ID');
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_rewards_tier_website')
        )
            ->addColumn(
                'tier_website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
                'ID')
            ->addColumn(
                'tier_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                5,
                ['unsigned' => true, 'nullable' => false],
                'Tier ID')
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'WEbsite ID')
            ->addIndex(
                $installer->getIdxName('mst_rewards_tier_website', ['tier_id']),
                ['tier_id']
            )
            ->addIndex(
                $installer->getIdxName('mst_rewards_tier_website', ['website_id']),
                ['website_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'mst_rewards_tier_website',
                    'website_id',
                    'store_website',
                    'website_id'
                ),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'mst_rewards_tier_website',
                    'earning_rule_id',
                    'mst_rewards_tier',
                    'tier_id'
                ),
                'tier_id',
                $installer->getTable('mst_rewards_tier'),
                'tier_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);
    }
}