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



namespace Mirasvit\Rewards\Api\Repository;

interface TierRepositoryInterface
{
    /**
     * @param \Mirasvit\Rewards\Api\Data\TierInterface $tier
     * @return \Mirasvit\Rewards\Api\Data\TierInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Mirasvit\Rewards\Api\Data\TierInterface $tier);

    /**
     * @param int $tierId
     * @return \Mirasvit\Rewards\Api\Data\TierInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($tierId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \Mirasvit\Rewards\Api\Data\TierSearchResultsInterface
     */
    public function getTiers(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null);

    /**
     * @return \Mirasvit\Rewards\Api\Data\TierInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFirstTier();

    /**
     * @param \Mirasvit\Rewards\Api\Data\TierInterface $tier
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Mirasvit\Rewards\Api\Data\TierInterface $tier);

    /**
     * @param int $tierId
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($tierId);

    /**
     * @return \Mirasvit\Rewards\Api\Data\TierInterface[]|\Mirasvit\Rewards\Model\ResourceModel\Tier\Collection
     */
    public function getCollection();
}