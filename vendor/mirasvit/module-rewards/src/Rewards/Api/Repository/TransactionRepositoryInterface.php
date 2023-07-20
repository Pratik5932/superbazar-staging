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


interface TransactionRepositoryInterface
{
    /**
     * @param \Mirasvit\Rewards\Api\Data\TransactionInterface $transaction
     * @return \Mirasvit\Rewards\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Mirasvit\Rewards\Api\Data\TransactionInterface $transaction);

    /**
     * @param int $transactionId
     * @return \Mirasvit\Rewards\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($transactionId);

    /**
     * @param \Mirasvit\Rewards\Api\Data\TransactionInterface $transaction
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Mirasvit\Rewards\Api\Data\TransactionInterface $transaction);

    /**
     * @param int $transactionId
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($transactionId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
     * @return \Mirasvit\Rewards\Api\Data\TransactionSearchResultsInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null);
}