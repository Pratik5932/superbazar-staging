<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Setup;

use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * Init
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Installs DB schema for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
 
        $installer = $setup;
        $installer->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $attributes = [
            'socialsignup_tid' => [
                "type"     => "text",
                "backend"  => "",
                "label"    => "social signup twitter id",
                "input"    => "text",
                "source"   => "",
                "visible"  => false,
                "required" => false,
                "default" => "",
                "frontend" => "",
                "unique"     => false,
                "note"       => ""
            ],
            'socialsignup_ttoken' => [
                "type"     => "text",
                "backend"  => "",
                "label"    => "social signup twitter token",
                "input"    => "text",
                "source"   => "",
                "visible"  => false,
                "required" => false,
                "default" => "",
                "frontend" => "",
                "unique"     => false,
                "note"       => ""
            ],
            'socialsignup_lid' => [
                "type"     => "text",
                "backend"  => "",
                "label"    => "social signup linkedIn id",
                "input"    => "text",
                "source"   => "",
                "visible"  => false,
                "required" => false,
                "default" => "",
                "frontend" => "",
                "unique"     => false,
                "note"       => ""
            ],
            'socialsignup_ltoken' => [
                "type"     => "text",
                "backend"  => "",
                "label"    => "social signup linkedIn token",
                "input"    => "text",
                "source"   => "",
                "visible"  => false,
                "required" => false,
                "default" => "",
                "frontend" => "",
                "unique"     => false,
                "note"       => ""
            ],
            'socialsignup_gid' => [
                "type"     => "text",
                "backend"  => "",
                "label"    => "social signup google id",
                "input"    => "text",
                "source"   => "",
                "visible"  => false,
                "required" => false,
                "default" => "",
                "frontend" => "",
                "unique"     => false,
                "note"       => ""
            ],
            'socialsignup_gtoken' => [
                "type"     => "text",
                "backend"  => "",
                "label"    => "social signup google token",
                "input"    => "text",
                "source"   => "",
                "visible"  => false,
                "required" => false,
                "default" => "",
                "frontend" => "",
                "unique"     => false,
                "note"       => ""
            ],
             'socialsignup_instaid' => [
                "type"     => "text",
                "backend"  => "",
                "label"    => "social signup instagram id",
                "input"    => "text",
                "source"   => "",
                "visible"  => false,
                "required" => false,
                "default" => "",
                "frontend" => "",
                "unique"     => false,
                "note"       => ""
             ],
             'socialsignup_instatoken' => [
                "type"     => "text",
                "backend"  => "",
                "label"    => "social signup instagram token",
                "input"    => "text",
                "source"   => "",
                "visible"  => false,
                "required" => false,
                "default" => "",
                "frontend" => "",
                "unique"     => false,
                "note"       => ""
             ],
        ];

        foreach ($attributes as $code => $options) {
            $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                $code,
                $options
            );
        }

        /**
         * Create table 'socialsignup wk_facebook_customer'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_facebook_customer'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer Id'
            )
            ->addColumn(
                'fb_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Fb Id'
            )
            ->setComment('Social signup facebook customer table');
             $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
