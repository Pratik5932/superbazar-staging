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
use Mirasvit\Rewards\Api\Service\RefundServiceInterface;
use Mirasvit\Rewards\Helper\Calculation;
use Mirasvit\Rewards\Model\Config;

class CreditmemoRefund implements ObserverInterface
{
    private $cancelEarnedPoints;

    private $restoreSpentPoints;

    private $config;

    private $refundService;

    public function __construct(
        \Mirasvit\Rewards\Service\Order\Transaction\CancelEarnedPoints $cancelEarnedPoints,
        \Mirasvit\Rewards\Service\Order\Transaction\RestoreSpentPoints $restoreSpentPoints,
        RefundServiceInterface $refundService,
        Config $config
    ) {
        $this->config             = $config;
        $this->refundService      = $refundService;
        $this->cancelEarnedPoints = $cancelEarnedPoints;
        $this->restoreSpentPoints = $restoreSpentPoints;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Currency_Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if (!$creditmemo) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }
        /** @var \Magento\Sales\Model\Order $order */
        $order = $creditmemo->getOrder();
        if ($this->config->getGeneralIsCancelAfterRefund()) {
            $this->cancelEarnedPoints->createTransaction($order, $creditmemo);
        }

        if ($this->config->getGeneralIsRestoreAfterRefund()) {
            $this->restoreSpentPoints->createTransaction($order, $creditmemo);
        }
        $refundInfo = $this->refundService->getByOrderId($order->getId());
        if (!$creditmemo->getId()) {
            $refundInfo->setRefundedPointsSum($refundInfo->getRefundedPointsSum() + $creditmemo->getRewardsRefundedPoints());
            $refundInfo->setBaseRefundedSum($refundInfo->getBaseRefundedSum() + $creditmemo->getRewardsBaseRefunded());
            $refundInfo->setRefundedSum($refundInfo->getRefundedSum() + $creditmemo->getRewardsRefunded());
        }
        if ($refundInfo->getBaseRefundedSum() > 0) {
            $leftToRefund = $order->getBaseTotalInvoiced() - $order->getBaseTotalOfflineRefunded() -
                $order->getBaseTotalOnlineRefunded() - $refundInfo->getBaseRefundedSum();
            // due to math errors in php we use with 0.00099
            if ($leftToRefund <= Calculation::ZERO_VALUE) {
                $order->setForcedCanCreditmemo(false);
            }
        } elseif ($order->getBaseTotalInvoiced() == $creditmemo->getRewardsBaseRefunded()) {
            // when we totally refund creditmemo refundInfo does not exist because DB transaction is not completed yet
            $order->setForcedCanCreditmemo(false);
        }

        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
        return;
    }
}
