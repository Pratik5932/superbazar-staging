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



namespace Mirasvit\Rewards\Service\Customer;

use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Service\MenuLink;
use Mirasvit\Rewards\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Registry;
use Mirasvit\Rewards\Api\Repository\TierRepositoryInterface;
use Mirasvit\Rewards\Helper\Balance;
use Mirasvit\Rewards\Helper\BehaviorRule;
use Mirasvit\Rewards\Helper\Mail;
use Mirasvit\Rewards\Helper\Tier\Order;

class Tier implements \Mirasvit\Rewards\Api\Service\Customer\TierInterface
{
    private $customers     = [];

    private $customerTiers = [];

    private $customerRepository;

    private $menuLink;

    private $searchCriteriaBuilder;

    private $sortOrderBuilder;

    private $registry;

    private $tierRepository;

    private $customerFactory;

    private $balanceHelper;

    private $rewardsBehavior;

    private $rewardsMail;

    private $tierOrder;

    private $config;

    public function __construct(
        MenuLink $menuLink,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        Registry $registry,
        TierRepositoryInterface $tierRepository,
        Balance $balanceHelper,
        BehaviorRule $rewardsBehavior,
        Mail $rewardsMail,
        Order $tierOrder,
        Config $config
    ) {
        $this->menuLink              = $menuLink;
        $this->customerRepository    = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->registry              = $registry;
        $this->tierRepository        = $tierRepository;
        $this->customerFactory       = $customerFactory;
        $this->balanceHelper         = $balanceHelper;
        $this->rewardsBehavior       = $rewardsBehavior;
        $this->rewardsMail           = $rewardsMail;
        $this->tierOrder             = $tierOrder;
        $this->config                = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getNextTier($customerId)
    {
        $customer = $this->getCustomer($customerId);
        $days     = $this->config->getTierCalculationPeriod();
        $website  = $customer->getStore()->getWebsite();

        if ($this->config->getTierCalcType($website) == TierInterface::TYPE_ORDER) {
            $points = $this->tierOrder->getSumForLastDays($customer, $days);
        } else {
            $points = $this->balanceHelper->getPointsForLastDays($customerId, $days);
        }

        $sortOrderSort = $this->sortOrderBuilder
            ->setField(TierInterface::KEY_MIN_EARN_POINTS)
            ->setDirection(\Magento\Framework\Api\SortOrder::SORT_ASC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(TierInterface::KEY_IS_ACTIVE, 1)
            ->addFilter('website_id', $customer->getWebsiteId())
            ->addFilter(TierInterface::KEY_MIN_EARN_POINTS, $points, 'gt')
            ->addSortOrder($sortOrderSort);

        $items = $this->tierRepository->getList($searchCriteria->create())->getItems();

        return array_shift($items);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerTiers($customerId)
    {
        if (empty($this->customerTiers[$customerId])) {
            $customer      = $this->getCustomer($customerId);
            $sortOrderSort = $this->sortOrderBuilder
                ->setField(TierInterface::KEY_MIN_EARN_POINTS)
                ->setDirection(\Magento\Framework\Api\SortOrder::SORT_ASC)
                ->create();

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(TierInterface::KEY_IS_ACTIVE, 1)
                ->addFilter('website_id', $customer->getWebsiteId())
                ->addSortOrder($sortOrderSort);

            $this->customerTiers[$customerId] = $this->tierRepository->getList($searchCriteria->create())->getItems();
        }

        return $this->customerTiers[$customerId];
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerTier($customerId)
    {
        $customer = $this->getCustomer($customerId);
        $tierId   = $customer->getData(TierInterface::CUSTOMER_KEY_TIER_ID);

        try {
            $tier = $this->tierRepository->get($tierId);
        } catch (NoSuchEntityException $e) {
            $tier = false;
        }
        if (!$tier) {
            try {
                $tier = $this->tierRepository->getFirstTier();
            } catch (NoSuchEntityException $e) {
                $tier = false;
            }
        }

        return $tier;
    }

    /**
     * @param int $customerId
     *
     * @return TierInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrentCustomerTier($customerId)
    {
        $days     = $this->config->getTierCalculationPeriod();
        $customer = $this->getCustomer($customerId);
        $website  = $customer->getStore()->getWebsite();
        if ($this->config->getTierCalcType($website) == TierInterface::TYPE_ORDER) {
            $points = $this->tierOrder->getSumForLastDays($customer, $days);
        } else {
            $points = $this->balanceHelper->getPointsForLastDays($customerId, $days);
        }
        if ($points < 0) {
            $points = 0;
        }

        $sortOrderSort = $this->sortOrderBuilder
            ->setField(TierInterface::KEY_MIN_EARN_POINTS)
            ->setDirection(\Magento\Framework\Api\SortOrder::SORT_DESC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(TierInterface::KEY_IS_ACTIVE, 1)
            ->addFilter('website_id', $customer->getWebsiteId())
            ->addFilter(TierInterface::KEY_MIN_EARN_POINTS, $points, 'lteq')
            ->addSortOrder($sortOrderSort);

        $items = $this->tierRepository->getList($searchCriteria->create())->getItems();
        if (!$items) {
            throw new LocalizedException(
                __('Unable to find tier for customer "%1". Please check tier configuration.', $customer->getName())
            );
        }

        return array_shift($items);
    }

    /**
     * @return $this
     */
    private function reset()
    {
        $this->customers     = [];
        $this->customerTiers = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomerTier($customerId)
    {
        $this->reset();
        try {
            $newTier     = $this->getCurrentCustomerTier($customerId);
            $currentTier = $this->getCustomerTier($customerId);
        } catch (\Exception $e) {
            return false;
        }

        $customer = $this->customerRepository->getById($customerId);
        // added to correctly load customer (by website id) in other case, it send newsletter confirmation to all subscribers
        $customer = $this->customerRepository->get($customer->getEmail(), $customer->getWebsiteId());

        if ($currentTier && !$customer->getCustomAttribute(TierInterface::CUSTOMER_KEY_TIER_ID)) {
            $customer->setCustomAttribute(TierInterface::CUSTOMER_KEY_TIER_ID, $currentTier->getId());
            $this->customerRepository->save($customer);
        }

        if ($this->isUpdateTierAllowed($newTier, $currentTier)
            && $this->menuLink->isShowMenu()
            && (in_array('all', $this->menuLink->getCustomerGroups()) || $this->menuLink->isShowMenuForCurrentCustomer($customer))) {
            $customer->setCustomAttribute(TierInterface::CUSTOMER_KEY_TIER_ID, $newTier->getId());

            $this->customerRepository->save($customer);

            /** @var \Magento\Customer\Model\Customer $customer */
            if ($currentTier && !$this->registry->registry('rule_processing')) {
                if ($currentTier->getMinEarnPoints() < $newTier->getMinEarnPoints()) {
                    $customer = $this->getCustomer($customerId);
                    $this->rewardsMail->sendNotificationTierUpEmail($newTier, $customer);
                    $this->registry->register('rule_processing', true, true);
                    $this->rewardsBehavior->processRule(
                        Config::BEHAVIOR_TRIGGER_CUSTOMER_TIER_UP,
                        $customer,
                        $customer->getWebsiteId(),
                        $customerId . '-' . $newTier->getId() . '_' . time()
                    );
                    $this->registry->register('rule_processing', false, true);
                } elseif ($currentTier->getId() != $newTier->getId()) {
                    $this->registry->register('rule_processing', true, true);
                    $this->rewardsBehavior->processRule(
                        Config::BEHAVIOR_TRIGGER_CUSTOMER_TIER_DOWN,
                        $customer,
                        $customer->getWebsiteId(),
                        $customerId . '-' . $newTier->getId() . '_' . time()
                    );
                    $this->registry->register('rule_processing', false, true);
                }
            }
        }

        return $newTier;
    }

    /**
     * @param \Mirasvit\Rewards\Api\Data\TierInterface $newTier
     * @param \Mirasvit\Rewards\Api\Data\TierInterface $currentTier
     *
     * @return bool
     */
    protected function isUpdateTierAllowed($newTier, $currentTier)
    {
        if (
            !$currentTier ||
            ($currentTier->getId() != $newTier->getId() && $this->config->getTierAutoMoveDown()) ||
            ($newTier->getMinEarnPoints() > $currentTier->getMinEarnPoints())
        ) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRemainingPoints($tier, $customerId)
    {
        $pointsLeft = 1;
        $days       = $this->config->getTierCalculationPeriod();
        $customer   = $this->getCustomer($customerId);
        $website    = $customer->getStore()->getWebsite();

        if ($this->config->getTierCalcType($website) == TierInterface::TYPE_ORDER) {
            $balance = $this->tierOrder->getSumForLastDays($customer, $days);
        } else {
            $balance = $this->balanceHelper->getPointsForLastDays($customerId, $days);
        }

        $nextTierStartedAt = $tier->getMinEarnPoints();

        if ($tier && $nextTierStartedAt > $balance) {
            $pointsLeft = $nextTierStartedAt - $balance;
        }

        return $pointsLeft;
    }

    /**
     * @param int $customerId
     *
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer($customerId)
    {
        if (!isset($this->customers[$customerId])) {
            $customer = $this->customerFactory->create();
            $customer->getResource()->load($customer, $customerId);
            $this->customers[$customerId] = $customer;
        }

        return $this->customers[$customerId];
    }
}
