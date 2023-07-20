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

class RefundEarnedPoints
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
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param int                                   $points
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Currency_Exception
     */
    public function createTransaction($creditmemo, $points)
    {
        if ($this->transactionService->getEarnedRefundedPointsTransaction($creditmemo)) {
            return;
        }
        $order = $creditmemo->getOrder();
        $this->rewardsData->setCurrentStore($order->getStore());
        $this->rewardsBalance->changePointsBalance($order->getCustomerId(), $points,
            $this->transactionService->translateComment(
                $order->getStore()->getId(),
                'Refunded %1 (creditmemo #%2, order #%3).',
                $this->rewardsData->formatPoints($points),
                $creditmemo->getIncrementId(),
                $order->getIncrementId()
            ),
            false, 'order_refund-'.$order->getId().'-'.$creditmemo->getId(), true);
    }
}
