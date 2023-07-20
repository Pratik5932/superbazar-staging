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

class UpgradeSchema1027 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public static function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rewards_earning_rule'),
            'active_from',
            'active_from',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                'unsigned' => false,
                'nullable' => true,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rewards_earning_rule'),
            'active_to',
            'active_to',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                'unsigned' => false,
                'nullable' => true,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rewards_notification_rule'),
            'active_from',
            'active_from',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                'unsigned' => false,
                'nullable' => true,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rewards_notification_rule'),
            'active_to',
            'active_to',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                'unsigned' => false,
                'nullable' => true,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rewards_spending_rule'),
            'active_from',
            'active_from',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                'unsigned' => false,
                'nullable' => true,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rewards_spending_rule'),
            'active_to',
            'active_to',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                'unsigned' => false,
                'nullable' => true,
            ]
        );
    }
}