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



namespace Mirasvit\Rewards\Helper\Product;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product;
use Magento\Tax\Model\Config as TaxConfig;
use Mirasvit\Rewards\Model\Config as RewardsConfig;
use Mirasvit\Rewards\Service\Product\Bundle\CalcPriceService as BundleCalcPriceService;
use Mirasvit\Rewards\Service\Product\CalcPriceService;

/**
 * Calculates bundle product prices in base currency: min, max and configured(sum of default bundle options) prices
 */
class Bundle
{
    /**
     * @var CatalogHelper
     */
    private $catalogHelper;
    /**
     * @var TaxConfig
     */
    private $taxConfig;
    /**
     * @var CalcPriceService
     */
    private $calcPriceService;
    /**
     * @var RewardsConfig
     */
    private $rewardsConfig;
    /**
     * @var BundleCalcPriceService
     */
    private $bundleCalcPriceService;

    public function __construct(
        CatalogHelper $catalogHelper,
        TaxConfig $taxConfig,
        CalcPriceService $calcPriceService,
        BundleCalcPriceService $bundleCalcPriceService,
        RewardsConfig $rewardsConfig
    ) {
        $this->catalogHelper          = $catalogHelper;
        $this->taxConfig              = $taxConfig;
        $this->calcPriceService       = $calcPriceService;
        $this->rewardsConfig          = $rewardsConfig;
        $this->bundleCalcPriceService = $bundleCalcPriceService;
    }
    /**
     * @param array $options
     * @return float
     */
    public function getConfiguredOptionsPrice($options)
    {
        $price = 0;

        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($options as $option) {
            $selections = $option->getSelections();
            if ($selections === null || !$option->getRequired()) {
                continue;
            }
            foreach ($selections as $selection) {
                if (!$selection->isSalable() || !$selection->getIsDefault()) {
                    continue;
                }
                $productPrice = $selection->getPriceModel()->getBasePrice($selection);
                if ($this->isUseTax()) {
                    $productPrice = $this->catalogHelper->getTaxPrice($selection, $productPrice, true);
                }
                $price += $productPrice;
            }
        }

        return $price;
    }

    /**
     * @param Product $bundleProduct
     * @param array   $options
     * @return float
     */
    public function getMaxOptionsPrice($bundleProduct, $options)
    {
        $price = 0;

        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($options as $option) {
            $selections = $option->getSelections();
            if ($selections === null || !$option->getRequired()) {
                continue;
            }
            $max = 0;
            $selectedProduct = null;
            foreach ($selections as $selection) {
                if (!$selection->isSalable()) {
                    continue;
                }
                $productPrice = $selection->getPriceModel()->getBasePrice($selection);
                $catalogRulePrice = $this->calcPriceService->getBaseCatalogPriceRulePrice($selection);
                if ($catalogRulePrice && $catalogRulePrice < $productPrice) {
                    $productPrice = $catalogRulePrice;
                }
                if ($this->isUseTax()) {
                    $productPrice = $this->catalogHelper->getTaxPrice($selection, $productPrice, true);
                }
                if ($productPrice > $max) {
                    $max = $productPrice;
                    $selectedProduct = $selection;
                }
            }
            if ($max) {
                $price += $this->bundleCalcPriceService
                    ->getOptionPrice($bundleProduct, $selectedProduct, $this->isUseTax());
            }
        }

        return $price;
    }

    /**
     * @param Product $bundleProduct
     * @param array   $options
     * @return float
     */
    public function getMinOptionsPrice($bundleProduct, $options)
    {
        $price = 0;

        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($options as $option) {
            $selections = $option->getSelections();
            if ($selections === null || !$option->getRequired()) {
                continue;
            }
            $min = 999999999;
            $selectedProduct = null;
            foreach ($selections as $selection) {
                if (!$selection->isSalable()) {
                    continue;
                }
//                $productPrice = $selection->getPriceModel()->getBasePrice($selection);

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                /** @var \Magento\Bundle\Pricing\Price\BundleSelectionFactory $selectionFactory */
                $selectionFactory = $objectManager->create('Magento\Bundle\Pricing\Price\BundleSelectionFactory');
                /** @var \Magento\Bundle\Pricing\Price\BundleSelectionPrice $priceObj */
                $typeId = $bundleProduct->getTypeId();
                $bundleProduct->setTypeId( \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
                $priceObj = $selectionFactory->create(
                    $bundleProduct,
                    $selection,
                    $selection->getSelectionQty(),
                    ['useRegularPrice' => false]
                );
                $productPrice = $priceObj->getValue();
                $bundleProduct->setTypeId($typeId);

                $catalogRulePrice = $this->calcPriceService->getBaseCatalogPriceRulePrice($selection);
                if ($catalogRulePrice && $catalogRulePrice < $productPrice) {
                    $productPrice = $catalogRulePrice;
                }

                if ($this->isUseTax()) {
                    $productPrice = $this->catalogHelper->getTaxPrice($selection, $productPrice, true);
                }
                if ($productPrice < $min) {
                    $min = $productPrice;
                    $selectedProduct = $selection;
                }
            }
            if ($min != 999999999) {
                $price += $this->bundleCalcPriceService
                    ->getOptionPrice($bundleProduct, $selectedProduct, $this->isUseTax());

//                if ($this->isUseTax()) {
//                    $price += $this->bundleCalcPriceService->getOptionPrice($bundleProduct, $selectedProduct);
//                } else {
//                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//                    /** @var \Magento\Bundle\Pricing\Price\BundleSelectionFactory $selectionFactory */
//                    $selectionFactory = $objectManager->create('Magento\Bundle\Pricing\Price\BundleSelectionFactory');
//                    /** @var \Magento\Bundle\Pricing\Price\BundleSelectionPrice $priceObj */
//                    $typeId = $bundleProduct->getTypeId();
//                    $bundleProduct->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
//                    $priceObj = $selectionFactory->create(
//                        $bundleProduct,
//                        $selectedProduct,
//                        $selectedProduct->getSelectionQty(),
//                        ['useRegularPrice' => false]
//                    );
//                    $price += $priceObj->getValue();
//                }
            }
        }

        return $price;
    }

    /**
     * @return bool
     */
    private function isUseTax()
    {
        return $this->taxConfig->getPriceDisplayType() !== 1 && $this->rewardsConfig->getGeneralIsIncludeTaxEarning();
    }

}