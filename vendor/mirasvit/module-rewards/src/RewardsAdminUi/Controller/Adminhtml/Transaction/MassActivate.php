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



namespace Mirasvit\RewardsAdminUi\Controller\Adminhtml\Transaction;

use Mirasvit\RewardsAdminUi\Controller\Adminhtml\Transaction as TranactionController;
use Mirasvit\Rewards\Helper\Balance;
use Mirasvit\Rewards\Model\ResourceModel\Transaction as TransactionResource;
use Mirasvit\Rewards\Model\ResourceModel\Transaction\CollectionFactory;
use Mirasvit\Rewards\Model\TransactionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Ui\Component\MassAction\Filter;

class MassActivate extends TranactionController
{
    private $date;
    private $filter;
    private $collectionFactory;
    private $transactionResource;

    public function __construct(
        TransactionResource $transactionResource,
        TransactionFactory $transactionFactory,
        CustomerFactory $customerFactory,
        Balance $rewardsBalance,
        Registry $registry,
        FileFactory $fileFactory,
        Context $context,
        DateTime $date,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct(
            $transactionFactory, $customerFactory, $rewardsBalance, $registry, $fileFactory, $context
        );
        $this->date = $date;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->transactionFactory = $transactionFactory;
        $this->transactionResource = $transactionResource;

    }
    /**
     * @return void
     */
    public function execute()
    {
        $ids = [];

        if ($this->getRequest()->getParam('transaction_id')) {
            $ids = $this->getRequest()->getParam('transaction_id');
        }

        if ($this->getRequest()->getParam(Filter::SELECTED_PARAM)) {
            $ids = $this->getRequest()->getParam(Filter::SELECTED_PARAM);
        }

        if (!$ids) {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $ids = $collection->getAllIds();
        }

        if ($ids && is_array($ids)) {
            $today = $this->date->gmtDate('Y-m-d h-i-s', $this->date->gmtTimestamp());
            $today = $this->date->gmtTimestamp();
            try {
                foreach ($ids as $id) {
                    /** @var \Mirasvit\Rewards\Model\Transaction $transaction */
                    $transaction = $this->transactionFactory->create()
                        ->setIsMassDelete(true);
                    $this->transactionResource->load($transaction, $id);

                    if (!$transaction->getIsActivated()) {
                        $transaction->setActivatedAt($today)
                            ->setIsActivated(true);
                        $this->transactionResource->save($transaction);
                    }
                }
                $this->messageManager->addSuccessMessage(
                    __(
                        'Total of %1 record(s) were successfully activated', count($ids)
                    )
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Please select Transaction(s)'));
        }
        $this->_redirect('*/*/index');
    }
}
