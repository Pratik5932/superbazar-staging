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



namespace Mirasvit\Rewards\Helper\Balance;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Customer;
use Magento\Quote\Model\Quote;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Model\Earning\Rule;
use Mirasvit\Rewards\Model\Earning\Tier;
use Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory;
use Magento\Catalog\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Framework\Module\Manager;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Main place to calculate earning points
 */
class Earn
{
    const PRICE = 'price';
    const PRICE_WITH_TAX = 'tax_price';

    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory
     */
    private $earningRuleCollectionFactory;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $catalogData;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(
        CollectionFactory $earningRuleCollectionFactory,
        Config $config,
        Data $catalogData,
        StoreManagerInterface $storeManager,
        TaxConfig $taxConfig,
        Manager $moduleManager,
        ProductMetadataInterface $productMetadata
    ) {
        $this->earningRuleCollectionFactory = $earningRuleCollectionFactory;
        $this->config                       = $config;
        $this->catalogData                  = $catalogData;
        $this->storeManager                 = $storeManager;
        $this->taxConfig                    = $taxConfig;
        $this->moduleManager                = $moduleManager;
        $this->productMetadata              = $productMetadata;
    }

    /**
     * @var float
     */
    private $shippingAmount;


    /**
     * @return float
     */
    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }

    /**
     * @param float $shippingAmount
     *
     * @return $this
     */
    public function setShippingAmount($shippingAmount)
    {
        $this->shippingAmount = $shippingAmount;

        return $this;
    }

    /**
     * @return \Mirasvit\Rewards\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Round points depends on earning rounding style in config
     * @param float $points
     * @return float|int
     */
    public function roundPoints($points)
    {
        /** @var mixed $points */
        $points = (string)$points; // some integer numbers of type float after conversion change its values
        if ($this->getConfig()->getAdvancedEarningRoundingStype()) {
            return floor($points);
        } else {
            return ceil($points);
        }
    }

    /**
     * Check if rewards config allows to include tax in earning amount
     * @return bool
     */
    public function isIncludeTax()
    {
        return $this->getConfig()->getGeneralIsIncludeTaxEarning();
    }

    /**
     * Calc cart subtotal for one rule
     * @param \Magento\Quote\Model\Quote           $quote
     * @param \Mirasvit\Rewards\Model\Earning\Rule $rule
     *
     * @return float
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getLimitedSubtotal($quote, $rule)
    {
        $subtotal = 0;
        foreach ($quote->getItemsCollection() as $item) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            if ($item->getParentItemId() || $item->isDeleted()) {
                continue;
            }

            $product = $this->getProductByItem($item);

            if ($item->getDiscountAmount()) {
                $product->setAmountDiscounted($item->getDiscountAmount());
            }

            if ($rule->getActions()->validate($product)) {
                $subtotal += $this->getProductPriceByItem($item);
            }
        }

        if ($this->getConfig()->getGeneralIsEarnShipping() && !$quote->isVirtual()) {
            $subtotal += $this->getShippingAmount();
        }

        if ($this->moduleManager->isEnabled('Mirasvit_Credit')) {
            if ($credit = $quote->getShippingAddress()->getBaseCreditAmount()) {
                $subtotal -= $credit;
            }
        }

        if ($subtotal < 0) {
            $subtotal = 0;
        }

        return $subtotal;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return mixed
     */
    private function getProductByItem($item)
    {
        $product = $item->getProduct();
        $product->setProduct($product);
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $children = [
                $product
            ];
            $product->setChildren($children);
        }

        return $product;
    }

    /**
     * Calc sum of earned points for for Product and Cart Rules
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Customer\Model\Customer $customer
     *
     * @return int number of points
     */
    public function getPointsEarned($quote, $customer)
    {
        return $this->roundPoints($this->getCartPoints($quote, $customer));
    }


    /**
     * Calcs earned points for Cart rules
     * @param Quote $quote
     * @param \Magento\Customer\Model\Customer $customer
     *
     * @return int number of points
     */
    private function getCartPoints($quote, $customer)
    {
        $total = 0;
        $customerGroupId = $customer->getGroupId();
        $websiteId = $quote->getStore()->getWebsiteId();
        $rules = $this->earningRuleCollectionFactory->create()
            ->addWebsiteFilter($websiteId)
            ->addCustomerGroupFilter($customerGroupId)
            ->addCurrentFilter()
            ->addFieldToFilter('type', \Mirasvit\Rewards\Model\Earning\Rule::TYPE_CART);
        $rules->getSelect()->order('sort_order');

        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        foreach ($rules as $rule) {
            $rule->afterLoad();

            /** @var \Magento\Quote\Model\Quote\Address $address */
            if ($quote->getIsVirtual()) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }

            //            do not need in rewards:3.0.0
            //            if (version_compare($this->productMetadata->getVersion(), '2.2.0', '<')) {
            //                foreach ($address->getAllItems() as $item) {// total_qty - allowed only in total collection process
            //                    $address->setTotalQty($address->getTotalQty() + $item->getQty());
            //                }
            //            }
            $address->setCustomer($customer);
            if ($rule->validate($address)) {
                $total += $this->getPointsPerRule($rule, $quote, $customer);
                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }

        return $total;
    }

    /**
     * @param Rule $rule
     * @param Quote $quote
     * @param Customer $customer
     * @return float|int
     */
    private function getPointsPerRule(Rule $rule, Quote $quote, $customer)
    {
        $tier = $rule->getTier($customer);
        switch ($tier->getEarningStyle()) {
            case Config::EARNING_STYLE_GIVE:
                return $this->getGivePoints($rule, $quote, $tier);
            case Config::EARNING_STYLE_AMOUNT_SPENT:
                $subtotal = $this->getLimitedSubtotal($quote, $rule);
                $steps = $tier->getMonetaryStep() > 0 ? $this->roundPoints($subtotal / $tier->getMonetaryStep()) : 0;
                $amount = $steps * $tier->getEarnPoints();
                if ($tier->getPointsLimit() && $amount > $tier->getPointsLimit()) {
                    $amount = $tier->getPointsLimit();
                }
                return $amount;
            case Config::EARNING_STYLE_QTY_SPENT:
                $qty = $this->getAppliedQty($rule, $quote);
                $qty = round($qty/$tier->getQtyStep(), 0, PHP_ROUND_HALF_DOWN);
                $amount = $qty * $tier->getEarnPoints();
                if ($tier->getPointsLimit() && $amount > $tier->getPointsLimit()) {
                    $amount = $tier->getPointsLimit();
                }
                return $amount;
        }
        return 0;
    }

    /**
     * @param Rule  $rule
     * @param Quote $quote
     * @param Tier  $tier
     * @return int
     */
    private function getGivePoints(Rule $rule, Quote $quote, Tier $tier)
    {
        $hasValidProduct = false;
        foreach ($quote->getItemsCollection() as $item) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            if ($item->getParentItemId()) {
                continue;
            }
            if ($rule->getActions()->validate($item)) {
                $hasValidProduct = true;
            }
        }
        return $hasValidProduct ? $tier->getEarnPoints() : 0;
    }

    /**
     * @param Rule $rule
     * @param Quote $quote
     * @return float|int
     */
    private function getAppliedQty(Rule $rule, Quote $quote)
    {
        $qty = 0;
        foreach ($quote->getItemsCollection() as $item) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            if ($item->getParentItemId()) {
                continue;
            }
            if ($rule->getActions()->validate($item)) {
                $qty += $item->getQty();
            }
        }
        return $qty;
    }


    /**
     * Get product price by quote item
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductPriceByItem($item)
    {
        $earnIncludingTax = $this->isIncludeTax();
        $priceIncludesTax = $this->taxConfig->priceIncludesTax();

        $store = $this->storeManager->getStore();
        $store->setCalculateRewardsTax(1);
        if ($priceIncludesTax || (!$this->taxConfig->applyTaxAfterDiscount() && $earnIncludingTax)) {
            $price = $this->getItemPriceWithTax($item);
        } else {
            $price = $this->getItemPriceWithoutTax($item);
        }
        if ($price < 0) {
            $price = 0;
        }
        $store->setCalculateRewardsTax(0);
        if ($priceIncludesTax) {
            $price += (float)$item->getWeeeTaxAppliedAmountInclTax() * $item->getQty();
        }

        return $price;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item  $item
     * @return float
     */
    private function getItemPriceWithTax($item)
    {
        $price = $item->getBasePriceInclTax() * $item->getQty();

        //We should reduce the price for earned points calculation only if "Include discount for Earning rules" is disabled
        if (!$this->getConfig()->getGeneralIsIncludeDiscountEarning()) {
            $price -= $item->getBaseDiscountAmount() + $item->getBaseRewardsDiscountAmount();
        }

        return $price;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float
     */
    private function getItemPriceWithoutTax($item)
    {
        $earnIncludingTax = $this->isIncludeTax();
        $priceIncludesTax = $this->taxConfig->priceIncludesTax();

        $price = $item->getBasePrice() * $item->getQty();
        //if option "Apply Customer Tax" set to "After Discount"
        if ($this->taxConfig->applyTaxAfterDiscount() &&
            !$this->getConfig()->getGeneralIsIncludeDiscountEarning()
        ) {
            $price -= $this->getItemDiscount($item);
            $price -= $item->getBaseRewardsDiscountAmount();
        }

        $shippingAddress = $item->getQuote()->getShippingAddress();
        $billingAddress = false;

        if ($shippingAddress && $shippingAddress->getQuote() && $shippingAddress->getQuote()->getBillingAddress()) {
            $billingAddress = $shippingAddress->getQuote()->getBillingAddress();
        }
        $price = $this->catalogData
            ->getTaxPrice($item->getProduct(), $price, ($priceIncludesTax || $earnIncludingTax),
                $shippingAddress, $billingAddress, null,  $this->storeManager->getStore(), false);

        //if option "Apply Customer Tax" set to "Before Discount"
        if (!$this->taxConfig->applyTaxAfterDiscount() &&
            !$this->getConfig()->getGeneralIsIncludeDiscountEarning()
        ) {
            $price -= $this->getItemDiscount($item);
            $price -= $item->getBaseRewardsDiscountAmount();
        }
        return $price;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float
     */
    private function getItemDiscount($item)
    {
        $discount = $item->getBaseDiscountAmount();
        if ($item->getProductType() == 'bundle' && $discount <= 0.0001) {
            $quote = $item->getQuote();
            foreach ($quote->getItemsCollection() as $bundleItem) {
                if ($bundleItem->getParentItemId() == $item->getId()) {
                    $discount += $bundleItem->getBaseDiscountAmount();
                }
            }
        }

        return $discount;
    }
}
