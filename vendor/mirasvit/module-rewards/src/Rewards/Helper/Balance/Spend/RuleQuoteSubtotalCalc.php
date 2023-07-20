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


namespace Mirasvit\Rewards\Helper\Balance\Spend;

use Mirasvit\Rewards\Model\Config\Source\Spending\ApplyTax;

class RuleQuoteSubtotalCalc
{
    /**
     * @var array
     */
    private $validItems = [];
    /**
     * @var \Mirasvit\Rewards\Helper\Balance
     */
    private $rewardsBalance;
    /**
     * @var \Mirasvit\Rewards\Model\Config
     */
    private $config;
    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;
    /**
     * @var \Mirasvit\Rewards\Service\RoundService
     */
    private $roundService;
    /**
     * @var \Mirasvit\Rewards\Service\ShippingService
     */
    private $shippingService;
    /**
     * @var ChargesCalc
     */
    private $chargesCalc;

    public function __construct(
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Tax\Model\Config $taxConfig,
        \Mirasvit\Rewards\Service\ShippingService $shippingService,
        ChargesCalc $chargesCalc,
        \Mirasvit\Rewards\Service\RoundService $roundService
    ) {
        $this->rewardsBalance  = $rewardsBalance;
        $this->config          = $config;
        $this->taxConfig       = $taxConfig;
        $this->shippingService = $shippingService;
        $this->roundService    = $roundService;
        $this->chargesCalc     = $chargesCalc;
    }

    /**
     * If tax applied after discount
     *
     * @return bool
     */
    private function isApplyTaxAfterDiscount()
    {
        return $this->config->getGeneralApplyTaxAfterSpendingDiscount() == ApplyTax::APPLY_SPENDING_TAX_DEFAULT &&
            $this->taxConfig->applyTaxAfterDiscount() && !$this->taxConfig->priceIncludesTax();
    }

    /**
     * @param int $ruleId
     * @return void
     */
    public function unsetItemsForRule($ruleId)
    {
        unset($this->validItems[$ruleId]);
    }

    /**
     * Returns ids of items, which satisfy reward rules.
     *
     * @return array
     */
    public function getItemIds()
    {
        $itemPoints = [];
        foreach ($this->validItems as $items) {
            $itemPoints = array_merge($itemPoints, $items);
        }
        return array_unique($itemPoints);
    }

    /**
     * Calcs quote subtotal for the rule
     * @param \Magento\Quote\Model\Quote            $quote
     * @param \Mirasvit\Rewards\Model\Spending\Rule $rule
     *
     * @return float
     */
    public function getLimitedSubtotal($quote, $rule)
    {
        $subtotal = 0;

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getItemsCollection() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            /** @var \Magento\SalesRule\Model\Rule\Condition\Product\Combine $actions */
            $actions = $rule->getActions();
            if ($actions->validate($item)) {
                $itemSubtotal = $this->getItemSubtotal($item);
                $subtotal += $itemSubtotal;

                if ($itemSubtotal) {
                    $this->validItems[$rule->getId()][] = $item->getId();
                }
            }
        }
        $subtotal += $this->chargesCalc->getShippingAmount($quote);

        $subtotal = $this->chargesCalc->applyAdditionalCharges($quote, $subtotal);

        return $subtotal;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float
     */
    private function getItemSubtotal($item)
    {
        $priceIncludesTax = $this->config->getGeneralIsIncludeTaxSpending();
        // Spend Incl Tax = true
        if ($priceIncludesTax) {
            // ApplyTaxAfterDiscount = true
            $itemPrice = $item->getBaseRowTotalInclTax();

            if (!$this->taxConfig->discountTax($item->getStore()) && $this->isApplyTaxAfterDiscount()) {
                $itemPrice = $item->getBasePrice() * $item->getQty() - $item->getBaseDiscountAmount();
            }

            // for now we are ignore paying tax amount when isApplyTaxAfterDiscount = true
            if (!$this->isApplyTaxAfterDiscount()) {
                $itemPrice = $item->getBasePriceInclTax() * $item->getQty();
                // if item wasn't totally paid with coupon, we add tax amount
                if ($itemPrice != $item->getBaseDiscountAmount()) {
                    $itemPrice  = $item->getBaseRowTotal() + $item->getBaseTaxAmount();
                    $itemPrice += $item->getBaseDiscountTaxCompensationAmount();
                }
            }
        } else { // Spend Incl Tax = false
            $itemPrice = $item->getBasePrice() * $item->getQty() - $item->getBaseDiscountAmount();
        }
        if ($this->roundService->isFaonniRoundEnabled()) {
            return $this->roundService->faonniRound($itemPrice / $item->getQty()) * $item->getQty();
        } else {
            return $itemPrice;
        }
    }
}
