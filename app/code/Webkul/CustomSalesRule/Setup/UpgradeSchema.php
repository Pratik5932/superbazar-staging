<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomSalesRule
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomSalesRule\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $setup->getConnection()->addColumn(
            $setup->getTable('salesrule'),
            'for_website',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => 11,
                'unsigned' => true,
                'nullable' => false,
                'comment' =>'For website'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('salesrule'),
            'for_app',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => 11,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'For application'
            ]
        );

        $setup->endSetup();
    }
}
