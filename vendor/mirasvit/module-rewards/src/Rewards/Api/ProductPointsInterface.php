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


namespace Mirasvit\Rewards\Api;

/**
 * Interface for product points calculation
 * @api
 */
interface ProductPointsInterface
{
    /**
     * @param string $sku
     * @param float  $price
     * @param int    $customerId
     * @param int    $websiteId
     * @param int    $tierId
     *
     * @return int
     */
    public function get($sku, $price, $customerId, $websiteId, $tierId);

    /**
     * @param \Mirasvit\Rewards\Api\Data\ProductPointsInterface[] $productInfo
     *
     * @return \Mirasvit\Rewards\Api\Data\ProductPointsResponseInterface[]
     */
    public function getList($productInfo);
}
