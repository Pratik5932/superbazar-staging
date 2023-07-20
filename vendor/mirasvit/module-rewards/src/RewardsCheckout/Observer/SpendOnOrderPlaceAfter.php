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

class SpendOnOrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    private $deductSpentPoints;
    private $orderService;
    private $rewardsPurchase;

    public function __construct(
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Service\Order $orderService,
        \Mirasvit\Rewards\Service\Order\Transaction\DeductSpentPoints $deductSpentPoints
    ) {
        $this->deductSpentPoints = $deductSpentPoints;
        $this->orderService      = $orderService;
        $this->rewardsPurchase   = $rewardsPurchase;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($order && $order->getId()) {
            $purchase = $this->rewardsPurchase->getByQuote($order->getQuoteId());
            if (!$purchase) {
                return;
            }
//            $this->refreshPoints($purchase->getQuote(), true, true);
            if (!$this->orderService->isLocked($order)) {
                $this->orderService->lock($order);
                if ($order->getCustomerId()) {
                    $this->deductSpentPoints->createTransaction($order);
                    //we should additionally set order_id for the compatibility with some checkouts which create several quotes
                    $purchase->setData('order_id', $order->getId())->save();
                }
            }
        }
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
