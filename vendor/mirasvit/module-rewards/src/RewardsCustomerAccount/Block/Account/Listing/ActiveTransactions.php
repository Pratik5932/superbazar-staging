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



namespace Mirasvit\RewardsCustomerAccount\Block\Account\Listing;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Model\Transaction;

/**
 * Class ActiveTransactions. Customer account active transaction block
 *
 * @package Mirasvit\RewardsCustomerAccount\Block\Account\Listing
 */
class ActiveTransactions extends Template
{
    private $config;

    /**
     * @var Transaction
     */
    protected $transaction;

    public function __construct(
        Config $config,
        Context $context,
        array $data = []
    ) {
        $this->config = $config;

        parent::__construct($context, $data);
    }

    /**
     * @param Transaction $transaction
     *
     * @return $this
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @return string
     */
    public function getExpirationEnabled()
    {
        return $this->config->getGeneralExpiresAfterDays();
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $expires = $this->transaction->getData('expires_at');

        if ($this->transaction->getAmount() <= 0) {
            return '';
        }

        if (!$expires) {
            return __('Active');
        }

        $date    = date_create($expires);
        $dateNow = date_create();
        $diff    = date_diff($date, $dateNow);

        $this->transaction->setData('expires_diff', $diff);

        return !$diff->invert ? (string)__('Expired') : (string)__('Active');
    }

    /**
     * @return string
     */
    public function getStatusDescription()
    {
        if ($this->transaction->getAmount() <= 0) {
            return '';
        }
        $diff = $this->transaction->getData('expires_diff');
        if (!$diff) {
            $this->getStatus();
            $diff = $this->transaction->getData('expires_diff');
        }
        $expires = $this->transaction->getData('expires_at');
        if ($expires) {
            if (!$diff->invert) {
                return __('Points are expired');
            } else {
                return __('Will expire %1', $this->transaction->getExpiresAtFormatted());
            }
        }
        return '';
    }
}
