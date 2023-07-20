<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Wallet\Ui\Component\DataProvider;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Order grid collection
 */
class Transactions extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'wallet_transaction',
        $resourceModel = \Ced\Wallet\Model\ResourceModel\Transaction::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft('customer_grid_flat', 'main_table.customer_id=customer_grid_flat.entity_id', ['name']);
        $this->getSelect()->columns(['action_type' => new \Zend_Db_Expr("CASE WHEN `main_table`.`action` = '0' THEN 'Credit' ELSE 'Debit' END")]);
        $this->addFilterToMap('name', 'customer_grid_flat.name');
        $this->addFilterToMap('action_type', new \Zend_Db_Expr("CASE WHEN `main_table`.`action` = '0' THEN 'Credit' ELSE 'Debit' END"));
    }
}
