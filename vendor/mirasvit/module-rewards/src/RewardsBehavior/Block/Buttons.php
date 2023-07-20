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



namespace Mirasvit\RewardsBehavior\Block;

/**
 * Class Buttons
 *
 * Container block for social buttons
 *
 * @package Mirasvit\Rewards\Block
 */
class Buttons extends \Mirasvit\RewardsBehavior\Block\Buttons\AbstractButtons
{
    /**
     * @var \Mirasvit\Rewards\Model\Config
     */
    protected $rewardsConfig;
    /**
     * @var \Mirasvit\Rewards\Helper\Behavior
     */
    protected $rewardsBehavior;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext,
        \Mirasvit\Rewards\Helper\Behavior $rewardsBehavior,
        \Mirasvit\Rewards\Model\Config $rewardsConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $rewardsConfig, $registry, $customerFactory, $customerSession, $productFactory, $context, $data
        );

        $this->rewardsConfig   = $rewardsConfig;
        $this->rewardsBehavior = $rewardsBehavior;
        $this->registry        = $registry;
        $this->context         = $context;
        $this->httpContext     = $httpContext;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $layout = $this->getLayout();
        $facebook = $layout->createBlock(
            '\Mirasvit\RewardsBehavior\Block\Buttons\Facebook\Like')->setTemplate('buttons/facebook/like.phtml'
        );
        $facebookShare = $layout->createBlock(
            '\Mirasvit\RewardsBehavior\Block\Buttons\Facebook\Share')->setTemplate('buttons/facebook/share.phtml'
        );
        $twitter = $layout->createBlock(
            '\Mirasvit\RewardsBehavior\Block\Buttons\Twitter\Tweet')->setTemplate('buttons/twitter/tweet.phtml'
        );
        $pinterest = $layout->createBlock(
            '\Mirasvit\RewardsBehavior\Block\Buttons\Pinterest\Pin')->setTemplate('buttons/pinterest/pin.phtml'
        );
        $referral = $layout->createBlock(
            '\Mirasvit\RewardsBehavior\Block\Buttons\Referral')->setTemplate('buttons/referral.phtml'
        );

        $this->setChild('facebook.like', $facebook);
        $this->setChild('facebook.share', $facebookShare);
        $this->setChild('twitter.tweet', $twitter);
        $this->setChild('pinterest.pin', $pinterest);
        $this->setChild('referral', $referral);
    }

    /**
     * @return int
     */
    public function getEstimatedEarnPoints()
    {
        $url = $this->getCurrentUrl();
        $websiteId = $this->context->getStoreManager()->getWebsite()->getId();
        return $this->rewardsBehavior->getEstimatedEarnPoints(
                \Mirasvit\Rewards\Model\Config::BEHAVIOR_TRIGGER_FACEBOOK_LIKE, $this->_getCustomer(), $websiteId, $url
            ) + $this->rewardsBehavior->getEstimatedEarnPoints(
                \Mirasvit\Rewards\Model\Config::BEHAVIOR_TRIGGER_TWITTER_TWEET, $this->_getCustomer(), $websiteId, $url
            ) + $this->rewardsBehavior->getEstimatedEarnPoints(
                \Mirasvit\Rewards\Model\Config::BEHAVIOR_TRIGGER_FACEBOOK_SHARE, $this->_getCustomer(), $websiteId, $url
            ) + $this->rewardsBehavior->getEstimatedEarnPoints(
                \Mirasvit\Rewards\Model\Config::BEHAVIOR_TRIGGER_PINTEREST_PIN, $this->_getCustomer(), $websiteId, $url
            ) + $this->rewardsBehavior->getEstimatedEarnPoints(
                \Mirasvit\Rewards\Model\Config::BEHAVIOR_TRIGGER_SEND_LINK, $this->_getCustomer(), $websiteId, $url
            );
    }

    /**
     * @return bool
     */
    public function isShareActive()
    {
        return $this->getConfig()->getFacebookShowShare();
    }

    /**
     * @return bool
     */
    public function isLikeActive()
    {
        return $this->getConfig()->getFacebookIsActive();
    }

    /**
     * @return bool
     */
    public function isTweetActive()
    {
        return $this->getConfig()->getTwitterIsActive();
    }

    /**
     * @return bool
     */
    public function isPinActive()
    {
        return ($this->context->getRequest()->getActionName() == 'view'
                && $this->context->getRequest()->getControllerName() == 'product'
            ) && $this->getConfig()->getPinterestIsActive();
    }

    /**
     * @return bool
     */
    public function isReferralActive()
    {
        return $this->rewardsConfig->getReferralIsActive();
    }

    /**
     * @return bool
     */
    public function isAddthisActive()
    {
        return $this->rewardsConfig->getAddthisIsActive();
    }

    /**
     * @return string
     */
    public function getAddthisCode()
    {
        return $this->rewardsConfig->getAddthisCode();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isLikeActive() || $this->isTweetActive() || $this->isReferralActive()
            || $this->isPinActive() || $this->isAddthisActive();
    }

    /**
     * @return string
     */
    public function getShareUrl()
    {
        return $this->getUrl('rewards_behavior/facebook/share');
    }

    /**
     * @return string
     */
    public function getLikeUrl()
    {
        return $this->getUrl('rewards_behavior/facebook/like');
    }

    /**
     * @return string
     */
    public function getUnlikeUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('rewards_behavior/facebook/unlike');
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->getConfig()->getFacebookAppId();
    }

    /**
     * @return string
     */
    public function isAuthorized()
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * As this block uses to include Facebook init scripts on page we should check if Facebook buttons are enabled
     * @return bool
     */
    public function isShowOnProductPage()
    {
        if ($this->getCurrentPage() == 'product') {
            return $this->isLikeActive() || $this->isShareActive();
        }

        return true;
    }
}
