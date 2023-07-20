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


namespace Mirasvit\RewardsBehavior\Plugin;

use Mirasvit\Affiliate\Api\Data\AccountInterface;
use Mirasvit\Affiliate\Service\AccountService;
use Mirasvit\Rewards\Model\Config;

/**
 * Reward customers after joining affiliate program provided by Mirasvit Affiliate extension.
 *
 * @package Mirasvit\RewardsBehavior\Plugin\Affiliate
 */
class EarnAfterMirasvitAffiliateCreateAccount
{
    /**
     * @var \Mirasvit\Rewards\Helper\BehaviorRule
     */
    private $rewardsBehavior;

    public function __construct(
        \Mirasvit\Rewards\Helper\BehaviorRule $rewardsBehavior
    ) {
        $this->rewardsBehavior = $rewardsBehavior;
    }

    /**
     * @param AccountService $accountService
     * @param AccountInterface $account
     * @return AccountInterface
     */
    public function afterCreateAccount(AccountService $accountService, AccountInterface $account)
    {
        $this->rewardsBehavior->processRule(Config::BEHAVIOR_TRIGGER_AFFILIATE_CREATE, $account->getCustomerId());

        return $account;
    }
}