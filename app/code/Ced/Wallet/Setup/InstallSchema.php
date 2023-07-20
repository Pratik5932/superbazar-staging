<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Wallet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Wallet\Setup;

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
        $table = $installer->getConnection()->newTable(
        		$installer->getTable('wallet_transaction')
        )->addColumn(
        		'id',
        		\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        		null,
        		['identity' => true, 'nullable' => false, 'primary' => true],
        		'ID'
        )->addColumn(
        		'order_id',
        		\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        		255,
        		['nullable' => false],
        		'Order ID'
        )->addColumn(
        		'action',
        		\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        		null,
        		['unsigned' => true, 'nullable' => false, 'default' => '0'],
        		'Action'
        )->addColumn(
        		'customer_id',
        		\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        		255,
        		['nullable' => false],
        		'Customer Id'
        )->addColumn(
        		'created_at',
        		\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        		null,
        		['unsigned' => true, 'nullable' => false, 'default' => '0'],
        		'Created At'
        )->addColumn(
        		'comment',
        		\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        		255,
        		['nullable' => false],
        		'Comment'
        )->setComment(
        		'Wallet Transaction Table'
        );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
	}
}