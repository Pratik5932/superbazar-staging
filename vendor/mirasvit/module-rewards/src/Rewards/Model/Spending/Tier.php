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


namespace Mirasvit\Rewards\Model\Spending;

use Mirasvit\Rewards\Api\Data\Spending\RuleInterface;
use Mirasvit\Rewards\Helper\Calculation;
use Mirasvit\Rewards\Api\Config\Rule\SpendingStyleInterface;

class Tier {

    /**
     * @var string
     */
    private $spendingStyle;
    /**
     * @var int
     */
    private $spendPoints;
    /**
     * @var int
     */
    private $spendMaxPoints;
    /**
     * @var int
     */
    private $spendMinPoints;
    /**
     * @var int
     */
    private $monetaryStep;
    /**
     * @var \Mirasvit\Rewards\Service\RoundService
     */
    private $roundService;

    /**
     * @param \Mirasvit\Rewards\Service\RoundService $roundService
     * @param array $tierData
     */
    public function __construct(
        \Mirasvit\Rewards\Service\RoundService $roundService,
        $tierData
    )
    {
        $this->roundService   = $roundService;
        $this->spendingStyle  = $tierData[RuleInterface::KEY_TIER_KEY_SPENDING_STYLE];
        $this->spendPoints    = $tierData[RuleInterface::KEY_TIER_KEY_SPEND_POINTS];
        $this->spendMinPoints = $tierData[RuleInterface::KEY_TIER_KEY_SPEND_MIN_POINTS];
        $this->spendMaxPoints = $tierData[RuleInterface::KEY_TIER_KEY_SPEND_MAX_POINTS];
        $this->monetaryStep   = $tierData[RuleInterface::KEY_TIER_KEY_MONETARY_STEP];
    }

    /**
     * @return string
     */
    public function getSpendingStyle()
    {
        return $this->spendingStyle;
    }

    /**
     * @return int
     */
    public function getSpendPoints()
    {
        return $this->spendPoints;
    }

    /**
     * @return int
     */
    public function getSpendMinPoints()
    {
        return $this->spendMinPoints;
    }

    /**
     * @return int
     */
    public function getSpendMaxPoints()
    {
        return $this->spendMaxPoints;
    }

    /**
     * @param float $subtotal
     * @return float
     */
    public function getMonetaryStep($subtotal)
    {
        if (!$subtotal) {
            return 0;
        }
        $value = (string) $this->monetaryStep;
        if (strpos($value, '%') === false) {
            return $value;
        }
        $value = str_replace('%', '', $value);

        return $subtotal * $value / 100;
    }

    /**
     * @param float $subtotal
     * @return bool|float
     */
    public function getSpendMinAmount($subtotal)
    {
        $min = $this->getSpendMinPoints();

        if ($min <= 0 && $this->getSpendingStyle() == SpendingStyleInterface::STYLE_FULL) {
            $min = $this->getSpendPoints();
        }
        // \p{Po} - because % has different presentation for different languages
        if (!preg_match('/\p{Po}/u', $min)) {
            return $min;
        }
        $min = preg_replace('/\p{Po}/u', '', $min);

        $monetaryStep = $this->getMonetaryStep($subtotal);
        if ($monetaryStep <= Calculation::ZERO_VALUE) {
            return 0;
        }

        return ceil($subtotal * $min / 100 / $monetaryStep * $this->getSpendPoints());
    }

    /**
     * @param float $subtotal
     * @return bool|float
     */
    public function getSpendMaxAmount($subtotal)
    {
        $tierMax      = $this->getSpendMaxPoints();
        $max          = $tierMax ?: '100%';
        $stepPoints   = $this->getSpendPoints();
        $monetaryStep = $this->getMonetaryStep($subtotal);
        if ($monetaryStep <= Calculation::ZERO_VALUE) {
            return 0;
        }

        if (strpos($max, '%') === false) {
            $max = '100%';
        }
        // \p{Po} - because % has different presentation for different languages
        if (preg_match('/\p{Po}/u', $tierMax)) {
            $tierMax = preg_replace('/\p{Po}/u', '', $tierMax);
            $tierMax = (int)($subtotal * $tierMax / 100 / $monetaryStep * $stepPoints);
        }
        $max = str_replace('%', '', $max);

        $points = $subtotal * $max / 100 / $monetaryStep * $stepPoints;
        $points = $this->roundService->round($points, $max == 100);//if points is limited we should not exceed it

        return $tierMax ? min($points, $tierMax) : $points;
    }
}
