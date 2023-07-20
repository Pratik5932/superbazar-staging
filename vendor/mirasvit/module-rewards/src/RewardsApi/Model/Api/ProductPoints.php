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

use Mirasvit\Rewards\Api\Data\ProductPointsInterface;

class ProductPoints extends \Magento\Framework\DataObject implements ProductPointsInterface
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
    public function getPrice()
    {
        return $this->getData(self::KEY_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        $this->setData(self::KEY_PRICE, $price);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::KEY_CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        $this->setData(self::KEY_CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteId()
    {
        return $this->getData(self::KEY_WEBSITE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setWebsiteId($websiteId)
    {
        $this->setData(self::KEY_WEBSITE_ID, $websiteId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTierId()
    {
        return $this->getData(self::KEY_TIER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setTierId($tierId)
    {
        $this->setData(self::KEY_TIER_ID, $tierId);

        return $this;
    }
}