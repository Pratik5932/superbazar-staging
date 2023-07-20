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



namespace Mirasvit\Rewards\Model\ResourceModel\Tier\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Mirasvit\Rewards\Model\ResourceModel\Tier\Collection as TierCollection;


class Collection extends TierCollection implements SearchResultInterface
{
    /**
     * @var \Magento\Framework\Api\Search\AggregationInterface
     */
    protected $aggregations;

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
