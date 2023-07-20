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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Mirasvit\Rewards\Api\Data\Earning\RuleInterface as EarningRuleInterface;
use Mirasvit\Rewards\Api\Data\Notification\RuleInterface as NotificationRuleInterface;
use Mirasvit\Rewards\Api\Data\Spending\RuleInterface as SpendingRuleInterface;
use Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory as EarningCollectionFactory;
use Mirasvit\Rewards\Model\ResourceModel\Notification\Rule\CollectionFactory as NotificationCollectionFactory;
use Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\CollectionFactory as SpendingCollectionFactory;

class UpgradeData1029 implements UpgradeDataInterface
{
    /**
     * @var mixed
     */
    private $earningCollectionFactory;
    /**
     * @var mixed
     */
    private $notificationCollectionFactory;
    /**
     * @var mixed
     */
    private $spendingCollectionFactory;

    public function __construct()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->earningCollectionFactory      =  $objectManager->create('Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory');
        $this->notificationCollectionFactory =  $objectManager->create('Mirasvit\Rewards\Model\ResourceModel\Notification\Rule\CollectionFactory');
        $this->spendingCollectionFactory     =  $objectManager->create('Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\CollectionFactory');
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->updateEarningRules($setup);
        $this->updateNotificationRules($setup);
        $this->updateSpendingRule($setup);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    public function updateEarningRules($setup)
    {
        $collection = $this->earningCollectionFactory->create()->addFieldToFilter('active_to', ['notnull' => 1]);
        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        foreach ($collection as $rule) {
            $table = $setup->getTable('mst_rewards_earning_rule');
            $bind = [
                EarningRuleInterface::KEY_ACTIVE_TO => $this->fixDate($rule->getData(EarningRuleInterface::KEY_ACTIVE_TO))
            ];
            $where = [$rule->getIdFieldName() . ' = ?' => (int)$rule->getId()];
            $setup->getConnection()->update($table, $bind, $where);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    public function updateNotificationRules($setup)
    {
        $collection = $this->notificationCollectionFactory->create()->addFieldToFilter('active_to', ['notnull' => 1]);
        /** @var \Mirasvit\Rewards\Model\Notification\Rule $rule */
        foreach ($collection as $rule) {
            $table = $setup->getTable('mst_rewards_notification_rule');
            $bind = [
                NotificationRuleInterface::KEY_ACTIVE_TO => $this->fixDate($rule->getData(NotificationRuleInterface::KEY_ACTIVE_TO))
            ];
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
        $collection = $this->spendingCollectionFactory->create()->addFieldToFilter('active_to', ['notnull' => 1]);
        /** @var \Mirasvit\Rewards\Model\Spending\Rule $rule */
        foreach ($collection as $rule) {
            $table = $setup->getTable('mst_rewards_spending_rule');
            $bind = [
                SpendingRuleInterface::KEY_ACTIVE_TO => $this->fixDate($rule->getData(SpendingRuleInterface::KEY_ACTIVE_TO))
            ];
            $where = [$rule->getIdFieldName() . ' = ?' => (int)$rule->getId()];
            $setup->getConnection()->update($table, $bind, $where);
        }
    }

    /**
     * @param string $date
     * @return string
     */
    private function fixDate($date)
    {
        return date('Y-m-d', strtotime((string) $date) - 1);
    }
}
