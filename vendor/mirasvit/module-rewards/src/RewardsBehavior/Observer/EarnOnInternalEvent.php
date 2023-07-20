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



namespace Mirasvit\RewardsBehavior\Observer;

use Magento\Framework\Event\ObserverInterface;

class EarnOnInternalEvent implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Mirasvit\Rewards\Helper\BehaviorRule
     */
    private $rewardsBehavior;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Mirasvit\Rewards\Helper\BehaviorRule $rewardsBehavior
    ) {
        $this->messageManager  = $messageManager;
        $this->customerSession = $customerSession;
        $this->rewardsBehavior = $rewardsBehavior;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        if (!$this->customerSession->getCustomerId()) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }

        $transaction = $this->rewardsBehavior->processRule(
            $observer->getEvent()->getCode(),
            $this->customerSession->getCustomer()->getId(),
            false,
            $observer->getEvent()->getAttr()
        );
        if ($transaction && $transaction->getComment()) {
            $this->messageManager->addSuccess($transaction->getComment());
        }
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
