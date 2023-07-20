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

use Magento\Framework\Module\Manager;
use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Rewards\Model\Config;
use Magento\Store\Model\StoreFactory;
use Magento\Customer\Model\CustomerFactory;
use Mirasvit\Rewards\Helper\BehaviorRule;

class EarnOnAheadworksReviewSubmit implements ObserverInterface
{
    protected $moduleManager;

    protected $storeFactory;

    protected $behaviorRuleHelper;

    protected $customerFactory;

    public function __construct(
        Manager $moduleManager,
        StoreFactory $storeFactory,
        CustomerFactory $customerFactory,
        BehaviorRule $behaviorRuleHelper
    ) {
        $this->moduleManager      = $moduleManager;
        $this->storeFactory       = $storeFactory;
        $this->behaviorRuleHelper = $behaviorRuleHelper;
        $this->customerFactory    = $customerFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__ . ':' . __METHOD__);

        if ($this->moduleManager->isEnabled('Aheadworks_AdvancedReviews')) {
            $review = $observer->getEvent()->getObject();

            $customer               = $this->customerFactory->create()->load($review->getCustomerId());
            $websiteId              = $this->storeFactory->create()->load($review->getStoreId())->getWebsiteId();
            $validateReviewRuleOnly = true;

            if ($review->getStatus() == \Aheadworks\AdvancedReviews\Model\Source\Review\Status::APPROVED && $review->getCustomerId()) {
                $validateReviewRuleOnly = false;
            }

            $this->behaviorRuleHelper->processRule(Config::BEHAVIOR_TRIGGER_REVIEW,
                $customer, $websiteId, $review->getId(), [], $validateReviewRuleOnly);
        }

        \Magento\Framework\Profiler::stop(__CLASS__ . ':' . __METHOD__);
    }
}
