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



namespace Mirasvit\RewardsBehavior\Block\Buttons;

/**
 * Class Referral
 *
 * Displays referral button
 *
 * @package Mirasvit\RewardsBehavior\Block\Buttons
 */
class Referral extends \Mirasvit\RewardsBehavior\Block\Buttons\AbstractButtons
{
    /**
     * @var \Mirasvit\Rewards\Helper\Behavior
     */
    private $rewardsBehavior;

    public function __construct(
        \Mirasvit\Rewards\Helper\Behavior $rewardsBehavior,
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->rewardsBehavior = $rewardsBehavior;
        parent::__construct($config, $registry, $customerFactory, $customerSession, $productFactory, $context, $data);
    }

    /**
     * @return bool|int
     */
    public function getEstimatedEarnPoints()
    {
        $url = $this->getCurrentUrl();
        $websiteId = $this->context->getStoreManager()->getWebsite()->getId();

        return $this->rewardsBehavior->getEstimatedEarnPoints(
            \Mirasvit\Rewards\Model\Config::BEHAVIOR_TRIGGER_TWITTER_TWEET, $this->_getCustomer(), $websiteId, $url
        );
    }
}
