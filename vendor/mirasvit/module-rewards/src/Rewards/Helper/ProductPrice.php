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



namespace Mirasvit\Rewards\Helper;

use \Magento\Catalog\Model\Product;
use Mirasvit\Rewards\Api\Data\Earning\RuleInterface;
use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Helper\Product\Bundle as BundleHelper;
use Mirasvit\Rewards\Model\Config as Config;
use Mirasvit\Rewards\Service\Product\Bundle\CalcPriceService as BundleCalcPriceService;
use Mirasvit\Rewards\Service\Product\CalcPriceService;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Registry;
use \Mirasvit\Rewards\Model\Earning\Rule;
use  \Magento\Quote\Model\Quote;

/**
 * Calculate product price
 */
class ProductPrice
{
    /**
     * @var \Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface
     */
    private $lowestPriceOptionsProvider;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var BundleCalcPriceService
     */
    private $bundleCalcPriceService;
    /**
     * @var BundleHelper
     */
    private $bundleHelper;
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;
    /**
     * @var CalcPriceService
     */
    private $calcPriceService;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $catalogData;
    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    public function __construct(
        Registry $registry,
        BundleCalcPriceService $bundleCalcPriceService,
        BundleHelper $bundleHelper,
        StockRegistryInterface $stockRegistry,
        CalcPriceService $calcPriceService,
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->registry                 = $registry;
        $this->bundleHelper                 = $bundleHelper;
        $this->bundleCalcPriceService       = $bundleCalcPriceService;
        $this->stockRegistry                = $stockRegistry;
        $this->calcPriceService             = $calcPriceService;
        $this->config                       = $config;
        $this->catalogData                  = $catalogData;
        $this->taxConfig                    = $taxConfig;
        $interface = 'Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface';
        if (interface_exists($interface)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->lowestPriceOptionsProvider = $objectManager->create($interface);
        }

    }


    /**
     * Price with/without tax. Depends on settings of rwp.
     * Used to calculate points.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     *
     * @return float
     */
    public function getProductPrice(Product $product, $priceType = 'final_price')
    {
        \Magento\Framework\Profiler::start(__METHOD__);
        $bundleProduct = $this->registry->registry('current_product');
        if ($this->isBundle($product)) {
            $price = $this->getBundlePrice($product, $priceType);
        } elseif ($bundleProduct && $this->isBundle($bundleProduct) && $bundleProduct->getId() != $product->getId()) {
            // bundle options
            $price = $this->bundleCalcPriceService->getOptionPrice($bundleProduct, $product, $this->isUseTax());
        } elseif ($this->isConfigurable($product)) {
            $price = $this->getConfigurablePrice($product);
        } else {
            $price = $this->getSimpleProductPrice($product);
        }
        \Magento\Framework\Profiler::stop(__METHOD__);

        return $price;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    private function getSimpleProductPrice(Product $product)
    {
        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        $qty = max((float)$stockItem->getMinSaleQty(), 1);
        $price = $product->getPriceModel()->getBasePrice($product, $qty);

        $catalogRulePrice = $this->calcPriceService->getBaseCatalogPriceRulePrice($product);
        if ($catalogRulePrice && $catalogRulePrice < $price) {
            $price = $catalogRulePrice;
        }
        if (!$price) {
            $price = $product->getPriceInfo()->getPrice('base_price')->getAmount()->getValue();
        } elseif ($this->isUseTax()) {
            $price = $this->catalogData->getTaxPrice($product, $price, true);
        }
        return $price;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    private function getConfigurablePrice(Product $product)
    {
        $price = $product->getPriceModel()->getBasePrice($product);
        if ($this->lowestPriceOptionsProvider) {
            foreach ($this->lowestPriceOptionsProvider->getProducts($product) as $subProduct) {
                $price            = $subProduct->getPriceModel()->getBasePrice($subProduct);
                $catalogRulePrice = $this->calcPriceService->getBaseCatalogPriceRulePrice($subProduct);
                if ($catalogRulePrice && $catalogRulePrice < $price) {
                    $price = $catalogRulePrice;
                }
            }
        }
        if ($this->isUseTax()) {
            $price = $this->catalogData->getTaxPrice($product, $price, true);
        }
        return $price;
    }

    /**
     * Get bundle product price in base currency depends on price type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string                         $priceType
     *
     * @return float
     */
    private function getBundlePrice(Product $product, $priceType)
    {
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);

        /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionCollection */
        $optionCollection = $typeInstance->getOptionsCollection($product);

        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product),
            $product
        );

        $options = $optionCollection->appendSelections($selectionCollection, true, false);
        $bundlePrice = 0;
        if ($priceType == 'minPrice') {
            $bundlePrice = $this->bundleHelper->getMinOptionsPrice($product, $options);
        } elseif ($priceType == 'maxPrice') {
            $bundlePrice = $this->bundleHelper->getMaxOptionsPrice($product, $options);
        } elseif ($priceType == 'configured_price') {
            $bundlePrice = $this->bundleHelper->getConfiguredOptionsPrice($options);
        }

        return $bundlePrice;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    private function isBundle(Product $product)
    {
        return $product->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    private function isConfigurable(Product $product)
    {
        return $product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
    }


    /**
     * Check if product price contains tax
     * @return bool
     */
    private function isUseTax()
    {
        return $this->taxConfig->getPriceDisplayType() !== 1 && $this->config->getGeneralIsIncludeTaxEarning();
    }
}