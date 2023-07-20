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



namespace Mirasvit\RewardsApi\Model\Api;

use Mirasvit\Rewards\Api\Data\ProductPointsResponseInterface;

class ProductPointsResponse extends \Magento\Framework\DataObject implements ProductPointsResponseInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->getData(self::KEY_SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function setSku($sku)
    {
        $this->setData(self::KEY_SKU, $sku);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPoints()
    {
        return $this->getData(self::KEY_POINTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setPoints($points)
    {
        $this->setData(self::KEY_POINTS, $points);

        return $this;
    }
}