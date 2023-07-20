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


namespace Mirasvit\Rewards\Model\Earning;
use Mirasvit\Rewards\Api\Data\Earning\RuleInterface;

class Tier {

    /**
     * @var string
     */
    private $earningStyle;
    /**
     * @var int
     */
    private $earnPoints;
    /**
     * @var int
     */
    private $pointsLimit;
    /**
     * @var int
     */
    private $monetaryStep;
    /**
     * @var int
     */
    private $qtyStep;
    /**
     * @var int
     */
    private $transferGroupId;

    /**
     * Tier constructor.
     * @param array $tierData
     */
    public function __construct($tierData)
    {
        if (isset($tierData[RuleInterface::KEY_TIER_KEY_EARNING_STYLE])) {
            $this->earningStyle = $tierData[RuleInterface::KEY_TIER_KEY_EARNING_STYLE];
        }
        if (isset($tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS])) {
            $this->earnPoints = $tierData[RuleInterface::KEY_TIER_KEY_EARN_POINTS];
        }
        if (isset($tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT])) {
            $this->pointsLimit = $tierData[RuleInterface::KEY_TIER_KEY_POINTS_LIMIT];
        }
        if (isset($tierData[RuleInterface::KEY_TIER_KEY_MONETARY_STEP])) {
            $this->monetaryStep = $tierData[RuleInterface::KEY_TIER_KEY_MONETARY_STEP];
        }
        if (isset($tierData[RuleInterface::KEY_TIER_KEY_QTY_STEP])) {
            $this->qtyStep = $tierData[RuleInterface::KEY_TIER_KEY_QTY_STEP];
        }
        if (isset($tierData[RuleInterface::KEY_TIER_KEY_TRANSFER_TO_GROUP])) {
            $this->transferGroupId = $tierData[RuleInterface::KEY_TIER_KEY_TRANSFER_TO_GROUP];
        }
    }

    /**
     * @return string
     */
    public function getEarningStyle()
    {
        return $this->earningStyle;
    }

    /**
     * @return int
     */
    public function getEarnPoints()
    {
        return $this->earnPoints;
    }

    /**
     * @return int
     */
    public function getPointsLimit()
    {
        return (int)$this->pointsLimit;
    }

    /**
     * @return int
     */
    public function getMonetaryStep()
    {
        return $this->monetaryStep;
    }

    /**
     * @return int
     */
    public function getQtyStep()
    {
        return $this->qtyStep;
    }

    /**
     * @return int
     */
    public function getTransferGroupId()
    {
        return $this->transferGroupId;
    }
}
