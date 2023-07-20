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

use Magento\Framework\Exception\LocalizedException;

class LockPurchaseOnOrderPlaceBefore implements \Magento\Framework\Event\ObserverInterface
{
    const LOCK_QUOTE_TIME = 5;

    private $rewardsPurchase;

    public function __construct(
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase
    ) {
        $this->rewardsPurchase = $rewardsPurchase;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        /** @var \Mirasvit\Rewards\Model\Purchase $purchase */
        $purchase = $this->rewardsPurchase->getByQuote($order->getQuoteId());
        if (!$purchase) {
            return;
        }
        if (
            $order->getPayment() && strpos($order->getPayment()->getMethod(), 'sagepay') === false &&
            (time() - self::LOCK_QUOTE_TIME) <= strtotime((string) $purchase->getLockQuote()) &&
            !$purchase->getQuote()->getIsMultiShipping()
        ) {
            throw new LocalizedException(
                __('Rewards error occurred on the server. Please try to place the order again.')
            );
        }
        $purchase->setLockQuote(time())->save();
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
