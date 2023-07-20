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



namespace Mirasvit\Rewards\Helper;
use Mirasvit\Rewards\Model\Config;
use \Mirasvit\Rewards\Model\Earning\Tier;
use \Mirasvit\Rewards\Model\Earning\Rule;

class BehaviorRule
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Behavior
     */
    private $behaviorHelper;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    private $customerGroup;
    /**
     * @var Balance
     */
    private $rewardsBalance;
    private $storeProviders;


    public function __construct(
        Behavior $behaviorHelper,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        StoreProviders $storeProviders,
        \Mirasvit\Rewards\Model\Config $config
    ) {
        $this->config                     = $config;
        $this->behaviorHelper                     = $behaviorHelper;
        $this->customerGroup                     = $customerGroup;
        $this->rewardsBalance                     = $rewardsBalance;
        $this->storeProviders                     = $storeProviders;
    }

    /**
     * @param string $ruleType
     * @param bool|\Magento\Customer\Model\Customer|int $customerId
     * @param bool|int $websiteId
     * @param bool|string $code
     * @param array $options
     * @param bool $validateReviewRuleOnly
     * @return bool|\Mirasvit\Rewards\Model\Transaction
     */
    public function processRule($ruleType, $customerId = false, $websiteId = false, $code = false, $options = [], $validateReviewRuleOnly = false)
    {
        if (!$customer = $this->storeProviders->getCustomer($customerId)) {
            return false;
        }
        if (!$websiteId) {
            $websiteId = $this->storeProviders->getWebsiteId();
        }
        $code = $this->prepareCode($code, $ruleType);
        if (!$this->behaviorHelper->checkIsAllowToProcessRule($customer->getId(), $code)) {
            return false;
        }
        $rules = $this->behaviorHelper->getRules($ruleType, $customer, $websiteId);
        $lastTransaction = false;
        foreach ($rules as $rule) {
            /* @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
            $rule->afterLoad();
            $total = $this->validateRule($rule, $customer, $options);
            if (!$total) {
                continue;
            }
            $tier = $rule->getTier($customer);
            if ($validateReviewRuleOnly) {
                $this->behaviorHelper->addSuccessMessage($tier->getEarnPoints(), $ruleType);
            } else {
                $lastTransaction = $this->processThisRule($rule, $customer, $code, $total);
                if ($lastTransaction && $ruleType != Config::BEHAVIOR_TRIGGER_REVIEW) {
                    $this->behaviorHelper->addSuccessMessage($tier->getEarnPoints(), $ruleType);
                }
            }
            if ($rule->getIsStopProcessing()) {
                break;
            }
        }
        return $lastTransaction;
    }

    /**
     * @param string $code
     * @param string $ruleType
     * @return string
     */
    private function prepareCode($code, $ruleType)
    {
        if ($code) {
            return $ruleType.'-'.$code;
        }
        return $ruleType;
    }

    /**
     * @param Rule $rule
     * @param \Magento\Customer\Model\Customer $customer
     * @param array $options
     * @return bool|float|int
     */
    private function validateRule(Rule $rule, $customer, $options)
    {
        /** @var \Magento\Framework\DataObject $customer */
        if (isset($options['referred_customer'])) {
            $customer->setReferredCustomer($options['referred_customer']);
        }
        if (isset($options['order'])) {
            $customer->setCustomerOrder($options['order']);
        }
        if (isset($options['rma'])) {
            $customer->setRma($options['rma']);
        }

        if (isset($options['product'])) {
            $customer->setProduct($options['product']);
        }

        if (!$rule->validate($customer)) {
            return false;
        }
        $tier = $rule->getTier($customer);

        $total = $tier->getEarnPoints();
        $ruleLimit = $tier->getPointsLimit();
        if (isset($options['order'])) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $options['order'];
            $total = $this->behaviorHelper->getPointsFromOrder($order, $tier, $ruleLimit);
        }

        if (!$this->behaviorHelper->isInLimit($ruleLimit, $customer->getId(), $total, $rule->getBehaviorTrigger())) {
            return false;
        }
        return $total;
    }

    /**
     * @param Rule $rule
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $code
     * @param float $total
     * @return bool|\Mirasvit\Rewards\Model\Transaction
     */
    private function processThisRule(Rule $rule, $customer, $code, $total)
    {
        $storeId = $customer->getData('store_id') ?: $customer->getStore()->getId();
        $rule->setStoreId($storeId);
        $lastTransaction = $this->rewardsBalance->changePointsBalance(
            $customer,
            $total,
            $rule->getHistoryMessage(),
            false,
            $code.'-'.$rule->getId(),
            true,
            $rule->getEmailMessage()
        );
        $tier = $rule->getTier($customer);
        $this->changeCustomerGroup($tier, $customer);
        return $lastTransaction;
    }

    /**
     * @param Tier $tier
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     */
    private function changeCustomerGroup(Tier $tier, $customer)
    {
        // If customer group is set, then customer should be assigned there
        $existingGroups = $this->customerGroup->toOptionArray();

        if ($tier->getTransferGroupId() && $customer->getGroupId() != $tier->getTransferGroupId()) {
            foreach ($existingGroups as $group) {
                if ($group['value'] == $tier->getTransferGroupId()) {
                    /** @var \Magento\Customer\Model\Customer $customer */
                    $customer->setGroupId($tier->getTransferGroupId())
                        ->save();
                    break;
                }
            }
        }

    }
}
