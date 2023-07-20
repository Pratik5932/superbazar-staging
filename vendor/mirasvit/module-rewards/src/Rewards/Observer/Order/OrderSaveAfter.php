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



namespace Mirasvit\Rewards\Observer\Order;

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    private $config;
    private $customerTierService;
    private $earnBehaviorPoints;
    private $earnCartPoints;
    private $earnReferralPoints;
    private $orderService;
    private $restoreSpentPoints;

    public function __construct(
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Service\Customer\Tier $customerTierService,
        \Mirasvit\Rewards\Service\Order $orderService,
        \Mirasvit\Rewards\Service\Order\Transaction\Earn\BehaviorPoints $earnBehaviorPoints,
        \Mirasvit\Rewards\Service\Order\Transaction\Earn\CartPoints $earnCartPoints,
        \Mirasvit\Rewards\Service\Order\Transaction\Earn\ReferralPoints $earnReferralPoints,
        \Mirasvit\Rewards\Service\Order\Transaction\RestoreSpentPoints $restoreSpentPoints
    ) {
        $this->config              = $config;
        $this->customerTierService = $customerTierService;
        $this->earnBehaviorPoints  = $earnBehaviorPoints;
        $this->earnCartPoints      = $earnCartPoints;
        $this->earnReferralPoints  = $earnReferralPoints;
        $this->orderService        = $orderService;
        $this->restoreSpentPoints  = $restoreSpentPoints;
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
        if (!$order) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }
        $status = $order->getStatus();

        if (\Magento\Sales\Model\Order::STATE_COMPLETE == $status && $order->getCustomerId()) {
            $this->customerTierService->updateCustomerTier($order->getCustomerId());
        }
        if (in_array($status, $this->config->getGeneralEarnInStatuses())) {
            $this->earnCartPoints->add($order);
            $this->earnBehaviorPoints->add($order);
            $this->earnReferralPoints->add($order);
        }

        /** compatibility with PSP MultiSafepay. They do not call order cancel event */
        if (\Magento\Sales\Model\Order::STATE_CANCELED == $status && !$this->orderService->isLocked($order)) {
            $this->orderService->lock($order);
            if ($order->getCustomerId()) {
                $this->restoreSpentPoints->createTransaction($order);
            }
        }
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
