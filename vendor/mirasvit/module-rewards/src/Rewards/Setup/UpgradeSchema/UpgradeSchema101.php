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

class UpgradeSchema101 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public static function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_rewards_earning_rule_queue')
        )
            ->addColumn(
                'queue_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
                'Queue Id')
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false],
                'Customer Id')
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false],
                'Website Id')
            ->addColumn(
                'rule_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['unsigned' => false, 'nullable' => false],
                'Rule Type')
            ->addColumn(
                'rule_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                512,
                ['unsigned' => false, 'nullable' => false],
                'Rule Code')
            ->addColumn(
                'is_processed',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'default' => 0],
                'Website Id')
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['unsigned' => false, 'nullable' => true],
                'Created At')
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['unsigned' => false, 'nullable' => true],
                'Updated At');
        $installer->getConnection()->createTable($table);
    }
}