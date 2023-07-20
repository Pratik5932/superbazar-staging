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



namespace Mirasvit\Rewards\Model\Spending\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use Mirasvit\Rewards\Api\Data\Spending\RuleInterface;

class Rule extends AbstractSimpleObject implements RuleInterface
{
    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->_get(self::KEY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::KEY_ID, $id);
    }
    /**
     * @inheritDoc
     */
    public function getRuleId()
    {
        return $this->_get(self::KEY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRuleId($id)
    {
        return $this->setData(self::KEY_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->_get(self::KEY_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->_get(self::KEY_DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::KEY_DESCRIPTION, $description);
    }

    /**
     * @inheritDoc
     */
    public function getIsActive()
    {
        return $this->_get(self::KEY_IS_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::KEY_IS_ACTIVE, $isActive);
    }

    /**
     * @inheritDoc
     */
    public function getActiveFrom()
    {
        return $this->_get(self::KEY_ACTIVE_FROM);
    }

    /**
     * @inheritDoc
     */
    public function setActiveFrom($activeFrom)
    {
        return $this->setData(self::KEY_ACTIVE_FROM, $activeFrom);
    }

    /**
     * @inheritDoc
     */
    public function getActiveTo()
    {
        return $this->_get(self::KEY_ACTIVE_TO);
    }

    /**
     * @inheritDoc
     */
    public function setActiveTo($activeTo)
    {
        return $this->setData(self::KEY_ACTIVE_TO, $activeTo);
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->_get(self::KEY_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setType($type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * @inheritDoc
     */
    public function getCondition()
    {
        return $this->_get(self::KEY_CONDITIONS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setCondition($condition)
    {
        return $this->setData(self::KEY_CONDITIONS_SERIALIZED, $condition);
    }

    /**
     * @inheritDoc
     */
    public function getAction()
    {
        return $this->_get(self::KEY_ACTIONS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setAction($action)
    {
        return $this->setData(self::KEY_ACTIONS_SERIALIZED, $action);
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder()
    {
        return $this->_get(self::KEY_SORT_ORDER);
    }

    /**
     * @inheritDoc
     */
    public function setSortOrder($order)
    {
        return $this->setData(self::KEY_SORT_ORDER, $order);
    }

    /**
     * @inheritDoc
     */
    public function getIsStopProcessing()
    {
        return $this->_get(self::KEY_IS_STOP_PROCESSING);
    }

    /**
     * @inheritDoc
     */
    public function setIsStopProcessing($isStop)
    {
        return $this->setData(self::KEY_IS_STOP_PROCESSING, $isStop);
    }

    /**
     * @inheritDoc
     */
    public function getFrontName()
    {
        return $this->_get(self::KEY_FRONT_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setFrontName($name)
    {
        return $this->setData(self::KEY_FRONT_NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getTiers()
    {
        return $this->_get(self::KEY_TIERS_SERIALIZED);
    }

    /**
     * @inheritDoc
     */
    public function setTiers($tiers)
    {
        return $this->setData(self::KEY_TIERS_SERIALIZED, $tiers);
    }

    /**
     * @return int[]
     */
    public function getWebsiteIds()
    {
        return $this->_get(self::KEY_WEBSITE_IDS);
    }

    /**
     * @param int[] $ids
     * @return $this
     */
    public function setWebsiteIds($ids)
    {
        return $this->setData(self::KEY_WEBSITE_IDS, $ids);
    }

    /**
     * @return int[]
     */
    public function getCustomerGroupIds()
    {
        return $this->_get(self::KEY_CUSTOMER_GROUP_IDS);
    }

    /**
     * @param int[] $ids
     * @return $this
     */
    public function setCustomerGroupIds($ids)
    {
        return $this->setData(self::KEY_CUSTOMER_GROUP_IDS, $ids);
    }
}