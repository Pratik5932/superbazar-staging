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

use Magento\Sales\Model\Order;

/**
 * Class PendingTransactions
 * Customer account pending transaction block
 * @package Mirasvit\RewardsCustomerAccount\Block\Account\Listing
 */
class PendingTransactions extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Rewards\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Mirasvit\Rewards\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $collection;

    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Purchase\CollectionFactory
     */
    protected $purchaseCollectionFactory;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @param \Mirasvit\Rewards\Model\TransactionFactory                       $transactionFactory
     * @param \Mirasvit\Rewards\Model\Config                                   $config
     * @param \Mirasvit\Rewards\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory
     * @param \Magento\Customer\Model\Session                                  $customerSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface                      $orderRepository
     * @param \Magento\Framework\View\Element\Template\Context                 $context
     * @param array                                                            $data
     */
    public function __construct(
        \Mirasvit\Rewards\Model\TransactionFactory $transactionFactory,
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->transactionFactory           = $transactionFactory;
        $this->config                       = $config;
        $this->purchaseCollectionFactory    = $purchaseCollectionFactory;
        $this->orderRepository              = $orderRepository;
        $this->orderCollectionFactory       = $orderCollectionFactory;
        $this->customerSession              = $customerSession;
        $this->context                      = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getTransactionCollection()) {
            /** @var \Magento\Theme\Block\Html\Pager $pager */
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'rewards.account_pending_transaction_list_toolbar_pager'
            )->setLimitVarName(
                'pt_limit'
            )->setPageVarName(
                'pt_p'
            )->setData(
                'show_amounts', false
            )->setShowPerPage(
                false
            )->setCollection(
                $this->getTransactionCollection()
            );
            $this->setChild('pager', $pager);
            $this->getTransactionCollection()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return \Mirasvit\Rewards\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Forms collection of pending transactions
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection|Order[]
     */
    public function getTransactionCollection()
    {
        if (!$this->collection) {
            $customerId = $this->customerSession->getCustomerId();

            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('customer_id', $customerId);
            $orderCollection->addFieldToFilter('state', ['neq' => Order::STATE_CANCELED]);
            $orderCollection->addFieldToFilter('state', ['neq' => Order::STATE_CLOSED]);
            $orderCollection->addFieldToFilter('rp.earn_points', ['gt' => 0]);
            $orderCollection->setOrder('main_table.entity_id', 'DESC');

            $orderSelect = $orderCollection->getSelect();
            $orderSelect->joinLeft(
                ['rt' => $orderCollection->getTable('mst_rewards_transaction')],
                'main_table.entity_id = REPLACE(rt.code, "order_earn-", "")',
                ['rt.transaction_id', 'rt.created_at as transaction_created_at', 'rt.activated_at', 'rt.is_activated']
            );
            $orderSelect->joinLeft(
                [
                    'rp' => $orderCollection->getTable('mst_rewards_purchase')
                ],
                'main_table.entity_id = rp.order_id AND rt.transaction_id IS NULL',
                [
                    'rp.purchase_id',
                    'rt.created_at as purchase_created_at',
                    'IF(rt.amount, rt.amount, rp.earn_points) as amount'
                ]
            );

            $this->collection = $orderCollection;
        }

        return $this->collection;
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getOrderUrl($orderId)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }
}
