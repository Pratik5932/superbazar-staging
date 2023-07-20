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
 * Class InactiveTransactions. Customer account inactive transaction block
 *
 * @package Mirasvit\RewardsCustomerAccount\Block\Account\Listing
 */
class InactiveTransactions extends Template
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
    public function getStatusDescription()
    {
        return __('Will be ready for use %1', $this->transaction->getActivatedAtFormatted());
    }
}
