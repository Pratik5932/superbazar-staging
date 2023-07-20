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



namespace Mirasvit\Rewards\Model\Cron;

use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mirasvit\Rewards\Api\Data\TransactionInterface;
use Mirasvit\Rewards\Helper\Mail;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Model\ResourceModel\Transaction as TransactionResource;
use Mirasvit\Rewards\Model\ResourceModel\Transaction\Collection as TransactionCollection;

class ActivateTransaction extends AbstractCron
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var DateTime
     */
    private $date;
    /**
     * @var Mail
     */
    private $rewardsMail;
    /**
     * @var TransactionCollection
     */
    private $transactionCollection;
    /**
     * @var TransactionResource
     */
    private $transactionResource;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Mail $rewardsMail,
        Config $config,
        TransactionResource $transactionResource,
        TransactionCollection $transactionCollection,
        DateTime $date,
        Filesystem $filesystem
    ) {
        parent::__construct($filesystem);

        $this->date                  = $date;
        $this->config                = $config;
        $this->rewardsMail           = $rewardsMail;
        $this->transactionResource   = $transactionResource;
        $this->transactionCollection = $transactionCollection;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    protected function execute()
    {
        if ($this->config->getGeneralActivatesAfterDays() <= 0) {
            return;
        }
        $today = $this->date->gmtDate('Y-m-d 23-59-59', $this->date->gmtTimestamp());

        $inactiveTransactions = $this->transactionCollection->addInActivatedFilter();
        $inactiveTransactions->addFieldToFilter(TransactionInterface::KEY_ACTIVATED_AT, ['lteq' => $today]);

        if (!$inactiveTransactions->count()) {
            echo "No transaction to activate.".PHP_EOL;
            return;
        }

        /** @var \Mirasvit\Rewards\Model\Transaction $transaction */
        foreach ($inactiveTransactions as $transaction) {
            $transaction->setIsActivated(true);
            $this->transactionResource->save($transaction);
            $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction);
        }
        echo "Transactions were activated: " . $inactiveTransactions->count() . PHP_EOL;
    }
}
