<?php

namespace Cminds\AdvancedPermissions\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Cminds AdvancedPermissions upgrade schema.
 *
 * @category Cminds
 * @package  Cminds_AdvancedPermissions
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.8', '<')) {
           $this->addAdminUserAttributes($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param $installer
     */
    private function addAdminUserAttributes($installer)
    {
        $tableAdmins = $installer->getTable('admin_user');

        $installer->getConnection()->addColumn(
            $tableAdmins,
            'post_code',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'default' => "",
                'comment' => 'Post Code'
            ]
        );
        $installer->getConnection()->addColumn(
            $tableAdmins,
            'post_codes',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'default' => "",
                'comment' => 'Post Codes'
            ]
        );
    }
}
