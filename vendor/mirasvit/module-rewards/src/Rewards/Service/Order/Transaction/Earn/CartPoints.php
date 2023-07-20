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



namespace Mirasvit\Rewards\Service\Order\Transaction\Earn;

/**
 * Adds order's cart earned points to customer account
 */
class CartPoints
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
     * Calculates and adds points, based on order items and subtotal.
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return int|false
     */
    public function add($order)
    {
        if ($this->transactionService->getEarnedPointsTransaction($order) ||
            !$order->getCustomerId()
        ) {
            return false;
        }

        $purchase = $this->rewardsPurchase->getByOrder($order);
        if (!$purchase || $purchase->getEarnPoints() <= 0) {
            return false;
        }
        $totalPoints = $purchase->getEarnPoints();
        $this->rewardsData->setCurrentStore($order->getStore());
        $this->rewardsBalance->changePointsBalance(
            $order->getCustomerId(),
            $totalPoints,
            $this->transactionService->translateComment(
                $order->getStore()->getId(),
                'Earned %1 for the order #%2.',
                $this->rewardsData->formatPoints($totalPoints),
                $order->getIncrementId()
            ),
            true, 'order_earn-'.$order->getId(), true);

        return $totalPoints;
    }
}