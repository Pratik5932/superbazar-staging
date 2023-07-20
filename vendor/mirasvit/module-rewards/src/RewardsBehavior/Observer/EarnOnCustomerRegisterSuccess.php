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
use Magento\Sales\Api\OrderRepositoryInterface;
use Mirasvit\Rewards\Model\Config;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Api\AccountManagementInterface;

class EarnOnCustomerRegisterSuccess implements ObserverInterface
{
    private $accountConfirmation;

    private $accountManagement;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $session;
    /**
     * @var \Mirasvit\Rewards\Helper\Balance\EarnBehaviorOrderPoints
     */
    private $earnBehaviorOrderPoints;
    /**
     * @var \Mirasvit\Rewards\Model\ReferralFactory
     */
    private $referralFactory;
    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Referral\CollectionFactory
     */
    private $referralCollectionFactory;
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber
     */
    private $subscriberModel;
    /**
     * @var \Mirasvit\Rewards\Helper\BehaviorRule
     */
    private $rewardsBehavior;
    /**
     * @var \Mirasvit\Rewards\Helper\Balance
     */
    private $balanceHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        AccountConfirmation $accountConfirmation,
        AccountManagementInterface $accountManagement,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Mirasvit\Rewards\Helper\Balance\EarnBehaviorOrderPoints $earnBehaviorOrderPoints,
        \Mirasvit\Rewards\Model\ReferralFactory $referralFactory,
        \Mirasvit\Rewards\Model\ResourceModel\Referral\CollectionFactory $referralCollectionFactory,
        \Magento\Newsletter\Model\ResourceModel\Subscriber $subscriberModel,
        \Mirasvit\Rewards\Helper\BehaviorRule $rewardsBehavior,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rewards\Helper\Balance $balanceHelper
    ) {
        $this->accountConfirmation       = $accountConfirmation;
        $this->accountManagement         = $accountManagement;
        $this->orderRepository           = $orderRepository;
        $this->productMetadata           = $productMetadata;
        $this->session                   = $session;
        $this->earnBehaviorOrderPoints   = $earnBehaviorOrderPoints;
        $this->referralFactory           = $referralFactory;
        $this->referralCollectionFactory = $referralCollectionFactory;
        $this->subscriberModel           = $subscriberModel;
        $this->rewardsBehavior           = $rewardsBehavior;
        $this->storeManager              = $storeManager;
        $this->balanceHelper             = $balanceHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }

        /** @var \Magento\Customer\Model\Customer $customerDataObject */
        $customerDataObject = $observer->getEvent()->getCustomerDataObject();
        $origCustomerDataObject = $observer->getEvent()->getOrigCustomerDataObject();
        $mVersion = $this->productMetadata->getVersion();
        $isExit = version_compare($mVersion, '2.2.2') < 0 && !$origCustomerDataObject;
        $customerId = $customerDataObject->getId();
        $isConfirmRequired = $this->accountConfirmation->isConfirmationRequired(
            $customerDataObject->getWebsiteId(),
            $customerId,
            $customerDataObject->getEmail());

        if ($isExit || !$customerId) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }

        if ($isConfirmRequired &&
            $this->accountManagement->getConfirmationStatus($customerId) == AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED
        ) {
            return;
        }

        if (!$isConfirmRequired && $customerId && ($origCustomerDataObject && $origCustomerDataObject->getId())) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }

        $this->applyRules($customerDataObject);

        // for m2.2.5. Added points for the order when customer created an account at the end of checkout
        // for early versions used plugin OrderCustomerManagement
        $delegateData = $observer->getEvent()->getData('delegate_data');
        if ($delegateData && array_key_exists('__sales_assign_order_id', $delegateData)) {
            $orderId = $delegateData['__sales_assign_order_id'];
            $order = $this->orderRepository->get($orderId);
            if ($order->getId()) {
                $this->earnBehaviorOrderPoints->earnBehaviorOrderPoints($order);
            }
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return void
     */
    protected function applyRules($customer)
    {
        $this->customerAfterCreate($customer);
        $websiteId = $this->storeManager->getWebsite()->getId();
        $this->rewardsBehavior->processRule(Config::BEHAVIOR_TRIGGER_SIGNUP, $customer, $websiteId);
        if ($this->isCustomerSubscribed($customer)) {
            $this->rewardsBehavior->processRule(Config::BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP, $customer, $websiteId);
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return bool
     */
    protected function isCustomerSubscribed($customer)
    {
        $subscribed = false;
        $subscriber = $this->subscriberModel->loadByEmail($customer->getEmail());
        if ($subscriber && $subscriber['subscriber_status']) {
            $subscribed = true;
        }

        return $subscribed;
    }

    /**
     * Customer sign up.
     *
     * @param \Magento\Customer\Model\Customer $customer
     *
     * @return void
     */
    protected function customerAfterCreate($customer)
    {
        $referral = false;
        if ($id = (int) $this->session->getReferral()) {
            /** @var \Mirasvit\Rewards\Model\Referral $referral */
            $referral = $this->referralFactory->create()->load($id);
        } else {
            $referrals = $this->referralCollectionFactory->create()
                ->addFieldToFilter('email', $customer->getEmail());
            if ($referrals->count()) {
                $referral = $referrals->getFirstItem();
            }
        }
        if (!$referral) {
            return;
        }
        $referral->finish(Config::REFERRAL_STATUS_SIGNUP, $customer->getId());
        /** @var \Mirasvit\Rewards\Model\Transaction $transaction */
        $transaction = $this->rewardsBehavior->processRule(
            Config::BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_SIGNUP,
            $referral->getCustomerId(),
            false,
            $customer->getId()
        );
        $referral->finish(Config::REFERRAL_STATUS_SIGNUP, false, $transaction);
    }
}
