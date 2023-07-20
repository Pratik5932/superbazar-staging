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

class ShowNotificationOnCheckoutSuccess implements \Magento\Framework\Event\ObserverInterface
{
    private $messageManager;
    private $orderFactory;
    private $rewardsData;
    private $rewardsPurchase;
    private $typeOnepage;

    public function __construct(
        \Magento\Checkout\Model\Type\Onepage $typeOnepage,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->messageManager  = $messageManager;
        $this->orderFactory    = $orderFactory;
        $this->rewardsData     = $rewardsData;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->typeOnepage     = $typeOnepage;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $session = $this->typeOnepage->getCheckout();
        $orderId = $session->getLastOrderId();
        if (!$session->getLastSuccessQuoteId() || !$orderId) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }
        $order = $this->orderFactory->create()->load($orderId);
        $this->addPointsNotifications($order);
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return void
     */
    public function addPointsNotifications($order)
    {
        if (!$order->getCustomerId()) {
            return;
        }

        $purchase = $this->rewardsPurchase->getByOrder($order);
        $totalSpendPoints = $purchase->getPointsNumber();
        $totalEarnedPoints = $purchase->getEarnPoints();

        if ($totalEarnedPoints && $totalSpendPoints) {
            $this->addNotificationMessage(__('You earned %1 and spent %2 for this order.',
                $this->rewardsData->formatPoints($totalEarnedPoints),
                $this->rewardsData->formatPoints($totalSpendPoints)));
        } elseif ($totalSpendPoints) {
            $this->addNotificationMessage(__('You spent %1 for this order.',
                $this->rewardsData->formatPoints($totalSpendPoints)));
        } elseif ($totalEarnedPoints) {
            $this->addNotificationMessage(__('You earned %1 for this order.',
                $this->rewardsData->formatPoints($totalEarnedPoints)));
        }
        if ($totalEarnedPoints) {
            $this->addNotificationMessage(
                __('Earned points will be enrolled to your account after we finish processing your order.')
            );
        }
    }

    /**
     * @param string $message
     *
     * @return void
     */
    private function addNotificationMessage($message)
    {
        $this->messageManager->addSuccessMessage($message);
    }
}
