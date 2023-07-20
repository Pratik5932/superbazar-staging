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


namespace Mirasvit\Rewards\Helper\Balance;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreFactory;
use Mirasvit\Rewards\Helper\BehaviorRule;
use Mirasvit\Rewards\Helper\Referral;
use Mirasvit\Rewards\Model\Config;

class EarnBehaviorOrderPoints
{
    private $rewardsBehavior;
    private $rewardsReferral;
    private $storeFactory;

    public function __construct(
        StoreFactory $storeFactory,
        BehaviorRule $rewardsBehavior,
        Referral $rewardsReferral
    ) {
        $this->storeFactory    = $storeFactory;
        $this->rewardsBehavior = $rewardsBehavior;
        $this->rewardsReferral = $rewardsReferral;
    }

    /**
     * Behavior "create order" event
     * @param Order|OrderInterface $order
     *
     * @return void
     */
    public function earnBehaviorOrderPoints($order)
    {
        if ($order->getCustomerId()) {
            $this->rewardsBehavior->processRule(
                Config::BEHAVIOR_TRIGGER_CUSTOMER_ORDER,
                $order->getCustomerId(),
                $this->storeFactory->create()->load($order->getStoreId())->getWebsiteId(),
                $order->getId(),
                ['order' => $order]
            );
        }

        $this->rewardsReferral->processReferralOrder($order);
    }

}
