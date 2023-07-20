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



namespace Mirasvit\Rewards\Observer\Refund;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Creditmemo;
use Mirasvit\Rewards\Api\Repository\RefundRepositoryInterface;
use Mirasvit\Rewards\Model\RefundFactory;
use Mirasvit\Rewards\Service\Order\Transaction\RefundEarnedPoints;

class CreditmemoSaveAfter implements ObserverInterface
{
    private $refundRepository;

    private $refundFactory;

    private $refundEarnedPoints;

    public function __construct(
        RefundEarnedPoints $refundEarnedPoints,
        RefundRepositoryInterface $refundRepository,
        RefundFactory $refundFactory
    ) {
        $this->refundEarnedPoints = $refundEarnedPoints;
        $this->refundRepository   = $refundRepository;
        $this->refundFactory      = $refundFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getData('creditmemo');

        $orderId      = $creditmemo->getOrder()->getId();
        $invoiceId    = $creditmemo->getInvoice() ? $creditmemo->getInvoice()->getId() : 0;
        $creditmemoId = $creditmemo->getId();

        if (!$this->refundRepository->getByCreditmemoId($creditmemoId) && $creditmemo->getRewardsRefundedPoints() > 0) {
            $refund = $this->refundFactory->create();
            $refund->setOrderId($orderId)
                ->setInvoiceId($invoiceId)
                ->setCreditmemoId($creditmemoId)
                ->setRefundedPoints($creditmemo->getRewardsRefundedPoints())
                ->setBaseRefunded($creditmemo->getRewardsBaseRefunded())
                ->setRefunded($creditmemo->getRewardsRefunded());

            try {
                $this->refundRepository->save($refund);
            } catch (\Exception $e) {
                return;
            }

            $this->refundEarnedPoints->createTransaction($creditmemo, $creditmemo->getRewardsRefundedPoints());
        }
    }
}
