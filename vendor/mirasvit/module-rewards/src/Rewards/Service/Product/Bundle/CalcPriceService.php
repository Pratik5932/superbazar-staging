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



namespace Mirasvit\Rewards\Service\Product\Bundle;

use Magento\CatalogRule\Model\ResourceModel\Rule as CatalogRule;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Rewards\Service\Currency\Calculation;

class CalcPriceService
{
    /**
     * @var Calculation
     */
    private $calculation;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var TimezoneInterface
     */
    private $dateTime;
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;
    /**
     * @var CatalogRule
     */
    private $ruleResource;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        CatalogRule $ruleResource,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        TimezoneInterface $dateTime,
        PriceCurrencyInterface $priceCurrency,
        Calculation $calculation
    ) {
        $this->calculation     = $calculation;
        $this->customerSession = $customerSession;
        $this->dateTime        = $dateTime;
        $this->priceCurrency   = $priceCurrency;
        $this->ruleResource    = $ruleResource;
        $this->storeManager    = $storeManager;
    }

    /**
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param SaleableInterface|\Magento\Bundle\Model\Selection $product
     * @param bool $useTax
     * @return float
     */
    public function getOptionPrice($bundleProduct, $product, $useTax)
    {
        /** @var \Magento\Bundle\Pricing\Price\BundleOptionPrice $optionPriceModel */
        $optionPriceModel = $bundleProduct->getPriceInfo()->getPrice('bundle_option');
        $amount = $optionPriceModel->getOptionSelectionAmount($product);
        $price = $amount->getValue($useTax ? null : 'tax');

        $price = $this->convertPrice($price);

        return $price;
    }

    /**
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @return float
     */
    public function getDisplayPrice($bundleProduct)
    {
        /** @var \Magento\Bundle\Pricing\Price\BundleRegularPrice $regularPriceModel */
        $regularPriceModel = $bundleProduct->getPriceInfo()->getPrice('regular_price');
        $minimalRegularPrice = $regularPriceModel->getMinimalPrice();
        /** @var \Magento\Bundle\Pricing\Price\FinalPrice $finalPriceModel */
        $finalPriceModel = $bundleProduct->getPriceInfo()->getPrice('final_price');
        $minimalPrice = $finalPriceModel->getMinimalPrice();
        $price = $minimalRegularPrice->getValue();
        if ($minimalPrice->getValue() < $minimalRegularPrice->getValue()) {
            $price = $minimalPrice->getValue();
        }

        $price = $this->convertPrice($price);

        return $price;
    }

    /**
     * @param float $price
     *
     * @return float
     */
    private function convertPrice($price)
    {
        $store = $this->storeManager->getStore();
        $baseCurrency = $store->getBaseCurrency();
        $priceCurrency = $this->priceCurrency->getCurrency();
        if ($priceCurrency->getCode() != $baseCurrency->getCode()) {
            $price = $this->calculation->convertToCurrency($price, $priceCurrency, $baseCurrency, $store);
        } else {
            $price = $this->priceCurrency->round($price);
        }

        return $price;
    }
}