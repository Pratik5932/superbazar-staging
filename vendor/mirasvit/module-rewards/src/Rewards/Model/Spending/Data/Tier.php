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
use Mirasvit\Rewards\Api\Data\Spending\TierInterface;

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
    public function getSpendingStyle()
    {
        return $this->_get(self::KEY_TIER_KEY_SPENDING_STYLE);
    }

    /**
     * @inheritDoc
     */
    public function setSpendingStyle($style)
    {
        return $this->setData(self::KEY_TIER_KEY_SPENDING_STYLE, $style);
    }

    /**
     * @inheritDoc
     */
    public function getSpendPoints()
    {
        return $this->_get(self::KEY_TIER_KEY_SPEND_POINTS);
    }

    /**
     * @inheritDoc
     */
    public function setSpendPoints($points)
    {
        return $this->setData(self::KEY_TIER_KEY_SPEND_POINTS, $points);
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
    public function getSpendMinPoints()
    {
        return $this->_get(self::KEY_TIER_KEY_SPEND_MIN_POINTS);
    }

    /**
     * @inheritDoc
     */
    public function setSpendMinPoints($points)
    {
        return $this->setData(self::KEY_TIER_KEY_SPEND_MIN_POINTS, $points);
    }

    /**
     * @inheritDoc
     */
    public function getSpendMaxPoints()
    {
        return $this->_get(self::KEY_TIER_KEY_SPEND_MAX_POINTS);
    }

    /**
     * @inheritDoc
     */
    public function setSpendMaxPoints($points)
    {
        return $this->setData(self::KEY_TIER_KEY_SPEND_MAX_POINTS, $points);
    }
}