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
 * Adds order's referral earned points to customer account
 */
class ReferralPoints
{
    private $rewardsReferral;

    public function __construct(
        \Mirasvit\Rewards\Helper\Referral $rewardsReferral
    ) {
        $this->rewardsReferral = $rewardsReferral;
    }


    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return void
     */
    public function add($order)
    {
        $this->rewardsReferral->processReferralOrder($order);
    }
}