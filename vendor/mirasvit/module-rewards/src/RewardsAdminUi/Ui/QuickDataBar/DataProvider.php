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


declare(strict_types=1);

namespace Mirasvit\RewardsAdminUi\Ui\QuickDataBar;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Mirasvit\Rewards\Api\Data\TransactionInterface;

class DataProvider
{
    private $resource;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    public function getConnection(): AdapterInterface
    {
        return $this->resource->getConnection();
    }

    public function getTransactionSelect(array $columns = []): Select
    {
        $columns = array_merge($columns, [
            'value' => new \Zend_Db_Expr('ABS(SUM(' . TransactionInterface::KEY_AMOUNT . '))'),
        ]);

        return $this->resource->getConnection()
            ->select()
            ->from($this->resource->getTableName(TransactionInterface::TABLE_NAME), $columns);
    }
}
