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

class UpgradeSchema102 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public static function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_rewards_customer_referral_link')
        )
            ->addColumn(
                'customer_referral_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
                'Customer Referral Id')
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Customer Id')
            ->addColumn(
                'referral_link',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['unsigned' => false, 'nullable' => false],
                'Referral Link')
            ->addIndex(
                $installer->getIdxName('mst_rewards_customer_referral_link', ['customer_id']),
                ['customer_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'mst_rewards_customer_referral_link',
                    'customer_id',
                    'customer_entity',
                    'entity_id'
                ),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);
    }
}