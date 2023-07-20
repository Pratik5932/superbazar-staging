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



namespace Mirasvit\RewardsCheckout\Observer;

use Mirasvit\Rewards\Service\Order;
use Mirasvit\Rewards\Service\Order\Transaction\CancelEarnedPoints;
use Mirasvit\Rewards\Service\Order\Transaction\RestoreSpentPoints;
use Mirasvit\Rewards\Helper\Purchase;

class RestorePointsOnOrderCancelAfter implements \Magento\Framework\Event\ObserverInterface
{
    private $rewardsPurchase;

    private $cancelEarnedPoints;

    private $orderService;

    private $restoreSpentPoints;

    public function __construct(
        Order $orderService,
        CancelEarnedPoints $cancelEarnedPoints,
        RestoreSpentPoints $restoreSpentPoints,
        Purchase $rewardsPurchase
    ) {
        $this->cancelEarnedPoints = $cancelEarnedPoints;
        $this->orderService       = $orderService;
        $this->restoreSpentPoints = $restoreSpentPoints;
        $this->rewardsPurchase    = $rewardsPurchase;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__ . ':' . __METHOD__);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if (!$this->orderService->isLocked($order)) {
            $this->orderService->lock($order);
            $purchase = $this->getPurchase($order);
            if ($order->getCustomerId()) {
                $this->restoreSpentPoints->createTransaction($order);
                $this->cancelEarnedPoints->createTransaction($order, false);
                // We should delete the rewards purchase as some payment methods use the quote of the canceled orders for the next purchase
                if ($purchase) {
                    $purchase->setQuoteId(0)->save();
                }
            }
        }
        \Magento\Framework\Profiler::stop(__CLASS__ . ':' . __METHOD__);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return bool|\Mirasvit\Rewards\Model\Purchase
     */
    protected function getPurchase($order)
    {
        $purchase = $this->rewardsPurchase->getByOrder($order);

        return $purchase;
    }

}
