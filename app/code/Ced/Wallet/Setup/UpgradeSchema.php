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
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
    	$installer = $setup;
        $installer->startSetup();


        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            
            $installer->getConnection()->addColumn(
                $setup->getTable('wallet_transaction'),
                'amount',
                [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length'    => '12,4',
                'nullable' => false,
                'comment' => 'Amount'
                ]
         );
        }
        if (version_compare($context->getVersion(), '0.0.3') < 0) {
            $installer->getConnection()->addColumn(
                $installer->getTable('wallet_transaction'),
                'transaction_with',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT
            );
        }

         if (version_compare($context->getVersion(), '0.0.4') < 0) {
            $tableName = $installer->getTable('wallet_transaction');
            if ($installer->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();
                $connection->addColumn(
                        $tableName,
                        'is_cashback',
                        [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 5,
                        'nullable' => false,
                        'default' =>'0',
                        'comment' => 'is_cashback'
                        ]
                );
               
                
            }
        }

        if (version_compare($context->getVersion(), '0.0.5') <= 0) {
            $installer = $setup;
            $installer->startSetup();
            $table = $installer->getConnection()->newTable(
                    $installer->getTable('wallet_cashback')
            )->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true,'unsigned' => true,'nullable'  => false,'primary'   => true],
                    'id'
            )
            ->addColumn(
                    'order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                    ), 'order_id'
            )->addColumn(
                'amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'amount'
            )->addColumn(
                    'scheduled_at',\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,255,
                    ['nullable' => false],
                    'Scheduled At'
            )->addColumn(
                    'status', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,10, array(
                    ), 'status'
            )
            ->addColumn(
                    'customer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10,array(
                    ), 'customer_id'
            );
            
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
        
        
         }



          if (version_compare($context->getVersion(), '0.0.6') < 0) {
            $wtableName = $installer->getTable('wallet_transaction');
            if ($installer->getConnection()->isTableExists($wtableName) == true) {
                $connection = $setup->getConnection();
                $connection->addColumn(
                        $wtableName,
                        'expiration_status',
                        [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 5,
                        'nullable' => false,
                        'default' =>'0',
                        'comment' => 'expiration_status'
                        ]
                );
                 $connection->addColumn(
                        $wtableName,
                        'expiration_time',
                        [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        'length' => 255,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'expiration_time'
                        ]
                );
                $connection->addColumn(
                        $wtableName,
                        'used_amount',
                        [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                        'nullable' => false,
                        'comment' => 'used_amount'
                        ]
                );
                
            }
        }

         if (version_compare($context->getVersion(), '0.0.7') <= 0) {
            $installer = $setup;
            $installer->startSetup();
            $table = $installer->getConnection()->newTable(
                    $installer->getTable('wallet_redeem')
            )->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true,'unsigned' => true,'nullable'  => false,'primary'   => true],
                    'id'
            )->addColumn(
                'amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'amount'
            )->addColumn(
                    'created_at',\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,255,
                    ['nullable' => false],
                    'created_at'
            )->addColumn(
                    'status', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,10, array(
                    ), 'status'
            )->addColumn(
                    'customer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10,array(
                    ), 'customer_id'
            )->addColumn(
                    'details', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2555,array(
                    ), 'details'
            )->addColumn(
                    'comment', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2555,array(
                    ), 'comment'
            );
            
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
        
        
         }
         
         if (version_compare($context->getVersion(), '0.0.8') < 0) {
              $installer = $setup;
              $setup->getConnection()->addColumn(
              		$setup->getTable('sales_order'),
              		'wallet_payment',
              		[
              		'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
              		'length'    => '12,4',
              		'nullable' => false,
              		'comment' => 'Wallet Payment'
              		]
              );
            
        }

       if (version_compare($context->getVersion(), '0.0.9') < 0) {
              $installer = $setup;
             $setup->getConnection()->addColumn(
              		$setup->getTable('wallet_transaction'),
              		'is_walletrecharge',
              		[
              		'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
              		'length'    => '2',
              		'default'   => '0',
              		'nullable' => false,
              		'comment' => 'Wallet Recharge'
              		]
              );
        }
       
        
        
 
        $installer->endSetup();
    }
}