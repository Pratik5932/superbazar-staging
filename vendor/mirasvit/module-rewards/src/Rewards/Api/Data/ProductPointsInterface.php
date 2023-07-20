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
 * Interface for product points api request
 * @api
 */
interface ProductPointsInterface
{
    const KEY_SKU = 'sku';
    const KEY_PRICE = 'price';
    const KEY_CUSTOMER_ID = 'customr_id';
    const KEY_WEBSITE_ID = 'website_id';
    const KEY_TIER_ID = 'tier_id';

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
     * @return float
     */
    public function getPrice();

    /**
     * @param float $price
     *
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @return int
     */
    public function getWebsiteId();

    /**
     * @param int $websiteId
     *
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * @return int
     */
    public function getTierId();

    /**
     * @param int $tierId
     *
     * @return $this
     */
    public function setTierId($tierId);
}
