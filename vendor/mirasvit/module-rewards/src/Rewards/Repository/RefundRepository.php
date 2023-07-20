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

use Mirasvit\Rewards\Api\Data\RefundInterface;
use Mirasvit\Rewards\Api\Repository\RefundRepositoryInterface;
use Mirasvit\Rewards\Model\ResourceModel\Refund\CollectionFactory;
use Mirasvit\Rewards\Model\RefundFactory;
use Mirasvit\Rewards\Model\ResourceModel\Refund as RefundResource;
use Mirasvit\Rewards\Api\Data\RefundSearchResultsInterfaceFactory;

class RefundRepository implements RefundRepositoryInterface
{
    use \Mirasvit\Rewards\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rewards\Repository\RepositoryFunction\GetList;
    /**
     * @var CollectionFactory
     */
    private $refundCollectionFactory;
    /**
     * @var RefundFactory
     */
    private $objectFactory;
    /**
     * @var RefundResource
     */
    private $refundResource;
    /**
     * @var RefundSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var array
     */
    private $instances = [];

    /**
     * @var array
     */
    private $instancesByCreditmemo = [];

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        CollectionFactory $refundCollectionFactory,
        RefundFactory $objectFactory,
        RefundResource $refundResource,
        RefundSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->productMetadata         = $productMetadata;
        $this->refundCollectionFactory = $refundCollectionFactory;
        $this->objectFactory           = $objectFactory;
        $this->refundResource          = $refundResource;
        $this->searchResultsFactory    = $searchResultsFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function save(RefundInterface $refund)
    {
        $this->refundResource->save($refund);

        return $refund;
    }

    /**
     * {@inheritDoc}
     */
    public function get($refundId)
    {
        if (!isset($this->instances[$refundId])) {
            /** @var RefundInterface $refund */
            $refund = $this->objectFactory->create();
            $this->refundResource->load($refund, $refundId);
            if (!$refund->getId()) {
                return false;
            }
            $this->instances[$refundId] = $refund;
        }

        return $this->instances[$refundId];
    }

    /**
     * {@inheritDoc}
     */
    public function getByCreditmemoId($creditmemoId)
    {
        if (!isset($this->instancesByCreditmemo[$creditmemoId])) {
            /** @var RefundInterface $refund */
            $refund = $this->objectFactory->create();
            $this->refundResource->load($refund, $creditmemoId, RefundInterface::KEY_CREDITMEMO_ID);
            if (!$refund->getId()) {
                return false;
            }
            $this->instancesByCreditmemo[$creditmemoId] = $refund;
            $this->instances[$refund->getId()] = $refund;
        }

        return $this->instancesByCreditmemo[$creditmemoId];
    }

    /**
     * {@inheritDoc}
     */
    public function delete(RefundInterface $refund)
    {
        try {
            $refundId = $refund->getId();
            $this->refundResource->delete($refund);
        } catch (\Exception $e) {
            return false;
        }
        unset($this->instances[$refundId]);

        return true;
    }
}
