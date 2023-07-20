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

interface TierInterface
{
    const KEY_TIER_KEY_TIER_ID           = 'tier_id';
    const KEY_TIER_KEY_EARNING_STYLE     = 'earning_style';
    const KEY_TIER_KEY_EARN_POINTS       = 'earn_points';
    const KEY_TIER_KEY_MONETARY_STEP     = 'monetary_step';
    const KEY_TIER_KEY_POINTS_LIMIT      = 'points_limit';
    const KEY_TIER_KEY_QTY_STEP          = 'qty_step';
    const KEY_TIER_KEY_TRANSFER_TO_GROUP = 'transfer_to_group';

    /**
     * @return int
     */
    public function getTierId();

    /**
     * @param int $id
     * @return $this
     */
    public function setTierId($id);

    /**
     * @return string
     */
    public function getEarningStyle();

    /**
     * @param string $style
     * @return $this
     */
    public function setEarningStyle($style);

    /**
     * @return int
     */
    public function getEarnPoints();

    /**
     * @param int $points
     * @return $this
     */
    public function setEarnPoints($points);

    /**
     * @return float
     */
    public function getMonetaryStep();

    /**
     * @param float $amount
     * @return $this
     */
    public function setMonetaryStep($amount);

    /**
     * @return int
     */
    public function getPointsLimit();

    /**
     * @param int $points
     * @return $this
     */
    public function setPointsLimit($points);

    /**
     * @return int
     */
    public function getQtyStep();

    /**
     * @param int $qty
     * @return $this
     */
    public function setQtyStep($qty);

    /**
     * @return int
     */
    public function getTransferToGroup();

    /**
     * @param int $id
     * @return $this
     */
    public function setTransferToGroup($id);
}