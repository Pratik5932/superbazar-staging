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


namespace Mirasvit\Rewards\Api\Data;

interface PurchaseInterface
{
    const KEY_QUOTE_ID = 'quote_id';
    const KEY_ORDER_ID = 'order_id';
    const KEY_SPEND_POINTS = 'spend_points';
    const KEY_BASE_SPEND_AMOUNT = 'base_spend_amount';
    const KEY_SPEND_AMOUNT = 'spend_amount';
    const KEY_SPEND_MIN_POINTS = 'spend_min_points';
    const KEY_SPEND_MAX_POINTS = 'spend_max_points';
    const KEY_EARN_POINTS = 'earn_points';
    const KEY_LOCK_QUOTE = 'lock_quote';

    /**
     * @return int
     */
    public function getQuoteId();

    /**
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * @return int
     */
    public function getSpendPoints();

    /**
     * @param int $spendPoints
     * @return $this
     */
    public function setSpendPoints($spendPoints);

    /**
     * @return float
     */
    public function getSpendAmount();

    /**
     * @param float $spendAmount
     * @return $this
     */
    public function setSpendAmount($spendAmount);

    /**
     * @return float
     */
    public function getBaseSpendAmount();

    /**
     * @param float $baseSpendAmount
     * @return $this
     */
    public function setBaseSpendAmount($baseSpendAmount);

    /**
     * @return int
     */
    public function getSpendMinPoints();

    /**
     * @param int $spendMinPoints
     * @return $this
     */
    public function setSpendMinPoints($spendMinPoints);

    /**
     * @return int
     */
    public function getSpendMaxPoints();

    /**
     * @param int $spendMaxPoints
     * @return $this
     */
    public function setSpendMaxPoints($spendMaxPoints);

    /**
     * @return int
     */
    public function getEarnPoints();

    /**
     * @param int $earnPoints
     * @return $this
     */
    public function setEarnPoints($earnPoints);

    /**
     * @return string
     */
    public function getLockQuote();

    /**
     * @param string $time
     * @return $this
     */
    public function setLockQuote($time);
}
