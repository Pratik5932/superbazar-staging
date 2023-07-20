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

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mirasvit\Rewards\Model\ReferralFactory as ObjectFactory;
use Mirasvit\Rewards\Model\ResourceModel\Referral as ReferralResource;
use Mirasvit\Rewards\Model\ResourceModel\Referral\CollectionFactory as ReferralCollectionFactory;
use Mirasvit\Rewards\Api\Data\ReferredCustomerSearchResultInterfaceFactory;

class ReferredCustomerRepository implements \Mirasvit\Rewards\Api\Repository\ReferralRepositoryInterface
{
    use \Mirasvit\Rewards\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rewards\Repository\RepositoryFunction\GetList;

    private $objectFactory;

    private $searchResultsFactory;

    private $referralResource;

    private $referralCollectionFactory;

    public function __construct(
        ObjectFactory $objectFactory,
        ReferralResource $referralResource,
        ReferralCollectionFactory $referralCollectionFactory,
        ReferredCustomerSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory             = $objectFactory;
        $this->referralResource          = $referralResource;
        $this->referralCollectionFactory = $referralCollectionFactory;
        $this->searchResultsFactory      = $searchResultsFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(\Mirasvit\Rewards\Api\Data\ReferredCustomerInterface $referredCustomer)
    {
        try {
            return $this->referralResource->save($referredCustomer);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save referred: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function get($referredCustomerId)
    {
        try {
            $referredCustomer = $this->referralCollectionFactory->create();
            $this->referralResource->load($referredCustomer, $referredCustomerId);

            return $referredCustomer;
        } catch (\Exception $e) {
            throw new NoSuchEntityException(
                __(
                    'Could not find referred: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(\Mirasvit\Rewards\Api\Data\ReferredCustomerInterface $referredCustomer)
    {
        try {
            $this->referralResource->delete($referredCustomer);

            return true;

        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete referred: %1',
                    $e->getMessage()
                ),
                $e
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getReferredCustomers(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this->getList($searchCriteria);
    }
}
