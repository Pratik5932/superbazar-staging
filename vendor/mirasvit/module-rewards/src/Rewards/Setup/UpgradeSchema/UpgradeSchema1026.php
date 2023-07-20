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

class UpgradeSchema1026 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public static function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $tableName = 'mst_rewards_refund';
        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )
        ->addColumn(
            'refund_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Refund Id')
        ->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Order Id')
        ->addColumn(
            'invoice_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Invoice Id')
        ->addColumn(
            'creditmemo_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Creditmemo Id')
        ->addColumn(
            'refunded_points',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Refunded amount of points')
        ->addColumn(
            'base_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,2',
            ['unsigned' => true, 'nullable' => false],
            'Base refunded amount')
        ->addColumn(
            'refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,2',
            ['unsigned' => true, 'nullable' => false],
            'Refunded amount')
        ->addIndex(
            $installer->getIdxName($tableName, ['order_id']),
            ['order_id']
        )
        ->addIndex(
            $installer->getIdxName($tableName, ['invoice_id']),
            ['invoice_id']
        )
        ->addIndex(
            $installer->getIdxName($tableName, ['creditmemo_id']),
            ['creditmemo_id']
        );
        $installer->getConnection()->createTable($table);
    }
}