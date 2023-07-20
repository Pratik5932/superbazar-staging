<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rewards\Setup\UpgradeData;

use Magento\Customer\Model\Customer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;
use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Api\Data\Earning\RuleInterface as EarningRuleInterface;
use Mirasvit\Rewards\Api\Data\Spending\RuleInterface as SpendingRuleInterface;
use Mirasvit\Rewards\Helper\Json as jsonHelper;
use Mirasvit\Rewards\Model\TierFactory;
use Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory as EarningCollectionFactory;
use Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\CollectionFactory as SpendingCollectionFactory;

class UpgradeData1016 implements UpgradeDataInterface
{
    /**
     * @var \Mirasvit\Rewards\Helper\Balance
     */
    protected $balanceHelper;
    /**
     * @var EarningCollectionFactory
     */
    protected $earningCollectionFactory;
    /**
     * @var Config
     */
    protected $eavConfig;
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;
    /**
     * @var jsonHelper
     */
    protected $jsonHelper;
    /**
     * @var mixed
     */
    protected $productMetadata;
    /**
     * @var SpendingCollectionFactory
     */
    protected $spendingCollectionFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var array
     */
    protected $tiers;
    /**
     * @var mixed
     */
    protected $tierCollectionFactory;
    /**
     * @var TierFactory
     */
    protected $tierFactory;

