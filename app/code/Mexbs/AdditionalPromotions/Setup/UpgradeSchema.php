<?php
namespace Mexbs\AdditionalPromotions\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $setup->getConnection()->addColumn(
                $installer->getTable('salesrule'),
                'display_popup_on_first_visit',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '6',
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Display Popup On First Customer Visit'
                ]
            );

            $setup->getConnection()->addColumn(
                $installer->getTable('salesrule'),
                'popup_on_first_visit_image',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Popup Image'
                ]
            );

            $setup->getConnection()->addColumn(
                $installer->getTable('quote'),
                'hint_messages',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '2M',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Hint Messages'
                ]
            );

            $setup->getConnection()->addColumn(
                $installer->getTable('salesrule'),
                'display_cart_hints',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '6',
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Display Cart Hints'
                ]
            );

            $setup->getConnection()->addColumn(
                $installer->getTable('salesrule'),
                'actions_hint_label',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '1K',
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Actions Label for Cart Hints'
                ]
            );

            $setup->getConnection()->addColumn(
                $installer->getTable('salesrule'),
                'hide_hints_after_discount_number',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '6',
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Hide Cart Hints after the Discount Number'
                ]
            );

            $setup->getConnection()->addColumn(
                $installer->getTable('salesrule'),
                'display_cart_hints_if_coupon_invalid',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '6',
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Display Cart Hints When Coupon is Invalid'
                ]
            );

            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            /**
             * Create table 'apactionrule_product'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('apactionrule_product'))
                ->addColumn(
                    'apactionrule_product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'AP Action Rule Product Id'
                )
                ->addColumn(
                    'rule_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Rule Id'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Product Id'
                )
                ->addColumn(
                    'group_number',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => true, 'default' => null],
                    'Group Number'
                )
                ->addColumn(
                    'product_has_custom_options',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['default' => 0],
                    'Whether Product has custom options'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'apactionrule_product',
                        ['rule_id', 'product_id', 'group_number'],
                        true
                    ),
                    ['rule_id', 'product_id', 'group_number'],
                    ['type' => 'unique']
                )->addForeignKey(
                    $installer->getFkName('apactionrule_product', 'rule_id', 'salesrule', 'rule_id'),
                    'rule_id',
                    $installer->getTable('salesrule'),
                    'rule_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->addForeignKey(
                    $installer->getFkName('apactionrule_product', 'rule_id', 'catalog_product_entity', 'entity_id'),
                    'rule_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('AP Rule Product');

            $installer->getConnection()->createTable($table);

            $setup->getConnection()->addColumn(
                $installer->getTable('salesrule'),
                'display_promo_block',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '6',
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Display Promo Blocks'
                ]
            );

            $setup->getConnection()->addColumn(
                $installer->getTable('salesrule'),
                'hide_promo_block_if_rule_applied',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '6',
                    'nullable' => false,
                    'default' => 1,
                    'comment' => 'Display Promo Blocks'
                ]
            );
        }
    }
}
