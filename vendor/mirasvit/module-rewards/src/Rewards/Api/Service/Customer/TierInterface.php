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



namespace Mirasvit\Rewards\Api\Service\Customer;

interface TierInterface
{
    /**
     * @param int $customerId
     * @return \Mirasvit\Rewards\Api\Data\TierInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getNextTier($customerId);

    /**
     * @param int $customerId
     * @return \Mirasvit\Rewards\Api\Data\TierInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerTiers($customerId);
    /**
     * @param int $customerId
     * @return \Mirasvit\Rewards\Api\Data\TierInterface|false
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerTier($customerId);

    /**
     * @param int $customerId
     * @return \Mirasvit\Rewards\Api\Data\TierInterface|false
     */
    public function updateCustomerTier($customerId);

    /**
     * @param \Mirasvit\Rewards\Api\Data\TierInterface $tier
     * @param int $customerId
     * @return int
     */
    public function getRemainingPoints($tier, $customerId);
}