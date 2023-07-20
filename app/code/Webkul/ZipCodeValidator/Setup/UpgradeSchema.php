<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ZipCodeValidator\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->addForeignKeys($setup);
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addForeignKeys(SchemaSetupInterface $setup)
    {
        /**
         * Add foreign keys for Region Id
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'zipcodevalidator_zipcode',
                'region_id',
                'zipcodevalidator_region',
                'id'
            ),
            $setup->getTable('zipcodevalidator_zipcode'),
            'region_id',
            $setup->getTable('zipcodevalidator_region'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }
}
