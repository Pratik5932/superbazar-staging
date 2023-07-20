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


namespace Mirasvit\Rewards\Model\Refund;

use Magento\Framework\DataObject;
use Mirasvit\Rewards\Api\Data\Refund\RefundInfoInterface;

class RefundInfo extends DataObject implements RefundInfoInterface
{
    /**
     * {@inheritDoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::KEY_ORDER_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::KEY_ORDER_ID, $orderId);
    }

    /**
     * {@inheritDoc}
     */
    public function getInvoiceIds()
    {
        return $this->getData(self::KEY_INVOICE_IDS);
    }

    /**
     * @param array|string $invoiceIds
     * @return $this
     */
    public function setInvoiceIds($invoiceIds)
    {
        return $this->setData(self::KEY_INVOICE_IDS, $invoiceIds);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreditmemoIds()
    {
        return (array)$this->getData(self::KEY_CREDITMEMO_IDS);
    }

    /**
     * @param array|string $creditmemoIds
     * @return $this
     */
    public function setCreditmemoIds($creditmemoIds)
    {
        return $this->setData(self::KEY_CREDITMEMO_IDS, $creditmemoIds);
    }

    /**
     * {@inheritDoc}
     */
    public function getRefundedPointsSum()
    {
        return $this->getData(self::KEY_REFUNDED_POINTS_SUM);
    }

    /**
     * {@inheritDoc}
     */
    public function setRefundedPointsSum($refundedPointsSum)
    {
        return $this->setData(self::KEY_REFUNDED_POINTS_SUM, $refundedPointsSum);
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseRefundedSum()
    {
        return $this->getData(self::KEY_BASE_REFUNDED_SUM);
    }

    /**
     * {@inheritDoc}
     */
    public function setBaseRefundedSum($baseRefundedSum)
    {
        return $this->setData(self::KEY_BASE_REFUNDED_SUM, $baseRefundedSum);
    }

    /**
     * {@inheritDoc}
     */
    public function getRefundedSum()
    {
        return $this->getData(self::KEY_REFUNDED_SUM);
    }

    /**
     * {@inheritDoc}
     */
    public function setRefundedSum($refundedSum)
    {
        return $this->setData(self::KEY_REFUNDED_SUM, $refundedSum);
    }
}
