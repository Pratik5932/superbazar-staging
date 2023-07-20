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


namespace Mirasvit\Rewards\Helper\Balance;

class SpendRulesList
{
    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\CollectionFactory
     */
    private $spendingRuleCollectionFactory;

    public function __construct(
        \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\CollectionFactory $spendingRuleCollectionFactory
    ) {
        $this->spendingRuleCollectionFactory = $spendingRuleCollectionFactory;
    }

    /**
     * Get spending rule collection for quote
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\Collection
     */
    public function getRules($quote)
    {
        $websiteId       = $quote->getStore()->getWebsiteId();
        $customerGroupId = $quote->getCustomerGroupId();

        return $this->getRuleCollection($websiteId, $customerGroupId);
    }

    /**
     * Get spending rule collection for website and customer group
     * @param int $websiteId
     * @param int $customerGroupId
     * @return \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\Collection
     */
    public function getRuleCollection($websiteId, $customerGroupId)
    {
        $rules = $this->spendingRuleCollectionFactory->create()
            ->addWebsiteFilter($websiteId)
            ->addCustomerGroupFilter($customerGroupId)
            ->addCurrentFilter()
        ;
        $rules->getSelect()->order('sort_order ASC');

        return $rules;
    }
}