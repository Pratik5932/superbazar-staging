<?php
namespace Vnecoms\Sms\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'ves_sms_message'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_sms_message')
        )->addColumn(
            'message_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Message Id'
        )->addColumn(
            'sid',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            63,
            [],
            'Message id from Message Gateway'
        )->addColumn(
            'gateway',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            31,
            [],
            'Gateway Id'
        )->addColumn(
            'message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            511,
            [],
            'Message content'
        )->addColumn(
            'to_mobile',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            15,
            [],
            'Mobile Number'
        )->addColumn(
            'note',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Note'
        )->addColumn(
            'additional_data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            127,
            ['nullable' => true, 'default' => null],
            'Additional Data'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            [],
            'Status'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->setComment(
            'Message history'
        );
        $installer->getConnection()->createTable($table);
        
        
        
        /**
         * Create table 'ves_sms_customer_mobile'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_sms_customer_mobile')
        )->addColumn(
            'mobile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Mobile Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true,'unsigned' => true],
            'Customer Account Id'
        )->addColumn(
            'mobile',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            15,
            [],
            'Mobile Number'
        )->addColumn(
            'otp',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            15,
            ['nullable' => true, 'default' => null],
            'OTP'
        )->addColumn(
            'otp_created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'OTP Created At'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['default' => 0],
            'Status'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addForeignKey(
            $setup->getFkName('ves_sms_customer_mobile', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Customer Mobile Number Table'
        );
        $installer->getConnection()->createTable($table);
        
        $installer->endSetup();
    }
}
