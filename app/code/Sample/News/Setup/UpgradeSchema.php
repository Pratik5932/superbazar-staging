<?php

namespace Sample\News\Setup;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    public function upgrade(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.2') > 0) {
            $setup->getConnection()->addColumn( $setup->getTable('sample_news_author'), 'postcodes', 'TEXT' );
        }elseif (version_compare($context->getVersion(), '2.0.5') < 0) {
/*            $setup->getConnection()->addColumn(
                $setup->getTable('ves_sms_message'),
                'note',
                'text NULL AFTER to_mobile'
            );*/
        }
    }
}
