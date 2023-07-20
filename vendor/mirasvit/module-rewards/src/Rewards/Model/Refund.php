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


namespace Mirasvit\Rewards\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Rewards\Api\Data\RefundInterface;

class Refund extends AbstractModel implements RefundInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rewards\Model\ResourceModel\Refund');
    }

    /**
     * {@inheritDoc}
     */
    public function getRefundId()
    {
        return $this->getData(self::KEY_REFUND_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setRefundId($refundId)
    {
        return $this->setData(self::KEY_REFUND_ID, $refundId);
    }

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
    public function getInvoiceId()
    {
        return $this->getData(self::KEY_INVOICE_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setInvoiceId($invoiceId)
    {
        return $this->setData(self::KEY_INVOICE_ID, $invoiceId);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreditmemoId()
    {
        return $this->getData(self::KEY_CREDITMEMO_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreditmemoId($creditmemoId)
    {
        return $this->setData(self::KEY_CREDITMEMO_ID, $creditmemoId);
    }

    /**
     * {@inheritDoc}
     */
    public function getRefundedPoints()
    {
        return $this->getData(self::KEY_REFUNDED_POINTS);
    }

    /**
     * {@inheritDoc}
     */
    public function setRefundedPoints($refundedPoints)
    {
        return $this->setData(self::KEY_REFUNDED_POINTS, $refundedPoints);
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseRefunded()
    {
        return $this->getData(self::KEY_BASE_REFUNDED);
    }

    /**
     * {@inheritDoc}
     */
    public function setBaseRefunded($baseRefunded)
    {
        return $this->setData(self::KEY_BASE_REFUNDED, $baseRefunded);
    }

    /**
     * {@inheritDoc}
     */
    public function getRefunded()
    {
        return $this->getData(self::KEY_REFUNDED);
    }

    /**
     * {@inheritDoc}
     */
    public function setRefunded($refunded)
    {
        return $this->setData(self::KEY_REFUNDED, $refunded);
    }
}
