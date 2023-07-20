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



namespace Mirasvit\RewardsBehavior\Block\Buttons\Facebook;

/**
 * Class Share
 *
 * Displays fb share button
 *
 * @package Mirasvit\RewardsBehavior\Block\Buttons\Facebook
 */
class Share extends \Mirasvit\RewardsBehavior\Block\Buttons\Facebook\Like
{
    /**
     * @return bool|int
     */
    public function getEstimatedEarnPoints()
    {
        $url = $this->getCurrentUrl();
        $websiteId = $this->context->getStoreManager()->getWebsite()->getId();
        return $this->rewardsBehavior->getEstimatedEarnPoints(
            \Mirasvit\Rewards\Model\Config::BEHAVIOR_TRIGGER_FACEBOOK_SHARE, $this->_getCustomer(), $websiteId, $url
        );
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getConfig()->getFacebookShowShare();
    }
}
