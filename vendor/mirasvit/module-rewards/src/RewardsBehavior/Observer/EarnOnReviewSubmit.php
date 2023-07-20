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
use Mirasvit\Rewards\Model\Config;
use Magento\Store\Model\StoreFactory;
use Magento\Customer\Model\CustomerFactory;
use Mirasvit\Rewards\Helper\BehaviorRule;
use Magento\Catalog\Model\ProductFactory;

class EarnOnReviewSubmit implements ObserverInterface
{
    protected $storeFactory;

    protected $behaviorRuleHelper;

    protected $customerFactory;

    protected $productFactory;

    public function __construct(
        StoreFactory    $storeFactory,
        CustomerFactory $customerFactory,
        BehaviorRule    $behaviorRuleHelper,
        ProductFactory  $productFactory
    ) {
        $this->storeFactory       = $storeFactory;
        $this->behaviorRuleHelper = $behaviorRuleHelper;
        $this->customerFactory    = $customerFactory;
        $this->productFactory     = $productFactory;
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
        /** @var \Magento\Review\Model\Review $review */
        $review = $observer->getEvent()->getObject();

        $customer  = $this->customerFactory->create()->load($review->getCustomerId());
        $websiteId = $this->storeFactory->create()->load($review->getStoreId())->getWebsiteId();
        $product   = $this->productFactory->create()->load($review->getData('entity_pk_value'));

        $requiredValidation = true;

        if ($review->isApproved() && $review->getCustomerId()) {
            $requiredValidation = false;
        }

        $this->behaviorRuleHelper->processRule(Config::BEHAVIOR_TRIGGER_REVIEW,
            $customer, $websiteId, $review->getId(), ['product' => $product], $requiredValidation);

        \Magento\Framework\Profiler::stop(__CLASS__ . ':' . __METHOD__);
    }
}
