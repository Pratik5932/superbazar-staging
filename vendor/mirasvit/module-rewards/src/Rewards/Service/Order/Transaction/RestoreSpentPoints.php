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

class RestoreSpentPoints
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
     * @param \Magento\Sales\Model\Order                 $order
     * @param \Magento\Sales\Model\Order\Creditmemo|bool $creditMemo
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Currency_Exception
     *
     */
    public function createTransaction($order, $creditMemo = false)
    {
        if (!$spendTransaction = $this->transactionService->getSpendPointsTransaction($order)) {
            return;
        }
        if ($creditMemo) { //if we create a credit memo
            $creditMemoId = $order->getCreditmemosCollection()->count();
            $proportion = $creditMemo->getSubtotal() / $order->getSubtotal();
            if ($proportion > 1) {
                $proportion = 1;
            }
            $totalPoints = round($spendTransaction->getAmount() * $proportion);
            // fix rounding error
            $restoredPoints = $this->transactionService->getSumSpendPointsRestored($order);
            if ($restoredPoints + abs($totalPoints) > abs($spendTransaction->getAmount())) {
                $totalPoints = (abs($spendTransaction->getAmount()) - $restoredPoints) * -1;
            }
        } else { //if we cancel order via backend
            $creditMemoId = 0;
            $totalPoints  = $spendTransaction->getAmount();
        }

        $this->rewardsData->setCurrentStore($order->getStore());
        $this->rewardsBalance->changePointsBalance($order->getCustomerId(), -$totalPoints,
            $this->transactionService->translateComment(
                $order->getStore()->getId(),
                'Restore spent %1 for the order #%2.',
                $this->rewardsData->formatPoints($totalPoints),
                $order->getIncrementId()
            ),
            false, 'order_spend_restore-'.$order->getId().'-'.$creditMemoId, false);
    }
}
