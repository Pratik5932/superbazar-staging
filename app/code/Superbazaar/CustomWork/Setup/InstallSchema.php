<?php
namespace Superbazaar\CustomWork\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        
        /**
         * Create table 'hyperlocal_useragent_list'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('hyperlocal_useragent_list'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'agent Id'
            )
            ->addColumn(
                'useragent',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'useragent'
            )
            ->addColumn(
                'zipcode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'zipcode'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Update Time'
            )
            ->setComment('hyperlocal_useragent_list');
        $installer->getConnection()->createTable($table);
		$installer->getConnection()
            ->addColumn(
				$installer->getTable('marketplace_userdata'),
				'bank_details',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => false,
					'length' => '2M',
					'default' => '',
					'comment' => 'Store Views'
				]
            );
        $installer->endSetup();
    }
}
