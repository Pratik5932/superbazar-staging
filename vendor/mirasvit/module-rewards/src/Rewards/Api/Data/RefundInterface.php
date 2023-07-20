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

interface RefundInterface
{
    const KEY_REFUND_ID       = 'refund_id';
    const KEY_ORDER_ID        = 'order_id';
    const KEY_INVOICE_ID      = 'invoice_id';
    const KEY_CREDITMEMO_ID   = 'creditmemo_id';
    const KEY_REFUNDED_POINTS = 'refunded_points';
    const KEY_BASE_REFUNDED   = 'base_refunded';
    const KEY_REFUNDED        = 'refunded';

    /**
     * @return int
     */
    public function getRefundId();

    /**
     * @param int $refundId
     * @return $this
     */
    public function setRefundId($refundId);

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
    public function getInvoiceId();

    /**
     * @param int $invoiceId
     * @return $this
     */
    public function setInvoiceId($invoiceId);

    /**
     * @return int
     */
    public function getCreditmemoId();

    /**
     * @param int $creditmemoId
     * @return $this
     */
    public function setCreditmemoId($creditmemoId);

    /**
     * @return int
     */
    public function getRefundedPoints();

    /**
     * @param int $refundedPoints
     * @return $this
     */
    public function setRefundedPoints($refundedPoints);

    /**
     * @return float
     */
    public function getBaseRefunded();

    /**
     * @param float $baseRefunded
     * @return $this
     */
    public function setBaseRefunded($baseRefunded);

    /**
     * @return float
     */
    public function getRefunded();

    /**
     * @param float $refunded
     * @return $this
     */
    public function setRefunded($refunded);

}
