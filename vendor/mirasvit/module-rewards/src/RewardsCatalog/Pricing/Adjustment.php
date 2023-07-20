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


namespace Mirasvit\RewardsCatalog\Pricing;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\SaleableInterface;

use Mirasvit\Rewards\Model\Config;

class Adjustment implements AdjustmentInterface
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var null
     */
    private $sortOrder;
    /**
     * Adjustment code rewards points
     */
    const ADJUSTMENT_CODE = 'rewards_earn';

    /**
     * Adjustment constructor.
     *
     * @param Config $config
     * @param int $sortOrder
     */
    public function __construct(Config $config, $sortOrder = null)
    {
        $this->config    = $config;
        $this->sortOrder = $sortOrder;
    }

    /**
     * Get adjustment code
     *
     * @return string
     */
    public function getAdjustmentCode()
    {
        return self::ADJUSTMENT_CODE;
    }

    /**
     * Define if adjustment is included in base price
     *
     * @return bool
     */
    public function isIncludedInBasePrice()
    {
        return false;// for some products discount excluded twice when prices (incl, excl) are displays
    }

    /**
     * Define if adjustment is included in display price
     *
     * @return bool
     */
    public function isIncludedInDisplayPrice()
    {
        return $this->config->getDisplayOptionsIsShowPointsOnProductPage() ||
        $this->config->getDisplayOptionsIsShowPointsOnFrontend();
    }

    /**
     * Extract adjustment amount from the given amount value
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|array $context
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function extractAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        return 0;
    }

    /**
     * Apply adjustment amount and return result value
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|array $context
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function applyAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        return $amount;
    }

    /**
     * Check if adjustment should be excluded from calculations along with the given adjustment
     *
     * @param string $adjustmentCode
     * @return bool
     */
    public function isExcludedWith($adjustmentCode)
    {
        return $this->getAdjustmentCode() === $adjustmentCode;
    }

    /**
     * Return sort order position
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }
}
