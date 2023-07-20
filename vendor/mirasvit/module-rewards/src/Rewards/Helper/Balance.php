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



namespace Mirasvit\Rewards\Helper;

class Balance extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Mirasvit\Rewards\Model\BalanceFactory
     */
    protected $balanceFactory;

    /**
     * @var \Mirasvit\Rewards\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var Mail
     */
    protected $rewardsMail;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    public function __construct(
        \Mirasvit\Rewards\Model\BalanceFactory $balanceFactory,
        \Mirasvit\Rewards\Model\TransactionFactory $transactionFactory,
        \Mirasvit\Rewards\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        \Mirasvit\Rewards\Helper\Mail $rewardsMail,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->balanceFactory               = $balanceFactory;
        $this->transactionFactory           = $transactionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->rewardsMail                  = $rewardsMail;
        $this->resource                     = $resource;
        $this->context                      = $context;

        parent::__construct($context);
    }

    /**
     * Create transaction
     *
     * @param \Magento\Customer\Model\Customer|int $customer
     * @param float                              $pointsNum
     * @param string                           $historyMessage
     * @param bool                             $isAllowPending - show is transaction can be deactivate regarding option
     * @param bool|string                             $code - if we have code, we will check for uniqness this transaction
     * @param bool                             $notifyByEmail
     * @param bool|string                             $emailMessage
     *
     * @return bool|\Mirasvit\Rewards\Model\Transaction
     */
    public function changePointsBalance(
        $customer, $pointsNum, $historyMessage, $isAllowPending, $code = false, $notifyByEmail = false, $emailMessage = false
    ) {
        $order = null;
        if (is_object($customer)) {
            if ($customer->getCustomerOrder()) {
                $order = $customer->getCustomerOrder();
            }
            $customerId = $customer->getId();
        } else {
            $customerId = $customer;
        }
        if ($code) {
            $collection = $this->transactionCollectionFactory->create()
                            ->addFieldToFilter('customer_id', $customerId)
                            ->addFieldToFilter('code', $code);
            if ($collection->count()) {
                return false;
            }
        }
        /** @var \Mirasvit\Rewards\Model\Transaction $transaction */
        $transaction = $this->transactionFactory->create()
            ->setCustomerId($customerId)
            ->setAmount($pointsNum);
        if ($code) {
            $transaction->setCode($code);
        }
        if ($isAllowPending) {
            $transaction->setIsAllowPending($isAllowPending);
        }
        $historyMessage = $this->rewardsMail->parseVariables($historyMessage, $transaction, $order);
        $transaction->setComment($historyMessage);
        $transaction->save();
        if ($notifyByEmail) {
            $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction, $emailMessage);
        }

        return $transaction;
    }

    /**
     * @param \Magento\Customer\Model\Customer|int $customer
     * @return int
     */
    public function getBalancePoints($customer)
    {
        if (is_object($customer)) {
            $customer = $customer->getId();
        }

        /** @var \Mirasvit\Rewards\Model\ResourceModel\Transaction\Collection $collection */
        $collection = $this->transactionCollectionFactory->create();
        $collection->addCustomerFilter((int)$customer);
        $collection->addActivatedFilter();
        $collection->getSelect()->columns(new \Zend_Db_Expr("SUM(amount) as balance"));

        return (int)$collection->getFirstItem()->getData('balance');
    }

    /**
     * @return \Mirasvit\Rewards\Api\Data\BalanceInterface[]
     */
    public function getAllBalances()
    {
        /** @var \Mirasvit\Rewards\Model\ResourceModel\Transaction\Collection $collection */
        $collection = $this->transactionCollectionFactory->create();
        $collection->addActivatedFilter();
        $collection->getSelect()->columns(new \Zend_Db_Expr("SUM(amount) as balance"))
            ->group('customer_id');

        $balances = [];
        foreach ($collection as $item) {
            $balance = $this->balanceFactory->create();
            $balance->setData([
                'customer_id' => $item->getData('customer_id'),
                'amount'      => $item->getData('balance'),
            ]);
            $balances[] = $balance;
        }

        return $balances;
    }

    /**
     * @param \Magento\Customer\Model\Customer|int $customer
     * @param int $days
     * @return int
     */
    public function getPointsForLastDays($customer, $days)
    {
        if (is_object($customer)) {
            $customer = $customer->getId();
        }

        /** @var \Mirasvit\Rewards\Model\ResourceModel\Transaction\Collection $collection */
        $collection = $this->transactionCollectionFactory->create();
        $collection->addCustomerFilter((int)$customer);
        $collection->addActivatedFilter();
        $collection->addFieldToFilter('amount', ['gt' => '0']);
        $collection->addFieldToFilter('code', ['nlike' => 'order_spend_restore%']);
        $collection->getSelect()->columns(new \Zend_Db_Expr("SUM(amount) as balance"));

        $days = (int)$days;
        if ($days) {
            if ($days == 1) {
                $interval = ' - interval 1 day';
            } else {
                $interval = ' - interval ' . $days . ' day';
            }
            $sql = 'CURDATE() ' . $interval;
            $collection->addFieldToFilter('main_table.created_at', ['gt' => new \Zend_Db_Expr($sql)]);
        }

        return (int)$collection->getFirstItem()->getData('balance');
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param string                           $code
     * @return void
     */
    public function cancelEarnedPoints($customer, $code)
    {
        $earnedTransaction = $this->getEarnedPointsTransaction($customer, $code);
        if (!$earnedTransaction) {
            return;
        }
        $earnedTransaction->delete();
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param string                           $code
     * @return \Mirasvit\Rewards\Model\Transaction|null
     */
    public function getEarnedPointsTransaction($customer, $code)
    {
        $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('code', ['like' => $code.'%'])
        ;

        $select = $collection->getSelect();
        $select->joinInner(
            ['er' => $this->transactionCollectionFactory->create()->getTable('mst_rewards_earning_rule')],
            "SUBSTRING_INDEX(main_table.code, '-', -1) = er.earning_rule_id AND " .
            "behavior_trigger = SUBSTRING_INDEX(main_table.code, '-', 1)" // to prevent coincidence with custom codes
        );

        if ($collection->count()) {
            return $collection->getFirstItem();
        }
        return null;
    }
}
