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

class EarnOnInvoiceSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    private $config;
    private $earnBehaviorPoints;
    private $earnCartPoints;
    private $earnReferralPoints;

    public function __construct(
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Service\Order\Transaction\Earn\BehaviorPoints $earnBehaviorPoints,
        \Mirasvit\Rewards\Service\Order\Transaction\Earn\CartPoints $earnCartPoints,
        \Mirasvit\Rewards\Service\Order\Transaction\Earn\ReferralPoints $earnReferralPoints
    ) {
        $this->config             = $config;
        $this->earnBehaviorPoints = $earnBehaviorPoints;
        $this->earnCartPoints     = $earnCartPoints;
        $this->earnReferralPoints = $earnReferralPoints;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        if ($invoice->getState() != \Magento\Sales\Model\Order\Invoice::STATE_PAID) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }

        if ($order && $this->config->getGeneralIsEarnAfterInvoice()) {
            $this->earnCartPoints->add($order);
            $this->earnBehaviorPoints->add($order);
            $this->earnReferralPoints->add($order);
        }
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
