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



namespace Mirasvit\Rewards\Service\Quote\Item;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Tax\Model\TaxDetails\ItemDetails;
use Mirasvit\Rewards\Helper\Purchase as PurchaseHelper;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Model\Config\Source\Spending\ApplyTax;
use Mirasvit\Rewards\Model\Purchase;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

class CalcPriceService
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @var float
     */
    private $taxRounding = 0;

    /**
     * @var float
     */
    private $baseTaxRounding = 0;

    /**
     * @var PurchaseHelper
     */
    private $purchaseHelper;

    public function __construct(
        TaxConfig $taxConfig,
        PurchaseHelper $purchaseHelper,
        Config $config
    ) {
        $this->taxConfig      = $taxConfig;
        $this->purchaseHelper = $purchaseHelper;
        $this->config         = $config;
    }

    /**
     * @param AbstractItem[]|\Magento\Quote\Api\Data\CartItemInterface[] $items
     * @param Purchase                                                   $purchase
     * @return array
     */
    public function getQuotePrices($items, $purchase)
    {
        $prices = [];

        $i = 0;

        $itemsAmount = count($items);

        /** @var AbstractItem $item */
        foreach ($items as $item) {
            $i++;

            if ($this->config->getAdvancedSpendingCalculationMethod() != Method::METHOD_ITEMS &&
                !in_array($item->getId(), $purchase->getQuoteProductIds())
            ) {
                continue;
            }

            $prices[$item->getId()] = $this->getPrices($item, $itemsAmount == $i);
        }

        return $prices;
    }

    /**
     * @param AbstractItem $item
     * @param bool         $applyCompensation
     * @return array
     */
    private function getPrices(AbstractItem $item, $applyCompensation = false)
    {
        //price with TAX
        if ($item->getData(OrderItemInterface::TAX_PERCENT) > 0) {
            $tax     = 0;
            $baseTax = 0;
            $itemPrice     = $item->getData(OrderItemInterface::ROW_TOTAL);
            $itemBasePrice = $item->getData(OrderItemInterface::BASE_ROW_TOTAL);

            if ($this->config->getGeneralIsIncludeTaxSpending()) {
                if ($this->isApplyTaxAfterDiscount()) {
                    $itemPrice     -= $item->getData(OrderItemInterface::DISCOUNT_AMOUNT);
                    $itemBasePrice -= $item->getData(OrderItemInterface::BASE_DISCOUNT_AMOUNT);
                }

                $tax     = $itemPrice * $item->getData(OrderItemInterface::TAX_PERCENT) / 100 + $this->taxRounding;
                $baseTax = $itemBasePrice *
                    $item->getData(OrderItemInterface::TAX_PERCENT) / 100 + $this->baseTaxRounding;

                $tax     = $this->fixedTaxCalc($item, $tax);
                $baseTax = $this->fixedTaxCalc($item, $baseTax);

                $tax     += $item->getData(OrderItemInterface::WEEE_TAX_APPLIED_ROW_AMOUNT);
                $baseTax += $item->getData(OrderItemInterface::BASE_WEEE_TAX_APPLIED_ROW_AMNT);
                $tax     += $item->getData(OrderItemInterface::DISCOUNT_TAX_COMPENSATION_AMOUNT);
                $baseTax += $item->getData(OrderItemInterface::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT);
            }

            $itemTotalPrice     = $itemPrice;
            $baseItemTotalPrice = $itemBasePrice;

            if (!$this->isApplyTaxAfterDiscount()) {
                $itemTotalPrice     += $tax;
                $baseItemTotalPrice += $baseTax;
                $itemTotalPrice     -= $item->getData(OrderItemInterface::DISCOUNT_AMOUNT);
                $baseItemTotalPrice -= $item->getData(OrderItemInterface::BASE_DISCOUNT_AMOUNT);
            }
        } else { // for bundle products price does not mark as percent
            if ($this->config->getGeneralIsIncludeTaxSpending()) {
                $itemTotalPrice     = $item->getData(OrderItemInterface::ROW_TOTAL_INCL_TAX);
                $baseItemTotalPrice = $item->getData(OrderItemInterface::BASE_ROW_TOTAL_INCL_TAX);
            } else {
                $itemTotalPrice     = $item->getData(OrderItemInterface::ROW_TOTAL);
                $baseItemTotalPrice = $item->getData(OrderItemInterface::BASE_ROW_TOTAL);
            }

            $itemTotalPrice     -= $item->getData(OrderItemInterface::DISCOUNT_AMOUNT);
            $baseItemTotalPrice -= $item->getData(OrderItemInterface::BASE_DISCOUNT_AMOUNT);
        }

        if ($applyCompensation) {
            $itemTotalPrice     += $this->taxRounding;
            $baseItemTotalPrice += $this->baseTaxRounding;
        }

        return [
            'price'     => round($itemTotalPrice, 2),
            'basePrice' => round($baseItemTotalPrice, 2),
        ];
    }

    /**
     * Fix tax rounding
     *
     * @param AbstractItem $item
     * @param float $tax
     * @return float
     */
    private function fixedTaxCalc($item, $tax)
    {
        $appliedTax      = (array)$item->getAppliedTaxes();
        $compensationTax = $item->getData(ItemDetails::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT);

        if ((!$appliedTax || !count($appliedTax)) && !$compensationTax) {
            $taxFixed = round($tax, 2);

            if ($taxFixed - $tax >= 0.005) {
                $this->taxRounding     = $taxFixed - $tax - 0.01;
                $this->baseTaxRounding = $taxFixed - $tax - 0.01;
            }

            return $taxFixed;
        }

        $amount = 0;
        foreach ($appliedTax as $v) {
            $amount += $v['amount'];
        }

        if (!$amount) {
            $amount = $compensationTax;
        }

        $diff = abs($tax - $amount);
        if ($diff < 0.02) { // we need this due to Magento rounding
            $rounding = 0;

            if ($diff - 0.005 > 0) {
                $rounding = $diff - 0.005;
                $this->baseTaxRounding = $this->taxRounding = $rounding;
            }

            $tax = $amount - $rounding;
        }

        return $tax;
    }

    /**
     * If tax applied after discount
     *
     * @return bool
     */
    private function isApplyTaxAfterDiscount()
    {
        return $this->taxConfig->applyTaxAfterDiscount() && !$this->taxConfig->priceIncludesTax() &&
            $this->config->getGeneralApplyTaxAfterSpendingDiscount() == ApplyTax::APPLY_SPENDING_TAX_DEFAULT;
    }
}