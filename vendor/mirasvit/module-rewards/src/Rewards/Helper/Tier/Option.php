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



namespace Mirasvit\Rewards\Helper\Tier;

use Mirasvit\Rewards\Api\Data\TierInterface;

class Option
{
    /**
     * @var \Mirasvit\Rewards\Api\Repository\TierRepositoryInterface
     */
    private $tierRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Mirasvit\Rewards\Api\Repository\TierRepositoryInterface $tierRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->tierRepository = $tierRepository;
    }

    /**
     * @return TierInterface[]
     */
    public function getTierList()
    {
        $sortOrderSort = $this->sortOrderBuilder
            ->setField(TierInterface::KEY_MIN_EARN_POINTS)
            ->setDirection(\Magento\Framework\Api\SortOrder::SORT_ASC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(TierInterface::KEY_IS_ACTIVE, 1)
            ->addSortOrder($sortOrderSort)
        ;

        return $this->tierRepository->getTiers($searchCriteria->create())->getItems();
    }

}
