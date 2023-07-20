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



namespace Mirasvit\Rewards\Model\Earning\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use Mirasvit\Rewards\Api\Data\Earning\TierInterface;

class Tier extends AbstractSimpleObject implements TierInterface
{
    /**
     * @inheritDoc
     */
    public function getTierId()
    {
        return $this->_get(self::KEY_TIER_KEY_TIER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTierId($id)
    {
        return $this->setData(self::KEY_TIER_KEY_TIER_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getEarningStyle()
    {
        return $this->_get(self::KEY_TIER_KEY_EARNING_STYLE);
    }

    /**
     * @inheritDoc
     */
    public function setEarningStyle($style)
    {
        return $this->setData(self::KEY_TIER_KEY_EARNING_STYLE, $style);
    }

    /**
     * @inheritDoc
     */
    public function getEarnPoints()
    {
        return $this->_get(self::KEY_TIER_KEY_EARN_POINTS);
    }

    /**
     * @inheritDoc
     */
    public function setEarnPoints($points)
    {
        return $this->setData(self::KEY_TIER_KEY_EARN_POINTS, $points);
    }

    /**
     * @inheritDoc
     */
    public function getMonetaryStep()
    {
        return $this->_get(self::KEY_TIER_KEY_MONETARY_STEP);
    }

    /**
     * @inheritDoc
     */
    public function setMonetaryStep($step)
    {
        return $this->setData(self::KEY_TIER_KEY_MONETARY_STEP, $step);
    }

    /**
     * @inheritDoc
     */
    public function getPointsLimit()
    {
        return $this->_get(self::KEY_TIER_KEY_POINTS_LIMIT);
    }

    /**
     * @inheritDoc
     */
    public function setPointsLimit($points)
    {
        return $this->setData(self::KEY_TIER_KEY_POINTS_LIMIT, $points);
    }

    /**
     * @inheritDoc
     */
    public function getQtyStep()
    {
        return $this->_get(self::KEY_TIER_KEY_QTY_STEP);
    }

    /**
     * @inheritDoc
     */
    public function setQtyStep($step)
    {
        return $this->setData(self::KEY_TIER_KEY_QTY_STEP, $step);
    }

    /**
     * @inheritDoc
     */
    public function getTransferToGroup()
    {
        return $this->_get(self::KEY_TIER_KEY_TRANSFER_TO_GROUP);
    }

    /**
     * @inheritDoc
     */
    public function setTransferToGroup($groupId)
    {
        return $this->setData(self::KEY_TIER_KEY_TRANSFER_TO_GROUP, $groupId);
    }
}