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



namespace Mirasvit\RewardsCustomerAccount\Helper\Account;

use Mirasvit\Rewards\Api\Data\Earning\RuleInterface as EarnRuleInterface;
use Mirasvit\Rewards\Api\Data\Spending\RuleInterface as SpendRuleInterface;
use Mirasvit\Rewards\Model\Config;

/**
 * Class Rule
 *
 * Select available rules to show them on rewards pages in customer account
 *
 * @package Mirasvit\RewardsCustomerAccount\Helper\Account
 */
class Rule
{
    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory
     */
    private $earningRuleCollectionFactory;
    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\CollectionFactory
     */
    private $spendingRuleCollectionFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var array
     */
    protected $socialTypes = [
        Config::BEHAVIOR_TRIGGER_FACEBOOK_LIKE,
        Config::BEHAVIOR_TRIGGER_FACEBOOK_SHARE,
        Config::BEHAVIOR_TRIGGER_PINTEREST_PIN,
        Config::BEHAVIOR_TRIGGER_TWITTER_TWEET,
    ];

    public function __construct(
        \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory $earningRuleCollectionFactory,
        \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\CollectionFactory $spendingRuleCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->earningRuleCollectionFactory = $earningRuleCollectionFactory;
        $this->spendingRuleCollectionFactory = $spendingRuleCollectionFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * @return \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\Collection
     */
    public function getDisplayEarnRules()
    {
        $websiteId = $this->getWebsiteId();
        return $this->earningRuleCollectionFactory->create()
            ->addWebsiteFilter($websiteId)
            ->addCustomerGroupFilter($this->customerSession->getCustomerGroupId())
            ->addCurrentFilter()
            ->addFieldToFilter(EarnRuleInterface::KEY_FRONT_NAME, ['notnull' => null])
            ->addFieldToFilter(EarnRuleInterface::KEY_FRONT_NAME, ['neq' => ''])
            ->addFieldToFilter(EarnRuleInterface::KEY_BEHAVIOR_TRIGGER, ['nin' => $this->socialTypes])
            ;
    }

    /**
     * @return \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\Collection
     */
    public function getDisplaySocialRules()
    {
        $websiteId = $this->getWebsiteId();
        return $this->earningRuleCollectionFactory->create()
            ->addWebsiteFilter($websiteId)
            ->addCustomerGroupFilter($this->customerSession->getCustomerGroupId())
            ->addCurrentFilter()
            ->addFieldToFilter(EarnRuleInterface::KEY_FRONT_NAME, ['notnull' => null])
            ->addFieldToFilter(EarnRuleInterface::KEY_FRONT_NAME, ['neq' => ''])
            ->addFieldToFilter(EarnRuleInterface::KEY_BEHAVIOR_TRIGGER, ['in' => $this->socialTypes])
            ;
    }

    /**
     * @return \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\Collection
     */
    public function getDisplaySpendRules()
    {
        $websiteId = $this->getWebsiteId();
        return $this->spendingRuleCollectionFactory->create()
            ->addWebsiteFilter($websiteId)
            ->addCustomerGroupFilter($this->customerSession->getCustomerGroupId())
            ->addCurrentFilter()
            ->addFieldToFilter(SpendRuleInterface::KEY_FRONT_NAME, ['notnull' => null])
            ->addFieldToFilter(SpendRuleInterface::KEY_FRONT_NAME, ['neq' => ''])
            ;
    }

    /**
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }
}