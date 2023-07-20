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



namespace Mirasvit\Rewards\Service\Order\Transaction;

class CancelEarnedPoints
{
    private $rewardsBalance;

    private $rewardsData;

    private $transactionService;

    public function __construct(
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Mirasvit\Rewards\Service\Order\Transaction $transactionService
    ) {
        $this->rewardsBalance     = $rewardsBalance;
        $this->rewardsData        = $rewardsData;
        $this->transactionService = $transactionService;
    }

    /**
     * Cancels earned points.
     *
     * @param \Magento\Sales\Model\Order                  $order
     * @param \Magento\Sales\Model\Order\Creditmemo|false $creditMemo
     *
     * @return void
     */
    public function createTransaction($order, $creditMemo)
    {
        $code = 'customer_order-' . $order->getId();
        $earnedBehaviorTransaction = $this->transactionService->getEarnedBehaviorTransaction($code);

        if (!($earnedTransaction = $this->transactionService->getEarnedPointsTransaction($order))
        && !($earnedBehaviorTransaction))
        {
            return;
        }

        $proportion = 1;
        if ($creditMemo) {
            if ($order->getSubtotal() > 0) {
                $proportion = $creditMemo->getSubtotal() / $order->getSubtotal();
            } else { // for zero orders with earning points
                $proportion = $this->getCreditmemoItemsQty($creditMemo) / $this->getCreditmemoOrderItemsQty($creditMemo);
            }
            if ($proportion > 1) {
                $proportion = 1;
            }
        }
        $this->rewardsData->setCurrentStore($order->getStore());

        if ($earnedTransaction) {
            $this->changeBalance($order, $earnedTransaction, $proportion);
        }

        if ($earnedBehaviorTransaction) {
            foreach ($earnedBehaviorTransaction as $transaction) {
                $this->changeBalance($order, $transaction, $proportion);
            }
        }
    }

    /**
     * Cancels earned points.
     *
     * @param \Magento\Sales\Model\Order          $order
     * @param \Mirasvit\Rewards\Model\Transaction $transaction
     * @param float                               $proportion
     *
     * @return void
     */
    protected function changeBalance($order, $transaction, $proportion)
    {
        $creditMemoId        = $order->getCreditmemosCollection()->count();
        $totalPoints         = round($transaction->getAmount() * $proportion);
        $totalPointsFormated = $this->rewardsData->formatPoints($totalPoints);
        $translatedComment   = $this->transactionService->translateComment(
            $order->getStore()->getId(),
            'Cancel earned %1 for the order #%2.',
            $totalPointsFormated,
            $order->getIncrementId()
        );

        $this->rewardsBalance->changePointsBalance(
            $order->getCustomerId(), -$totalPoints, $translatedComment, false,
            'order_earn_cancel-' . $order->getId() . '-' . $creditMemoId, false
        );
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return int
     */
    public function getCreditmemoItemsQty($creditmemo)
    {
        $itemsRefunding = 0;
        foreach ($creditmemo->getItems() as $item) {
            if ($item->getOrderItem()->getParentItem()) {
                continue;
            }
            $itemsRefunding += $item->getQty();
        }

        return $itemsRefunding;
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return int
     */
    public function getCreditmemoOrderItemsQty($creditmemo)
    {
        $itemsRefunding = 0;

        $order = $creditmemo->getOrder();
        $items = $order->getItemsCollection();
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($items as $item) {
            $itemsRefunding += $item->getQtyInvoiced();
        }

        return $itemsRefunding;
    }
}
