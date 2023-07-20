<?php
/**
 * Copyright (c) 2017 Vnecoms Co ltd. All rights reserved.
 */

namespace Vnecoms\Sms\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Customer\Model\Customer;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    
    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    protected $customerSetupFactory;
    
    /**
     * @var \Vnecoms\Sms\Model\ResourceModel\Mobile\CollectionFactory
     */
    protected $mobileCollectionFactory;
    
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;
    
    /**
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     * @param \Vnecoms\Sms\Model\ResourceModel\Mobile\CollectionFactory $mobileCollectionFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
        \Vnecoms\Sms\Model\ResourceModel\Mobile\CollectionFactory $mobileCollectionFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->mobileCollectionFactory = $mobileCollectionFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->customerFactory = $customerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        
        $customerSetup->addAttribute(Customer::ENTITY, 'mobilenumber', [
            'group' => 'General',
            'type' => 'static',
            'label' => 'Mobile Number',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 100,
            'position' => 100,
            'used_in_grid' => true,
            'visible_in_grid' => true,
            'searchable_in_grid' => true,
            'filterable_in_grid' => true,
            'system' => 0,
        ]);
        
        $mobileNumberAttr = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'mobilenumber');
        $mobileNumberAttr->setData(
            'used_in_forms',
            ['adminhtml_customer', 'checkout_register', 'customer_account_create', 'customer_account_edit', 'adminhtml_checkout']
        )->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1);
        
        $mobileNumberAttr->setData('is_used_in_grid',true);
        $mobileNumberAttr->setData('is_visible_in_grid',true);
        $mobileNumberAttr->setData('is_filterable_in_grid',true);
        
        $mobileNumberAttr->save();
        
        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->invalidate();
    }
}
