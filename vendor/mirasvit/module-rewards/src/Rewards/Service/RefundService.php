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



namespace Mirasvit\Rewards\Service;

use Mirasvit\Rewards\Api\Data\RefundInterface;
use Mirasvit\Rewards\Api\Service\RefundServiceInterface;
use Mirasvit\Rewards\Model\Refund\RefundInfoFactory;
use Mirasvit\Rewards\Model\ResourceModel\Refund\CollectionFactory;

class RefundService implements RefundServiceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var RefundInfoFactory
     */
    private $refundInfoFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        RefundInfoFactory $refundInfoFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->refundInfoFactory = $refundInfoFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getByOrderId($orderId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(RefundInterface::KEY_ORDER_ID, $orderId);

        $invoiceIds = [];
        $creditmemoIds = [];
        /** @var \Mirasvit\Rewards\Model\Refund\RefundInfo $refundInfo */
        $refundInfo = $this->refundInfoFactory->create();
        /** @var \Mirasvit\Rewards\Model\Refund $refund */
        foreach ($collection as $refund) {
            $refundInfo->setOrderId($refund->getOrderId());
            if ($refund->getInvoiceId()) {
                $invoiceIds[] = $refund->getInvoiceId();
            }
            $creditmemoIds[] = $refund->getCreditmemoId();
            $refundInfo->setRefundedPointsSum($refundInfo->getRefundedPointsSum() + $refund->getRefundedPoints());
            $refundInfo->setBaseRefundedSum($refundInfo->getBaseRefundedSum() + $refund->getBaseRefunded());
            $refundInfo->setRefundedSum($refundInfo->getRefundedSum() + $refund->getRefunded());
        }
        if ($invoiceIds) {
            $refundInfo->setInvoiceIds(implode(',', $invoiceIds));
        }
        if ($creditmemoIds) {
            $refundInfo->setCreditmemoIds(implode(',', $creditmemoIds));
        }

        return $refundInfo;
    }

    /**
     * {@inheritDoc}
     */
    public function getByCreditMemoId($creditMemoId) {
        $collection = $this->collectionFactory->create()
        ->addFieldToFilter(RefundInterface::KEY_CREDITMEMO_ID, $creditMemoId);

        return $collection->getFirstItem();
    }
}
