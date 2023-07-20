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



namespace Mirasvit\RewardsAdminUi\Ui\QuickDataBar;

use Magento\Backend\Block\Template;
use Magento\Framework\DB\Select;
use Mirasvit\Core\Ui\QuickDataBar\SparklineDataBlock;
use Mirasvit\Rewards\Api\Data\TransactionInterface;

class EarnedDataBlock extends SparklineDataBlock
{
    private $dataProvider;

    public function __construct(
        DataProvider     $dataProvider,
        Template\Context $context
    ) {
        $this->dataProvider = $dataProvider;

        parent::__construct($context);
    }

    public function getLabel(): string
    {
        return (string)__('Earned Points');
    }

    public function getScalarValue(): string
    {
        $select = $this->getSelect();

        $value = (int)$this->dataProvider->getConnection()->fetchOne($select);
//echo $select;die();
        return number_format($value, 0, '.', ' ');
    }

    public function getSparklineValues(): array
    {
        $dateExpr = $this->getDateIntervalExpr(TransactionInterface::KEY_CREATED_AT);

        $select = $this->getSelect([$dateExpr])
            ->group($dateExpr);

        $result = [];
        foreach ($this->dataProvider->getConnection()->fetchPairs($select) as $date => $value) {
            $result[$date] = (int)$value;
        }

        return $result;
    }

    private function getSelect(array $columns = []): Select
    {
        $select = $this->dataProvider->getTransactionSelect($columns);
        $select->where(TransactionInterface::KEY_AMOUNT . ' > ?', 0)
            ->where(TransactionInterface::KEY_CREATED_AT . ' >= ?', $this->dateFrom)
            ->where(TransactionInterface::KEY_CREATED_AT . ' <= ?', $this->dateTo);

        return $select;
    }
}
