<?php

namespace Vnecoms\Sms\Setup;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    public function upgrade(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_entity'),
                'mobilenumber',
                'VARCHAR(255) NULL AFTER gender'
            );
        }elseif (version_compare($context->getVersion(), '2.0.5') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('ves_sms_message'),
                'note',
                'text NULL AFTER to_mobile'
            );
        }
    }
}
