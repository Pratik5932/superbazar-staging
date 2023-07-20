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

/**
 * Deduct order's spent points from customer account
 */
class DeductSpentPoints
{
    private $rewardsBalance;

    private $rewardsData;

    private $rewardsPurchase;

    private $transactionService;

    public function __construct(
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Service\Order\Transaction $transactionService
    ) {
        $this->rewardsBalance     = $rewardsBalance;
        $this->rewardsData        = $rewardsData;
        $this->rewardsPurchase    = $rewardsPurchase;
        $this->transactionService = $transactionService;
    }

    /**
     * Decreases the number of points on the customer account.
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return int
     */
    public function createTransaction($order)
    {
        if (!$purchase = $this->rewardsPurchase->getByOrder($order)) {
            return 0;
        }
        if ($totalPoints = $purchase->getSpendPoints()) {
            $this->rewardsData->setCurrentStore($order->getStore());
            $this->rewardsBalance->changePointsBalance($order->getCustomerId(), -$totalPoints,
                $this->transactionService->translateComment(
                    $order->getStore()->getId(),
                    'Spent %1 for the order #%2.',
                    $this->rewardsData->formatPoints($totalPoints),
                    $order->getIncrementId()
                ),
                false, 'order_spend-'.$order->getId(), true);

            return $totalPoints;
        }
        return 0;
    }
}
