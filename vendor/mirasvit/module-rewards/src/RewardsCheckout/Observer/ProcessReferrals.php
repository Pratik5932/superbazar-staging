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

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Sales\Model\Order;
use Mirasvit\Rewards\Helper\Balance\EarnBehaviorOrderPoints;
use Mirasvit\Rewards\Model\ReferralFactory;

class ProcessReferrals implements \Magento\Framework\Event\ObserverInterface
{
    private $checkoutSession;

    private $earnBehaviorOrderPoints;

    private $referralFactory;

    private $sessionManager;

    public function __construct(
        CheckoutSession $checkoutSession,
        EarnBehaviorOrderPoints $earnBehaviorOrderPoints,
        ReferralFactory $referralFactory,
        SessionManagerInterface $sessionManager
    ) {
        $this->checkoutSession         = $checkoutSession;
        $this->earnBehaviorOrderPoints = $earnBehaviorOrderPoints;
        $this->referralFactory         = $referralFactory;
        $this->sessionManager          = $sessionManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);

        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($order && $order->getId()) {
            $referral = null;
            if ($id = (int)$this->sessionManager->getReferral()) {
                $referral = $this->referralFactory->create()->load($id);
            }

            if ($id = (int)$this->checkoutSession->getReferral()) {
                $referral = $this->referralFactory->create()->load($id);
            }

            if ($referral) {
                $referral->setQuoteId($order->getQuoteId())
                    ->save();
            }
        }

        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
