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


namespace Mirasvit\Rewards\Api\Data\Earning;

interface RuleInterface
{
    const KEY_ID                      = 'earning_rule_id';
    const KEY_NAME                    = 'name';
    const KEY_DESCRIPTION             = 'description';
    const KEY_IS_ACTIVE               = 'is_active';
    const KEY_ACTIVE_FROM             = 'active_from';
    const KEY_ACTIVE_TO               = 'active_to';
    const KEY_TYPE                    = 'type';
    const KEY_CONDITIONS_SERIALIZED   = 'conditions_serialized';
    const KEY_ACTIONS_SERIALIZED      = 'actions_serialized';
    const KEY_BEHAVIOR_TRIGGER        = 'behavior_trigger';
    const KEY_SORT_ORDER              = 'sort_order';
    const KEY_IS_STOP_PROCESSING      = 'is_stop_processing';
    const KEY_PARAM1                  = 'param1';
    const KEY_HISTORY_MESSAGE         = 'history_message';
    const KEY_EMAIL_MESSAGE           = 'email_message';
    const KEY_FRONT_NAME              = 'front_name';
    const KEY_TIERS_SERIALIZED        = 'tiers_serialized';
    const KEY_WEBSITE_IDS             = 'website_ids';
    const KEY_CUSTOMER_GROUP_IDS      = 'customer_group_ids';
    const KEY_IS_SHOW_ON_PRODUCT_PAGE = 'is_show_on_product_page';

    const KEY_TIER_KEY_EARNING_STYLE     = 'earning_style';
    const KEY_TIER_KEY_EARN_POINTS       = 'earn_points';
    const KEY_TIER_KEY_MONETARY_STEP     = 'monetary_step';
    const KEY_TIER_KEY_POINTS_LIMIT      = 'points_limit';
    const KEY_TIER_KEY_QTY_STEP          = 'qty_step';
    const KEY_TIER_KEY_TRANSFER_TO_GROUP = 'transfer_to_group';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getRuleId();

    /**
     * @param int $id
     * @return $this
     */
    public function setRuleId($id);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsActive();

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * @return string
     */
    public function getActiveFrom();

    /**
     * @param string $activeFrom
     * @return $this
     */
    public function setActiveFrom($activeFrom);

    /**
     * @return string
     */
    public function getActiveTo();

    /**
     * @param string $activeTo
     * @return $this
     */
    public function setActiveTo($activeTo);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return \Magento\SalesRule\Api\Data\ConditionInterface
     */
    public function getCondition();

    /**
     * @param \Magento\SalesRule\Api\Data\ConditionInterface|null $condition
     * @return $this
     */
    public function setCondition($condition);

    /**
     * @return \Magento\SalesRule\Api\Data\ConditionInterface
     */
    public function getAction();

    /**
     * @param \Magento\SalesRule\Api\Data\ConditionInterface $action
     * @return $this
     */
    public function setAction($action);

    /**
     * @return string
     */
    public function getBehaviorTrigger();

    /**
     * @param string $trigger
     * @return $this
     */
    public function setBehaviorTrigger($trigger);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $order
     * @return $this
     */
    public function setSortOrder($order);

    /**
     * @return int
     */
    public function getIsStopProcessing();

    /**
     * @param int $isStop
     * @return $this
     */
    public function setIsStopProcessing($isStop);

    /**
     * @return string
     */
    public function getParam1();

    /**
     * @param string $message
     * @return $this
     */
    public function setParam1($message);

    /**
     * @return string
     */
    public function getHistoryMessage();

    /**
     * @param string $message
     * @return $this
     */
    public function setHistoryMessage($message);

    /**
     * @return string
     */
    public function getEmailMessage();

    /**
     * @param string $message
     * @return $this
     */
    public function setEmailMessage($message);

    /**
     * @return string
     */
    public function getFrontName();

    /**
     * @param string $name
     * @return $this
     */
    public function setFrontName($name);

    /**
     * @return \Mirasvit\Rewards\Api\Data\Earning\TierInterface[]
     */
    public function getTiers();

    /**
     * @param \Mirasvit\Rewards\Api\Data\Earning\TierInterface[] $tiers
     * @return $this
     */
    public function setTiers($tiers);

    /**
     * @return int[]|null
     */
    public function getWebsiteIds();

    /**
     * @param int[]|null $ids
     * @return $this
     */
    public function setWebsiteIds($ids);

    /**
     * @return string[]|null
     */
    public function getCustomerGroupIds();

    /**
     * @param string[]|null $ids
     * @return $this
     */
    public function setCustomerGroupIds($ids);
}