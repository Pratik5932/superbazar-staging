<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\Plugin\Sales\Order;

use Magento\Framework\App\ResourceConnection;

/**
 * Grid Class for order purchase point.
 */
class Grid
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function afterSearch($interceptor, $collection)
    {
        if ($collection->getMainTable() === $this->resourceConnection->getTableName('sales_order_grid')) {

            $leftJoinTableName = $this->resourceConnection->getTableName('mobikul_orderPurchasePoint');

            $collection->getSelect()->joinLeft(
                ['co' => $leftJoinTableName],
                "co.order_id = main_table.entity_id",
                ['purchase_point' => 'co.purchase_point']
            );
        }

        return $collection;
    }
}
