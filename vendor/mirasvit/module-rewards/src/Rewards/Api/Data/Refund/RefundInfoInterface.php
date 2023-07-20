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


namespace Mirasvit\Rewards\Api\Data\Refund;

interface RefundInfoInterface
{
    const KEY_ORDER_ID            = 'order_id';
    const KEY_INVOICE_IDS         = 'invoice_ids';
    const KEY_CREDITMEMO_IDS      = 'creditmemo_ids';
    const KEY_REFUNDED_POINTS_SUM = 'refunded_points_sum';
    const KEY_BASE_REFUNDED_SUM   = 'base_refunded_sum';
    const KEY_REFUNDED_SUM        = 'refunded_sum';

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
     * @return array
     */
    public function getInvoiceIds();

    /**
     * @param array $invoiceIds
     * @return $this
     */
    public function setInvoiceIds($invoiceIds);

    /**
     * @return array
     */
    public function getCreditmemoIds();

    /**
     * @param array $creditmemoIds
     * @return $this
     */
    public function setCreditmemoIds($creditmemoIds);

    /**
     * @return int
     */
    public function getRefundedPointsSum();

    /**
     * @param int $refundedPointsSum
     * @return $this
     */
    public function setRefundedPointsSum($refundedPointsSum);

    /**
     * @return float
     */
    public function getBaseRefundedSum();

    /**
     * @param float $baseRefundedSum
     * @return $this
     */
    public function setBaseRefundedSum($baseRefundedSum);

    /**
     * @return float
     */
    public function getRefundedSum();

    /**
     * @param float $refundedSum
     * @return $this
     */
    public function setRefundedSum($refundedSum);

}