    public function __construct(
        jsonHelper $jsonHelper,
        EarningCollectionFactory $earningCollectionFactory,
        SpendingCollectionFactory $spendingCollectionFactory,
        TierFactory $tierFactory,
        StoreManagerInterface $storeManager,
        EavSetupFactory $eavSetupFactory,
        IndexerRegistry $indexerRegistry,
        Config $eavConfig
    ) {
        $this->jsonHelper      = $jsonHelper;
        $this->tierFactory     = $tierFactory;
        $this->storeManager    = $storeManager;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->eavConfig       = $eavConfig;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->balanceHelper = $objectManager->create('Mirasvit\Rewards\Helper\Balance');
        $this->tierCollectionFactory = $objectManager->create('Mirasvit\Rewards\Model\ResourceModel\Tier\CollectionFactory');
        $this->productMetadata = $objectManager->create('Magento\Framework\App\ProductMetadataInterface');

        $this->earningCollectionFactory = $earningCollectionFactory;
        $this->spendingCollectionFactory = $spendingCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->addCustomerAttr($setup);

        $this->tiers = $this->tierCollectionFactory->create()->orderByPoints();
        if (!$this->tiers->count()) {
            $this->createDefaultTier();
            $this->tiers = $this->tierCollectionFactory->create();
        }

        $this->updateEarningRule($setup);
        $this->updateSpendingRule($setup);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    public function updateEarningRule($setup)
    {
        $collection = $this->earningCollectionFactory->create();
        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        foreach ($collection as $rule) {
            $tiersData = [];
            foreach ($this->tiers as $tier) {
                $tiersData[$tier->getId()] = [
                    EarningRuleInterface::KEY_TIER_KEY_EARNING_STYLE => $rule->getData('earning_style'),
                    EarningRuleInterface::KEY_TIER_KEY_EARN_POINTS => $rule->getData('earn_points'),
                    EarningRuleInterface::KEY_TIER_KEY_MONETARY_STEP => $rule->getData('monetary_step'),
                    EarningRuleInterface::KEY_TIER_KEY_QTY_STEP => $rule->getData('qty_step'),
                    EarningRuleInterface::KEY_TIER_KEY_POINTS_LIMIT => $rule->getData('points_limit'),
                ];
            }
            $table = $setup->getTable('mst_rewards_earning_rule');
            $bind = [EarningRuleInterface::KEY_TIERS_SERIALIZED => $this->jsonHelper->serialize($tiersData)];
            $where = [$rule->getIdFieldName() . ' = ?' => (int)$rule->getId()];
            $setup->getConnection()->update($table, $bind, $where);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    public function updateSpendingRule($setup)
    {
        $collection = $this->spendingCollectionFactory->create();
        /** @var \Mirasvit\Rewards\Model\Spending\Rule $rule */
        foreach ($collection as $rule) {
            $tiersData = [];
            foreach ($this->tiers as $tier) {
                $tiersData[$tier->getId()] = [
                    SpendingRuleInterface::KEY_TIER_KEY_SPENDING_STYLE => $rule->getData('spending_style'),
                    SpendingRuleInterface::KEY_TIER_KEY_SPEND_POINTS => $rule->getData('spend_points'),
                    SpendingRuleInterface::KEY_TIER_KEY_MONETARY_STEP => $rule->getData('monetary_step'),
                    SpendingRuleInterface::KEY_TIER_KEY_SPEND_MIN_POINTS => $rule->getData('spend_min_points'),
                    SpendingRuleInterface::KEY_TIER_KEY_SPEND_MAX_POINTS => $rule->getData('spend_max_points'),
                ];
            }
            $table = $setup->getTable('mst_rewards_spending_rule');
            $bind = [EarningRuleInterface::KEY_TIERS_SERIALIZED => $this->jsonHelper->serialize($tiersData)];
            $where = [$rule->getIdFieldName() . ' = ?' => (int)$rule->getId()];
            $setup->getConnection()->update($table, $bind, $where);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    public function addCustomerAttr(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->removeAttribute(
            Customer::ENTITY,
            TierInterface::CUSTOMER_KEY_TIER_ID
        );
        $eavSetup->addAttribute(
            Customer::ENTITY,
            TierInterface::CUSTOMER_KEY_TIER_ID,
            $this->getCustomerAttributeSettings()
        );
        $subscription = $this->eavConfig->getAttribute(Customer::ENTITY, TierInterface::CUSTOMER_KEY_TIER_ID);
        $subscription->setData(
            'used_in_forms',
            ['adminhtml_customer']
        );
        $subscription->getResource()->save($subscription);
    }

    /**
     * @return array
     */
    private function getCustomerAttributeSettings()
    {
        if (version_compare($this->productMetadata->getVersion(), "2.2.4", ">=")) {
            $settings = [
                'type'       => 'int',
                'label'      => 'Rewards Tier',
                'input'      => 'select',
                'source'     => 'Mirasvit\Rewards\Model\Customer\Entity\Attribute\Source\Tier',
                'global'     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'required'   => true,
                'sort_order' => 210,
                'system'     => false,
                'position'   => 210,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
            ];
        } else {
            $settings = [
                'type'       => 'int',
                'label'      => 'Rewards Tier',
                'input'      => 'select',
                'source'     => 'Mirasvit\Rewards\Model\Customer\Entity\Attribute\Source\Tier',
                'global'     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'required'   => true,
                'sort_order' => 210,
                'system'     => false,
                'position'   => 210,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ];
        }

        return $settings;
    }

    /**
     * @return void
     */
    public function createDefaultTier()
    {
        $websites = $this->storeManager->getWebsites();
        $data = [
            TierInterface::KEY_NAME            => 'Beginner',
            TierInterface::KEY_IS_ACTIVE       => 1,
            TierInterface::KEY_MIN_EARN_POINTS => 0,
            TierInterface::KEY_TEMPLATE_ID     => 0,
            TierInterface::KEY_WEBSITE_IDS     => array_keys($websites),
        ];
        $tier = $this->tierFactory->create();
        $tier->addData($data);
        $tier->getResource()->save($tier);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    public function updateAllCustomers(ModuleDataSetupInterface $setup)
    {
        $attr = $this->eavConfig->getAttribute(Customer::ENTITY, TierInterface::CUSTOMER_KEY_TIER_ID);
        $rowData = [
            'attribute_id' => $attr->getId(),
            'entity_id'    => 0,
            'value'        => $this->tiers->getFirstItem()->getId(),
        ];

        $step = 1000;
        $offset = 0;
        $isProcessing = true;
        while ($isProcessing) {
            $data = [];
            $isProcessing = false;
            $select = $setup->getConnection()->select()->from(
                ['ce' => $setup->getTable('customer_entity')],
                ['entity_id']
            )->limit($step, $offset);
            foreach ($setup->getConnection()->fetchAll($select) as $row) {
                $isProcessing = true;
                $customerBalance = $this->balanceHelper->getBalancePoints($row['entity_id']);
                /** @var \Mirasvit\Rewards\Api\Data\TierInterface $tier */
                foreach ($this->tiers as $tier) {
                    if ($customerBalance >= $tier->getMinEarnPoints()) {
                        $rowData['value'] = $tier->getId();
                    } else {
                        break;
                    }
                }
                $rowData['entity_id'] = $row['entity_id'];
                $data[] = $rowData;
            }

            if ($isProcessing) {
                $setup->getConnection()
                    ->insertMultiple($setup->getTable('customer_entity_int'), $data);
            }
            $offset += $step;
        }
    }
}
