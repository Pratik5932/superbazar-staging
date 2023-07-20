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



namespace Mirasvit\RewardsCatalog\Helper;

use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Model\Config as Config;
use \Magento\Customer\Model\Customer;
use \Magento\Catalog\Model\Product;
/**
 * Calculate earning points for product page
 */
class Earn
{
    /**
     * @var array
     */
    private $productPageRules = [];
    /**
     * @var \Mirasvit\Rewards\Helper\ProductPrice
     */
    private $productPriceHelper;
    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory
     */
    private $earningRuleCollectionFactory;
    /**
     * @var \Mirasvit\Rewards\Helper\Balance\Earn
     */
    private $earnHelper;
    /**
     * @var EarnProductPage
     */
    private $earnProductPageHelper;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    public function __construct(
        \Mirasvit\Rewards\Helper\Balance\Earn $earnHelper,
        \Mirasvit\RewardsCatalog\Helper\EarnProductPage $earnProductPageHelper,
        \Mirasvit\Rewards\Helper\ProductPrice $productPriceHelper,
        \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory $earningRuleCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->productPriceHelper = $productPriceHelper;
        $this->earningRuleCollectionFactory = $earningRuleCollectionFactory;
        $this->earnHelper = $earnHelper;
        $this->earnProductPageHelper = $earnProductPageHelper;
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Use only for points calculations.
     *
     * @param Product $product
     * @param Customer $customer
     * @param int $websiteId
     * @param bool $round
     * @return float|int
     */
    public function getProductFloatPoints(Product $product, Customer $customer, $websiteId, $round)
    {
        \Magento\Framework\Profiler::start(__METHOD__);
        $points = 0;
        $productRules = $this->getProductPageRules($customer, $websiteId);
        if ($productRules->count()) {
            $price = $this->productPriceHelper->getProductPrice($product, "final_price");
            $points = $this->earnProductPageHelper->getProductPagePoints(
                $product,
                $price,
                $customer,
                $websiteId
            );
            if ($round) {
                $points = $this->earnHelper->roundPoints($points);
            }
        }
        \Magento\Framework\Profiler::stop(__METHOD__);

        return $points;
    }

    /**
     * Prepare earning Product rules
     *
     * @param Customer $customer
     * @param int $websiteId
     * @return \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\Collection
     */
    public function getProductPageRules($customer, $websiteId)
    {
        if (empty($this->productPageRules[$websiteId][$customer->getGroupId()])) {
            $rules = $this->earningRuleCollectionFactory->create()
                ->addWebsiteFilter($websiteId)
                ->addCustomerGroupFilter($customer->getGroupId())
                ->addCurrentFilter()
                ->addProductPageFilter();
            $rules->getSelect()->order('sort_order');

            $this->productPageRules[$websiteId][$customer->getGroupId()] = $rules;
        }

        return $this->productPageRules[$websiteId][$customer->getGroupId()];
    }



    /**
     * Function returns true for grouped or bundled products if after adding to the cart
     * customer may receive product points
     *
     * @param Product       $product
     * @param Customer       $customer
     * @param int                             $websiteId
     * @return bool|true
     */
    public function getIsProductPointsPossible(Product $product, Customer $customer, $websiteId)
    {
        if (!$product) {
            return false;
        }

        $possibleNotstandardProducts = [
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
            \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE,
        ];

        if (!in_array($product->getTypeId(), $possibleNotstandardProducts)) {
            return false;
        }

        $rules = $this->getProductPageRules($customer, $websiteId);

        return $rules->count() > 0;
    }

    /**
     * Calculates the number of points for some product.
     *
     * @param Product $product
     * @param Customer $customer
     * @param int $websiteId
     * @return float|int
     */
    public function getProductPoints(
        Product $product,
        Customer $customer,
        $websiteId
    ) {
        $price = $this->productPriceHelper->getProductPrice($product);

        $product->setCustomer($customer); //why we need this???

        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        $minAllowed = max((float)$stockItem->getMinSaleQty(), 1);
        $rulePrice = $minAllowed * $price;

        $total = 0;
        $rules = $this->getProductPageRules($customer, $websiteId);
        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        foreach ($rules as $rule) {
            $tier = $rule->getTier($customer);
            $rule->afterLoad();

            // prepere product for actions validation
            $product->setProduct($product);
            if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $children = [
                    $product
                ];
                $product->setChildren($children);
            }

            if ($tier->getEarnPoints() &&
                $rulePrice >= $tier->getMonetaryStep() &&
                $rule->validate($product) && $rule->getActions()->validate($product)
            ) {
                switch ($tier->getEarningStyle()) {
                    case Config::EARNING_STYLE_GIVE:
                        $total += $tier->getEarnPoints();
                        break;

                    case Config::EARNING_STYLE_AMOUNT_SPENT:
                    case Config::EARNING_STYLE_AMOUNT_PRICE:
                        $step = $this->earnHelper->roundPoints($price / $tier->getMonetaryStep());

                        $amount = $step * $tier->getEarnPoints();
                        if ($tier->getPointsLimit() && $amount > $tier->getPointsLimit()) {
                            $amount = $tier->getPointsLimit();
                        }
                        $total += $amount;
                        if ($rule->getProductNotification()) {
                            $this->earnProductPageHelper->addProductMessage($product->getId(), $rule->getId(), $rule->getProductNotification());
                        }
                        break;
                    case Config::EARNING_STYLE_QTY_SPENT:
                        $qty = $product->getQty() ?: 1;
                        $qty = round($qty/$tier->getQtyStep(), 0, PHP_ROUND_HALF_DOWN);
                        $amount = $qty * $tier->getEarnPoints();
                        if ($tier->getPointsLimit() && $amount > $tier->getPointsLimit()) {
                            $amount = $tier->getPointsLimit();
                        }
                        $total += $amount;
                        break;
                }

                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }

        return $total;
    }

    /**
     * @param float $points
     * @return float
     */
    public function roundPoints($points)
    {
        return $this->earnHelper->roundPoints($points);
    }

    /**
     * Calculates the number of points for the product in API call.
     *
     * @api
     * @todo merge with getProductPoints
     *
     * @param \Magento\Catalog\Model\Product               $product
     * @param float                                        $price
     * @param int                                          $tierId
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int|bool                                     $websiteId
     *
     * @return int number of points
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPointsByTier(
        $product,
        $price,
        $tierId,
        $customer = null,
        $websiteId = 1
    ) {
        if (!($product instanceof \Magento\Catalog\Model\Product)) {
            $product = $this->productFactory->create()->load($product->getId());
        }
        $product->setCustomer($customer);
        $stockItem  = $this->stockRegistry->getStockItem($product->getId(), $websiteId);
        $minAllowed = max((float)$stockItem->getMinSaleQty(), 1);
        $rulePrice  = $minAllowed * $price;

        $total = 0;
        $rules = $this->getProductPageRules($customer, $websiteId);
        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        foreach ($rules as $rule) {
            $tears = $rule->getTiersSerialized();
            if ($tierId) {
                if (isset($tears[$tierId])) {
                    $tierData = $tears[$tierId];
                } else {
                    $tierData = $rule->getDefaultTierData();
                }
            } else {
                $tierData = array_shift($tears);
            }
            $rule->afterLoad();
            if ($tierData['earn_points'] &&
                $rulePrice >= $tierData['monetary_step'] &&
                $rule->validate($product)
            ) {
                switch ($tierData['earning_style']) {
                    case Config::EARNING_STYLE_GIVE:
                        $total += $tierData['earn_points'];
                        break;

                    case Config::EARNING_STYLE_AMOUNT_SPENT:
                    case Config::EARNING_STYLE_AMOUNT_PRICE:
                        $amount = $price / $tierData['monetary_step'] * $tierData['earn_points'];

                        if ( $tierData['points_limit'] && $amount > $tierData['points_limit'] ) {
                            $amount = $tierData['points_limit'];
                        }
                        $total += $amount;
                        break;
                }

                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }

        return $total;
    }
}