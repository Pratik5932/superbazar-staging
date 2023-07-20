<?php
namespace Mexbs\AdditionalPromotions\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $setup->getConnection()->addColumn(
            $installer->getTable('salesrule'),
            'action_details_serialized',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'nullable' => true,
                'default' => null,
                'comment' => 'Action Details Serialized'
            ]
        );

        $setup->getConnection()->modifyColumn(
            $installer->getTable('salesrule'),
            'simple_action',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
            ]
        );

        $setup->getConnection()->addColumn(
            $installer->getTable('quote_address'),
            'discount_details',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'nullable' => true,
                'default' => null,
                'comment' => 'Discount Details'
            ]
        );

        $setup->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'discount_details',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'nullable' => true,
                'default' => null,
                'comment' => 'Discount Details'
            ]
        );

        $setup->getConnection()->addColumn(
            $installer->getTable('salesrule'),
            'discount_order_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '32',
                'nullable' => true,
                'default' => null,
                'comment' => 'Order products by'
            ]
        );

        $setup->getConnection()->addColumn(
            $installer->getTable('salesrule'),
            'max_groups_number',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => '10',
                'nullable' => false,
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Maximum number of groups'
            ]
        );

        $setup->getConnection()->addColumn(
            $installer->getTable('salesrule'),
            'max_sets_number',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => '10',
                'nullable' => false,
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Maximum number of sets'
            ]
        );

        $setup->getConnection()->addColumn(
            $installer->getTable('salesrule'),
            'discount_breakdown_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '32',
                'nullable' => false,
                'default' => \Mexbs\AdditionalPromotions\Model\Source\SalesRule\BreakdownType::TYPE_CONFIG,
                'comment' => 'Discount Breakdown Type'
            ]
        );

        $setup->getConnection()->addColumn(
            $installer->getTable('salesrule'),
            'max_discount_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => '10',
                'nullable' => false,
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Maximum Discount Amount'
            ]
        );

        $setup->getConnection()->addColumn(
            $installer->getTable('salesrule'),
            'skip_special_price',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'unsigned' => true,
                'default' => 2,
                'comment' => 'Skip Special Price'
            ]
        );

        $setup->getConnection()->addColumn(
            $installer->getTable('salesrule'),
            'skip_tier_price',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'unsigned' => true,
                'default' => 2,
                'comment' => 'Skip Tier Price'
            ]
        );

        $setup->getConnection()->addColumn(
            $installer->getTable('quote_item'),
            'ap_rule_matches',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'nullable' => true,
                'default' => null,
                'comment' => 'AP rule matches'
            ]
        );

        $setup->getConnection()->addColumn(
            $installer->getTable('quote_item'),
            'ap_price_type_flags',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'comment' => 'AP price type flags'
            ]
        );


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
}