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



namespace Mirasvit\RewardsApi\Service\Customer\Management;

use Mirasvit\Rewards\Api\Repository\TransactionRepositoryInterface;
use Mirasvit\Rewards\Api\Repository\ReferralRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Search implements \Mirasvit\Rewards\Api\Service\Customer\Management\SearchInterface
{
    private $transactionRepository;

    private $referralRepository;

    private $searchCriteriaBuilder;

    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        ReferralRepositoryInterface $referralRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->referralRepository    = $referralRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactions($customerId, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        if ($searchCriteria) {
            $customerSearchCriteria = $this->searchCriteriaBuilder
                ->addFilter('customer_id', $customerId)->create();
            $groups                 = $searchCriteria->getFilterGroups();
            foreach ($customerSearchCriteria->getFilterGroups() as $filterGroup) {
                $groups[] = $filterGroup;
            }
            $searchCriteria->setFilterGroups($groups);
        } else {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('customer_id', $customerId)->create();
        }

        return $this->transactionRepository->getList($searchCriteria)->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getFriendsList($customerId, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('customer_id', $customerId)->create();

        return $this->referralRepository->getReferredCustomers($searchCriteria)->getItems();
    }
}
