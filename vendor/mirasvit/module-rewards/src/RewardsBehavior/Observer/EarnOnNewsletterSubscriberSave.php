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
use Magento\Newsletter\Model\Subscriber;
use Mirasvit\Rewards\Model\Config;

class EarnOnNewsletterSubscriberSave implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerFactory
     */
    private $customerResourceFactory;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Mirasvit\Rewards\Helper\Balance
     */
    private $rewardsBalance;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Mirasvit\Rewards\Helper\BehaviorRule
     */
    protected $rewardsBehavior;

    /**
     * @param \Mirasvit\Rewards\Helper\BehaviorRule $rewardsBehavior
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        \Mirasvit\Rewards\Helper\BehaviorRule $rewardsBehavior,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerResourceFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry
    ) {
        $this->rewardsBalance  = $rewardsBalance;
        $this->rewardsBehavior = $rewardsBehavior;
        $this->customerFactory = $customerFactory;
        $this->moduleManager   = $moduleManager;
        $this->storeManager    = $storeManager;
        $this->registry        = $registry;

        $this->customerResourceFactory = $customerResourceFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);

        /** @var Subscriber $subscriber */
        $subscriber = $observer->getEvent()->getDataObject();
        if ($this->moduleManager->isEnabled('Ebizmarts_MailChimp')) {
            $this->subscribeEbizmartsEnabled($subscriber);
        } else {
            $this->subscribeEbizmartsDisabled($subscriber);
        }

        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }

    /**
     * @param Subscriber $subscriber
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function subscribeEbizmartsEnabled($subscriber)
    {
        // mailchimp sets setIsStatusChanged instead of setStatusChanged
        if (($subscriber->getId() && ($subscriber->isStatusChanged() || $subscriber->getIsStatusChanged() ||
                // mailchimp does not set StatusChanged when subscriber already exists
                $subscriber->getOrigData('subscriber_status') != $subscriber->getData('subscriber_status')
            ))) {
            if (!$subscriber->getCustomerId()) { // mailchimp does not set customer id
                $customer = $this->customerFactory->create();
                $websiteId = $this->storeManager->getStore($subscriber->getStoreId())->getWebsiteId();
                $customer->setWebsiteId($websiteId);
                $this->customerResourceFactory->create()->loadByEmail($customer, $subscriber->getSubscriberEmail());
            } else {
                $customer = $this->customerFactory->create();
                $this->customerResourceFactory->create()->load($customer, $subscriber->getCustomerId());
            }
            $customerId = $customer->getId();

            if ($subscriber->getStatus() == Subscriber::STATUS_SUBSCRIBED) {
                $websiteId = $this->storeManager->getStore($subscriber->getStoreId())->getWebsiteId();
                $this->rewardsBehavior->processRule(
                    Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP, $customerId, $websiteId
                );
            } elseif ($subscriber->getStatus() == Subscriber::STATUS_UNSUBSCRIBED) {
                $this->rewardsBalance->cancelEarnedPoints($customer, Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP);
            }
        }
    }

    /**
     * @param Subscriber $subscriber
     */
    private function subscribeEbizmartsDisabled($subscriber)
    {
        if (($subscriber->getId() && $subscriber->isStatusChanged())) {
            if ($subscriber->getStatus() == Subscriber::STATUS_SUBSCRIBED) {
                $websiteId = $this->storeManager->getStore($subscriber->getStoreId())->getWebsiteId();
                $this->rewardsBehavior->processRule(
                    Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP, $subscriber->getCustomerId(), $websiteId
                );
            } elseif ($subscriber->getStatus() == Subscriber::STATUS_UNSUBSCRIBED) {
                $customer = $this->customerFactory->create();
                $this->customerResourceFactory->create()->load($customer, $subscriber->getCustomerId());
                $this->rewardsBalance->cancelEarnedPoints($customer, Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP);
            }
        }
    }
}
