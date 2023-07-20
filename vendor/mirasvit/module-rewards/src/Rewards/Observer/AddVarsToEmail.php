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



namespace Mirasvit\Rewards\Observer;

use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Rewards\Helper\Purchase;
use Mirasvit\Rewards\Helper\Referral;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Rewards\Service\Customer\Tier as TierService;

class AddVarsToEmail implements ObserverInterface
{
    private $purchase;

    private $referral;

    private $storeManager;

    private $tierService;

    public function __construct(
        Purchase $purchase,
        Referral $referral,
        StoreManagerInterface $storeManager,
        TierService $tierService
    ) {
        $this->purchase     = $purchase;
        $this->referral     = $referral;
        $this->storeManager = $storeManager;
        $this->tierService  = $tierService;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__ . ':' . __METHOD__);
        $variables = $observer->getEvent()->getTransport();
        if (is_object($variables)) {
            $variables = $variables->getData();
        }

        if (isset($variables['order']) && $variables['order'] instanceof \Magento\Sales\Api\Data\OrderInterface) {
            /** @var \Magento\Sales\Api\Data\OrderInterface $order */
            $order = $variables['order'];
        } else {
            \Magento\Framework\Profiler::stop(__CLASS__ . ':' . __METHOD__);

            return;
        }

        $purchase = $this->purchase->getByOrder($order);
        if (!$purchase) {
            return;
        }

        $order->setRewardsEarnedPoints($purchase->getEarnPoints());
        $order->setRewardsSpentPoints($purchase->getSpendPoints());
        $order->setRewardsSpentAmount($purchase->getSpendAmount());

        if (!$order->getCustomerIsGuest()) {
            $order->setInvitationLink(
                $this->storeManager->getStore($order->getStoreId())->getBaseUrl() . 'r/'
                . $this->referral->getReferralLinkId($order->getCustomerId())
            );
            if ($this->tierService->getCustomerTier($order->getCustomerId())) {
                $order->setCustomerTier($this->tierService->getCustomerTier($order->getCustomerId())->getName());
            }
        }
        \Magento\Framework\Profiler::stop(__CLASS__ . ':' . __METHOD__);
    }
}
