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



namespace Mirasvit\Rewards\Repository;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Mirasvit\Rewards\Model\Tier;

class TierRepository implements \Mirasvit\Rewards\Api\Repository\TierRepositoryInterface
{
    use \Mirasvit\Rewards\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rewards\Repository\RepositoryFunction\GetList;

    /**
     * @var Tier[]
     */
    protected $instances = [];

    /**
     * @var Tier
     */
    protected $firstTier;
    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Tier\CollectionFactory
     */
    private $tierCollectionFactory;
    /**
     * @var \Mirasvit\Rewards\Model\TierFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Tier
     */
    private $tierResource;
    /**
     * @var \Mirasvit\Rewards\Api\Data\TierSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Mirasvit\Rewards\Model\ResourceModel\Tier\CollectionFactory $tierCollectionFactory,
        \Mirasvit\Rewards\Model\TierFactory $objectFactory,
        \Mirasvit\Rewards\Model\ResourceModel\Tier $tierResource,
        \Mirasvit\Rewards\Api\Data\TierSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->productMetadata       = $productMetadata;
        $this->tierCollectionFactory = $tierCollectionFactory;
        $this->objectFactory         = $objectFactory;
        $this->tierResource          = $tierResource;
        $this->searchResultsFactory  = $searchResultsFactory;
        $this->customerFactory       = $customerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rewards\Api\Data\TierInterface $tier)
    {
        if ($tier->getTierId()) {
            $existingTier = $this->get($tier->getTierId());
            $mergedData = array_merge($existingTier->getData(), $tier->getData());
            $tier->setData($mergedData);
        }
        $this->tierResource->save($tier);

        return $tier;
    }

    /**
     * {@inheritdoc}
     */
    public function get($tierId)
    {
        if (!isset($this->instances[$tierId])) {
            /** @var Tier $tier */
            $tier = $this->objectFactory->create();
            $this->tierResource->load($tier, $tierId);
            if (!$tier->getId()) {
                throw NoSuchEntityException::singleField('id', $tierId);
            }
            $this->instances[$tierId] = $tier;
        }

        return $this->instances[$tierId];
    }

    /**
     * {@inheritdoc}
     */
    public function getTiers(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this->getList($searchCriteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstTier()
    {
        if (!isset($this->firstTier)) {
            /** @var Tier $tier */
            $tier = $this->objectFactory->create();
            $this->tierResource->load($tier, 0, 'min_earn_points');
            if (!$tier->getId()) {
                throw NoSuchEntityException::singleField('id', 'first tier');
            }
            $this->firstTier = $tier;
        }

        return $this->firstTier;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rewards\Api\Data\TierInterface $tier)
    {
        try {
            $tierId = $tier->getId();
            $this->tierResource->delete($tier);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete tier with id %1',
                    $tier->getId()
                ),
                $e
            );
        }
        unset($this->instances[$tierId]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($tierId)
    {
        $tier = $this->get($tierId);

        return $this->delete($tier);
    }

    /**
     * @return \Mirasvit\Rewards\Model\ResourceModel\Tier\Collection
     */
    public function getCollection()
    {
        return $this->tierCollectionFactory->create();
    }
}
