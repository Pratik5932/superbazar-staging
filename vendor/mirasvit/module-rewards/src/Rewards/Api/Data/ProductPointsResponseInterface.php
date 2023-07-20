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


namespace Mirasvit\Rewards\Api\Data;

/**
 * Interface for product points api response
 * @api
 */
interface ProductPointsResponseInterface
{
    const KEY_SKU = 'sku';
    const KEY_POINTS = 'points';

    /**
     * @return string
     */
    public function getSku();

    /**
     * @param string $sku
     *
     * @return $this
     */
    public function setSku($sku);

    /**
     * @return int
     */
    public function getPoints();

    /**
     * @param int $points
     *
     * @return $this
     */
    public function setPoints($points);
}
