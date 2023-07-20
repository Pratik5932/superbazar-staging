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


namespace Mirasvit\Rewards\Helper\Balance;

class SpendCartRangeData {
    public $subtotal, $balancePoints, $minPoints, $maxPoints;

    /**
     * SpendCartRangeData constructor.
     * @param float $quoteSubTotal
     * @param float $balancePoints
     * @param float $minPoints
     * @param float $totalPoints
     */
    public function __construct($quoteSubTotal, $balancePoints, $minPoints, $totalPoints)
    {
        $this->minPoints = $minPoints;
        $this->maxPoints = $totalPoints;
        $this->subtotal = $quoteSubTotal;
        $this->balancePoints = $balancePoints;
    }
}