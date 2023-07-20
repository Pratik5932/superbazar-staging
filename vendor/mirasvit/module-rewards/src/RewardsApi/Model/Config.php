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



namespace Mirasvit\RewardsApi\Model;

use Magento\Store\Model\ScopeInterface;

class Config extends \Mirasvit\Rewards\Model\Config
{
    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     */
    public function getReferralInvitationEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/referral/api_invitation_email_template',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
