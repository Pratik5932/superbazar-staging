<?php

namespace C4B\FreeProduct\Setup;

use C4B\FreeProduct\SalesRule\Action\GiftAction;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Add a field for Gift SKU into SalesRule entity
 *
 * @category   C4B
 * @package    C4B_FreeProduct
 * @author     Dominik Meglič <meglic@code4business.de>
 * @copyright  code4business Software GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $setup->getConnection()->addColumn($setup->getTable('salesrule'), GiftAction::RULE_DATA_X_SKU, [
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Sku of X product'
        ]);
        $setup->getConnection()->addColumn($setup->getTable('salesrule'), GiftAction::RULE_DATA_Y_SKU, [
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Sku of Y product'
        ]);
        $setup->getConnection()->addColumn($setup->getTable('salesrule'), GiftAction::RULE_DATA_DISCOUNT_TYPE, [
            'type' => Table::TYPE_VARCHAR,
            'length' => 255,
            'comment' => 'Discount type'
        ]);

        $setup->endSetup();
    }
}
