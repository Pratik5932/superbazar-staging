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
use Mirasvit\Rewards\Model\ResourceModel\Purchase\CollectionFactory as PurchaseCollectionFactory;
use Magento\Framework\App\ResourceConnection;

class Purchase extends AbstractCron
{
    private $resource;

    private $purchaseCollectionFactory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        PurchaseCollectionFactory $purchaseCollectionFactory,
        ResourceConnection        $resource,
        Filesystem                $filesystem
    ) {
        parent::__construct($filesystem);

        $this->purchaseCollectionFactory = $purchaseCollectionFactory;
        $this->resource                  = $resource;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    protected function execute()
    {
        $resource           = $this->resource;
        $beforeClearing     = $this->purchaseCollectionFactory->create()->count();

        $purchaseTable = $resource->getTableName('mst_rewards_purchase');
        $deleteSql           = "DELETE FROM $purchaseTable WHERE spend_points is null AND earn_points is null";
        $resource->getConnection()->query($deleteSql);
        $updateSql           = "UPDATE $purchaseTable SET quote_id = 0 WHERE order_id is not null";
        $resource->getConnection()->query($updateSql);

        $afterClearing = $this->purchaseCollectionFactory->create()->count();
        $totalRemoved  = $beforeClearing - $afterClearing;

        echo "Were cleared " . $totalRemoved . " records" . PHP_EOL;
    }
}
