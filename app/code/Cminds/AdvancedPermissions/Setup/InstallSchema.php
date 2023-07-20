<?php

namespace Cminds\AdvancedPermissions\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // create main table
        $table = $setup->getConnection()
            ->newTable($setup->getTable('cminds_advancedpermissions_role'))
            ->addColumn(
                'role_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Role Id'
            )
            ->addColumn(
                'scope_limit',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                    'default' => false
                ],
                'Scope limit'
            )
            ->addColumn(
                'category_limit',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                    'default' => false
                ],
                'Categories limit'
            )
            ->addColumn(
                'product_limit',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                    'default' => false
                ],
                'Products limit'
            )
//            ->addIndex(
//                $setup->getIdxName(
//                    'cminds_stocknotification_request',
//                    ['role_id'],
//                    AdapterInterface::INDEX_TYPE_UNIQUE
//                ),
//                ['role_id'],
//                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
//            )
            ->addForeignKey(
                $setup->getFkName(
                    'cminds_advancedpermissions_role',
                    'role_id',
                    'authorization_role',
                    'role_id'
                ),
                'role_id',
                $setup->getTable('authorization_role'),
                'role_id',
                Table::ACTION_CASCADE
            )
            ->setComment("Cminds AdvancedPermissions main table");

        $setup->getConnection()->createTable($table);

        // create role scope table
        $table = $setup->getConnection()
            ->newTable($setup->getTable('cminds_advancedpermissions_role_scope'))
            ->addColumn(
                'role_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Role id'
            )
            ->addColumn(
                'access_level',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true,
                    'default' => null
                ],
                'Access level'
            )
            ->addColumn(
                'reference_value',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default' => null
                ],
                'Reference value, website ids or store view ids'
            )
            ->addForeignKey(
                $setup->getFkName(
                    'cminds_advancedpermissions_role_scope',
                    'role_id',
                    'authorization_role',
                    'role_id'
                ),
                'role_id',
                $setup->getTable('authorization_role'),
                'role_id',
                Table::ACTION_CASCADE
            )
            ->setComment("Cminds AdvancedPermissions role scope table");

        $setup->getConnection()->createTable($table);

        // create role category table
        $table = $setup->getConnection()
            ->newTable($setup->getTable('cminds_advancedpermissions_role_category'))
            ->addColumn(
                'role_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Role id'
            )
            ->addColumn(
                'reference_value',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default' => null
                ],
                'Reference value, category ids'
            )
            ->addForeignKey(
                $setup->getFkName(
                    'cminds_advancedpermissions_role_category',
                    'role_id',
                    'authorization_role',
                    'role_id'
                ),
                'role_id',
                $setup->getTable('authorization_role'),
                'role_id',
                Table::ACTION_CASCADE
            )
            ->setComment("Cminds AdvancedPermissions role category table");

        $setup->getConnection()->createTable($table);

        // create role product table
        $table = $setup->getConnection()
            ->newTable($setup->getTable('cminds_advancedpermissions_role_product'))
            ->addColumn(
                'role_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => false,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Role id'
            )
            ->addColumn(
                'access_level',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true,
                    'default' => null
                ],
                'Access level'
            )
            ->addColumn(
                'reference_value',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default' => null
                ],
                'Reference value, product ids'
            )
            ->addForeignKey(
                $setup->getFkName(
                    'cminds_advancedpermissions_role_product',
                    'role_id',
                    'authorization_role',
                    'role_id'
                ),
                'role_id',
                $setup->getTable('authorization_role'),
                'role_id',
                Table::ACTION_CASCADE
            )
            ->setComment("Cminds AdvancedPermissions role product table");

        $setup->getConnection()->createTable($table);

        // create product user id column

        $table = $setup->getTable('catalog_product_entity');
        $setup->getConnection()->addColumn(
            $table,
            'owner_user_id',
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Creator user id',
                'default' => null,
            ]
        );

        $setup->endSetup();
    }
}
