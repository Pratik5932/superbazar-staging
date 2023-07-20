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


namespace Mirasvit\Rewards\Api\Data\Spending;

interface TierInterface
{
    const KEY_TIER_KEY_TIER_ID          = 'tier_id';
    const KEY_TIER_KEY_SPENDING_STYLE   = 'spending_style';
    const KEY_TIER_KEY_SPEND_POINTS     = 'spend_points';
    const KEY_TIER_KEY_MONETARY_STEP    = 'monetary_step';
    const KEY_TIER_KEY_SPEND_MIN_POINTS = 'spend_min_points';
    const KEY_TIER_KEY_SPEND_MAX_POINTS = 'spend_max_points';

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
    public function getSpendingStyle();

    /**
     * @param string $style
     * @return $this
     */
    public function setSpendingStyle($style);

    /**
     * @return int
     */
    public function getSpendPoints();

    /**
     * @param int $points
     * @return $this
     */
    public function setSpendPoints($points);

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
    public function getSpendMinPoints();

    /**
     * @param int $points
     * @return $this
     */
    public function setSpendMinPoints($points);

    /**
     * @return int
     */
    public function getSpendMaxPoints();

    /**
     * @param int $points
     * @return $this
     */
    public function setSpendMaxPoints($points);

}