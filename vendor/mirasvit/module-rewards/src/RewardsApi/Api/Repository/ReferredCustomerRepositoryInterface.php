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



namespace Mirasvit\RewardsApi\Api\Repository;


interface ReferredCustomerRepositoryInterface
{
    /**
     * @param int $customerId
     *
     * @return string
     */
    public function getCode($customerId);

    /**
     * @param int    $customerId
     * @param string $code
     * @param int    $storeId
     *
     * @return int
     */
    public function addReferral($customerId, $code, $storeId);

    /**
     * @param int      $customerId
     * @param int      $storeId
     * @param string   $message
     * @param string[] $friendMail
     *
     * @return void
     */
    public function sendReferralMessage($customerId, $storeId, $message, $friendMail);

    /**
     * @param string $code
     * @param int    $quoteId
     * @param int    $storeId
     *
     * @return int
     */
    public function addGuestReferral($code, $quoteId, $storeId);
}
