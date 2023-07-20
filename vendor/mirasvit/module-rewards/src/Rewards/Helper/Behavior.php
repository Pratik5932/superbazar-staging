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
use Mirasvit\Rewards\Model\ResourceModel;
use \Magento\Sales\Model\Order;
use \Mirasvit\Rewards\Model\Earning\Tier;

class Behavior
{
    /**
     * @var ResourceModel\Transaction\CollectionFactory
     */
    private $transactionCollectionFactory;
    /**
     * @var ResourceModel\Earning\Rule\CollectionFactory
     */
    private $earningRuleCollectionFactory;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;
    /**
     * @var Data
     */
    private $rewardsData;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var Config
     */
    private $config;

    public function __construct(
        ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        ResourceModel\Earning\Rule\CollectionFactory $earningRuleCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->earningRuleCollectionFactory = $earningRuleCollectionFactory;
        $this->resource                     = $resource;
        $this->rewardsData                  = $rewardsData;
        $this->date                         = $date;
        $this->config               = $config;
        $this->messageManager               = $messageManager;
    }


    /**
     * @param string                           $ruleType
     * @param \Magento\Customer\Model\Customer $customer
     * @param bool|int                         $websiteId
     * @param bool|string                         $code
     * @return bool|int
     */
    public function getEstimatedEarnPoints($ruleType, $customer, $websiteId, $code = false)
    {
        if (!$this->checkIsAllowToProcessRule($customer->getId(), $code)) {
            return false;
        }

        $rules = $this->getRules($ruleType, $customer, $websiteId);
        $amount = 0;
        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        foreach ($rules as $rule) {
            $rule->afterLoad();
            $tier = $rule->getTier($customer);
            $ruleLimit = $tier->getPointsLimit();
            $rulePoints = $tier->getEarnPoints();
            if (!$this->isInLimit($ruleLimit, $customer->getId(), $rulePoints, $rule->getBehaviorTrigger())) {
                continue;
            }
            $amount += $rulePoints;
        }

        return $amount;
    }

    /**
     * @param int    $customerId
     * @param string $code
     * @return bool
     */
    public function checkIsAllowToProcessRule($customerId, $code)
    {
        $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('code', $code)
            ->addFieldToFilter('customer_id', $customerId);
        $isAllow = $collection->count() == 0;

        return $isAllow;
    }

    /**
     * @param string                           $ruleType
     * @param \Magento\Customer\Model\Customer $customer
     * @param bool|int                         $websiteId
     * @return \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\Collection
     */
    public function getRules($ruleType, $customer, $websiteId)
    {
        $customerGroupId = $customer->getGroupId();
        $rules = $this->earningRuleCollectionFactory->create()
            ->addWebsiteFilter($websiteId)
            ->addCustomerGroupFilter($customerGroupId)
            ->addIsActiveFilter()
            ->addCurrentFilter()
            ->addFieldToFilter('type', \Mirasvit\Rewards\Model\Earning\Rule::TYPE_BEHAVIOR)
            ->addFieldToFilter('behavior_trigger', $ruleType);

        return $rules;
    }

    /**
     * @param int $ruleLimit
     * @param int                               $customerId
     * @param int                               $futurePoints
     * @param string                            $trigger
     * @return bool
     */
    public function isInLimit($ruleLimit, $customerId, $futurePoints, $trigger)
    {
        if (!$ruleLimit) {
            return true;
        }
        $resource = $this->resource;
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('mst_rewards_transaction');
        $date = $this->date->gmtDate('Y-m-d 00:00:00');
        $sum = (int) $readConnection->fetchOne(
            "SELECT SUM(amount) FROM $table ".
            "WHERE customer_id=".(int) $customerId." AND ".
            "code LIKE '$trigger-%' AND created_at > '$date'"
        );
        if ($ruleLimit >= ($sum + $futurePoints)) {
            return true;
        }

        return false;
    }

    /**
     * @param Order $order
     * @param Tier $tier
     * @param int $ruleLimit
     * @return float|int
     */
    public function getPointsFromOrder(Order $order, Tier $tier, $ruleLimit)
    {
        switch ($tier->getEarningStyle()) {
            case Config::EARNING_STYLE_GIVE:
                return $tier->getEarnPoints();
            case Config::EARNING_STYLE_AMOUNT_SPENT:
                if ($this->config->getGeneralIsIncludeTaxEarning()) {
                    $subtotal = $order->getGrandTotal();
                } else {
                    $subtotal = $order->getSubtotal();
                }
                $steps = (int) ($subtotal / $tier->getMonetaryStep());
                $amount = $steps * $tier->getEarnPoints();
                if ($ruleLimit && $amount > $ruleLimit) {
                    $amount = $ruleLimit;
                }
                return $amount;
            case Config::EARNING_STYLE_QTY_SPENT:
                $steps = (int) ($order->getTotalQtyOrdered() / $tier->getQtyStep());
                $amount = $steps * $tier->getEarnPoints();
                if ($ruleLimit && $amount > $ruleLimit) {
                    $amount = $ruleLimit;
                }
                return $amount;
        }
        return 0;
    }

    /**
     * Adds a success message in the frontend (via session).
     *
     * @param int    $points
     * @param string $ruleType
     *
     * @return void
     */
    public function addSuccessMessage($points, $ruleType)
    {
        $comments = [
            Config::BEHAVIOR_TRIGGER_SIGNUP => __('You received %1 for signing up'),
            Config::BEHAVIOR_TRIGGER_SEND_LINK => __('You received %1 for sending this product'),
            Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP => __('You received %1 for sign up for newsletter'),
            Config::BEHAVIOR_TRIGGER_PUSHNOTIFICATION_SIGNUP => __(
                'You received %1 for sign up for push notifications'
            ),
            Config::BEHAVIOR_TRIGGER_REVIEW => __('You will receive %1 after approving of this review'),
            Config::BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_SIGNUP => __(
                'You received %1 for sign up of referral customer.'
            ),
            Config::BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_ORDER => __('You received %1 for order of referral customer.'),
            Config::BEHAVIOR_TRIGGER_BIRTHDAY => __('Happy birthday! You received %1.'),
            Config::BEHAVIOR_TRIGGER_AFFILIATE_CREATE => __('You received %1 for joining the affiliate program.'),
        ];
        $hiddenPoints = [
            Config::BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_SIGNUP,
            Config::BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_ORDER,
        ];
        if (isset($comments[$ruleType])) {
            $notification = __($comments[$ruleType], $this->rewardsData->formatPoints($points));
            if (!in_array($ruleType, $hiddenPoints)) {
                $this->messageManager->addSuccessMessage($notification);
            }
        }
    }
}
