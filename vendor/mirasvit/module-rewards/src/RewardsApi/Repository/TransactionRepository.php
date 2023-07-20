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



namespace Mirasvit\RewardsApi\Repository;

use Mirasvit\Rewards\Api\Repository\TransactionRepositoryInterface as Transaction;
use Mirasvit\RewardsApi\Api\Repository\TransactionRepositoryInterface;
use Mirasvit\Rewards\Helper\Mail;

class TransactionRepository implements TransactionRepositoryInterface
{
    private $transactionRepository;

    private $rewardsNotification;

    public function __construct(
        Transaction $transactionRepository,
        Mail $rewardsNotification
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->rewardsNotification = $rewardsNotification;
    }

    /**
     * @param \Mirasvit\Rewards\Api\Data\TransactionInterface $transaction
     *
     * @return \Mirasvit\Rewards\Api\Data\TransactionInterface
     */
    public function saveTransaction($transaction)
    {
        $transaction = $this->transactionRepository->save($transaction);
        $this->rewardsNotification->sendNotificationBalanceUpdateEmail($transaction);

        return $transaction;
    }


}
