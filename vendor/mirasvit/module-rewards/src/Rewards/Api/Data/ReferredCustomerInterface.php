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



namespace Mirasvit\Rewards\Api\Data;

interface ReferredCustomerInterface
{
    const TABLE_NAME = 'mst_rewards_referral';

    const ID = 'referral_id';

    const KEY_CUSTOMER_ID         = 'customer_id';
    const KEY_NEW_CUSTOMER_ID     = 'new_customer_id';
    const KEY_EMAIL               = 'email';
    const KEY_NAME                = 'name';
    const KEY_STATUS              = 'status';
    const KEY_STORE_ID            = 'store_id';
    const KEY_LAST_TRANSACTION_ID = 'last_transaction_id';
    const KEY_POINTS_AMOUNT       = 'points_amount';
    const KEY_CREATED_AT          = 'created_at';
    const KEY_QUOTE_ID            = 'quote_id';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @return int
     */
    public function getNewCustomerId();

    /**
     * @param int $newCustomerId
     *
     * @return $this
     */
    public function setNewCustomerId($newCustomerId);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * @return int
     */
    public function getLastTransactionId();

    /**
     * @param int $lastTransactionId
     *
     * @return $this
     */
    public function setLastTransactionId($lastTransactionId);

    /**
     * @return int
     */
    public function getPointsAmount();

    /**
     * @param int $pointsAmount
     *
     * @return $this
     */
    public function setPointsAmount($pointsAmount);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return int
     */
    public function getQuoteId();

    /**
     * @param int $quoteId
     *
     * @return $this
     */
    public function setQuoteId($quoteId);
}
