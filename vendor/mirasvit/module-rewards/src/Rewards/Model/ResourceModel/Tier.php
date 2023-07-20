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



namespace Mirasvit\Rewards\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Rewards\Api\Data\TierInterface;

class Tier extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mst_rewards_tier', 'tier_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Mirasvit\Rewards\Model\Tier
     */
    protected function loadWebsiteIds(\Magento\Framework\Model\AbstractModel $object)
    {
        /* @var  \Mirasvit\Rewards\Model\Tier $object */
        $select = $this->getConnection()->select()
            ->from($this->getTable('mst_rewards_tier_website'))
            ->where('tier_id = ?', $object->getId());
        if ($data = $this->getConnection()->fetchAll($select)) {
            $array = [];
            foreach ($data as $row) {
                $array[] = $row['website_id'];
            }
            $object->setData('website_ids', $array);
        }

        return $object;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    protected function saveWebsiteIds($object)
    {
        /* @var  \Mirasvit\Rewards\Model\Tier $object */
        $condition = $this->getConnection()->quoteInto('tier_id = ?', $object->getId());
        $this->getConnection()->delete($this->getTable('mst_rewards_tier_website'), $condition);
        foreach ((array) $object->getData('website_ids') as $id) {
            $objArray = [
                'tier_id' => $object->getId(),
                'website_id' => $id,
            ];
            $this->getConnection()->insert(
                $this->getTable('mst_rewards_tier_website'), $objArray
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            [TierInterface::KEY_IS_ACTIVE]
        )->where(
            "{$this->getIdFieldName()} = :pid"
        );

        $binds = ['pid' => (int)$object->getId()];

        $isActive = $this->getConnection()->fetchOne($select, $binds);
        if (empty($object->getData(TierInterface::KEY_IS_ACTIVE)) && !empty($isActive)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Magento\Customer\Model\ResourceModel\CustomerRepository $customerCollection */
            $customerCollection = $objectManager->create('Magento\Customer\Model\ResourceModel\CustomerRepository');
            /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria */
            $searchCriteria = $objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder');
            $searchCriteria->addFilter(TierInterface::CUSTOMER_KEY_TIER_ID, $object->getId());
            $count = $customerCollection->getList($searchCriteria->create())->getTotalCount();
            if ($count) {
                throw new LocalizedException(
                    __('This tier is assign to %1 customer(s). Please reassign customer(s) to other tier.', $count)
                );
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Customer\Model\ResourceModel\CustomerRepository $customerCollection */
        $customerCollection = $objectManager->create('Magento\Customer\Model\ResourceModel\CustomerRepository');
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = $objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder');
        $searchCriteria->addFilter(TierInterface::CUSTOMER_KEY_TIER_ID, $object->getId());
        $count = $customerCollection->getList($searchCriteria->create())->getTotalCount();
        if ($count) {
            throw new LocalizedException(
                __('This tier is assign to %1 customer(s). Please reassign customer(s) to other tier.', $count)
            );
        }

        return parent::_beforeDelete($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        /* @var  \Mirasvit\Rewards\Model\Tier $object */
        if (!$object->getIsMassDelete()) {
            $this->loadWebsiteIds($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rewards\Model\Tier $object */
        if (!$object->getIsMassStatus()) {
            $this->saveWebsiteIds($object);
        }

        return parent::_afterSave($object);
    }
}
