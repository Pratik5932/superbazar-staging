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



namespace Mirasvit\Rewards\Repository\RepositoryFunction;

use \Magento\Framework\App\ObjectManager;
use \Magento\Framework\Api\SortOrder;
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as Collection;

trait GetList
{
    /**
     * @var \Mirasvit\Rewards\Api\SearchCriteria\CollectionProcessor
     */
    public $collectionProcessor;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        /** @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection */
        $collection = $this->objectFactory->create()->getCollection();
        $searchData = $this->searchResultsFactory->create();

        $searchData->setTotalCount($collection->getSize());

        if ($searchCriteria) {
            $searchData->setSearchCriteria($searchCriteria);
            if (version_compare($this->productMetadata->getVersion(), "2.2.0", "<")) {
                foreach ($searchCriteria->getFilterGroups() as $group) {
                    $this->addFilterGroupToCollection($group, $collection);
                }
            }

            $collection->setCurPage($searchCriteria->getCurrentPage());

            if (version_compare($this->productMetadata->getVersion(), "2.2.0", "<")) {
                $sortOrders = $searchCriteria->getSortOrders();
                if ($sortOrders) {
                    /** @var SortOrder $sortOrder */
                    foreach ($sortOrders as $sortOrder) {
                        $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'ASC' : 'DESC';
                        $collection->getSelect()->order($sortOrder->getField() . ' ' . $direction);
                    }
                }
            }

            $collection->setPageSize($searchCriteria->getPageSize());

            if (version_compare($this->productMetadata->getVersion(), "2.2.0", ">=")) {
                $this->getCollectionProcessor()->process($searchCriteria, $collection);
            }
        }

        $searchData->setItems($collection->getItems());

        return $searchData;
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 102.0.0
     * @return \Magento\Framework\Api\SearchCriteria\CollectionProcessor
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = ObjectManager::getInstance()->get(
                'Mirasvit\Rewards\Api\SearchCriteria\CollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }
}