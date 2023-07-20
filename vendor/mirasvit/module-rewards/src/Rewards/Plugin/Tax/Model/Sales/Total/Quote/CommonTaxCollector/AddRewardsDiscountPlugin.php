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


namespace Mirasvit\Rewards\Plugin\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Mirasvit\Rewards\Helper\Purchase as PurchaseHelper;
use Mirasvit\Rewards\Model\Config\Source\Spending\ApplyTax;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

/**
 * Apply rewards discount before tax calculations
 *
 * @package Mirasvit\Rewards\Plugin
 */
class AddRewardsDiscountPlugin
{
    private $config;

    private $purchaseHelper;

    public function __construct(
        Config $config,
        PurchaseHelper $purchaseHelper
    ) {
        $this->config         = $config;
        $this->purchaseHelper = $purchaseHelper;
    }

    /**
     * @param CommonTaxCollector          $commonTaxCollector
     * @param \callable                   $proceed
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param bool                        $priceIncludesTax
     * @param bool                        $useBaseCurrency
     * @return QuoteDetailsItemInterface[]
     */
    public function aroundMapItems(CommonTaxCollector $commonTaxCollector,
                                    $proceed,
                                    ShippingAssignmentInterface $shippingAssignment,
                                    $priceIncludesTax,
                                    $useBaseCurrency
    ) {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $returnValue = $proceed($shippingAssignment, $priceIncludesTax, $useBaseCurrency);

        if ($this->config->getAdvancedSpendingCalculationMethod() == Method::METHOD_ITEMS) {
            return $returnValue;
        }

        $items = $shippingAssignment->getItems();
        if (!count($items) || !count($returnValue)) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);

            return $returnValue;
        }
        if ($this->config->getGeneralApplyTaxAfterSpendingDiscount() == ApplyTax::APPLY_SPENDING_AFTER_TAX) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);

            return $returnValue;
        }

        $parentItems = [];
        $usedItems = [];
        $totalSum  = 0;
        foreach ($items as $item) {
            /** @var \Magento\Quote\Model\Quote\Item  $item */
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $parentItems[$item->getId()] = $item->getQty();
                continue;
            }
        }
        foreach ($items as $item) {
            /** @var \Magento\Quote\Model\Quote\Item  $item */
            if ($item->getHasChildren() && $item->isChildrenCalculated()) { // see CommonTaxCollector::mapItems
                continue;
            }

            $parentQty = 1;
            if ($item->getParentItemId() && isset($parentItems[$item->getParentItemId()])) {
                $parentQty = $parentItems[$item->getParentItemId()];
            }
            if ($useBaseCurrency) {
                $itemPrice = $item->getBaseTaxCalculationPrice() * $item->getQty() * $parentQty - $item->getBaseDiscountAmount();
            } else {
                $itemPrice = $item->getTaxCalculationPrice() * $item->getQty() * $parentQty - $item->getDiscountAmount();
            }
            $totalSum += $itemPrice;
            $usedItems[$item->getTaxCalculationItemId()] = $itemPrice;
        }
        if (!$totalSum) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);

            return $returnValue;
        }

        $item = array_shift($items);
        $purchase = $this->purchaseHelper->getByQuote($item->getQuote());
        if (!$purchase || $purchase->getSpendAmount() <= 0) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);

            return $returnValue;
        }

        if ($useBaseCurrency) {
            $spendAmount = $purchase->getBaseSpendAmount();
        } else {
            $spendAmount = $purchase->getSpendAmount();
        }

        if ($spendAmount > $totalSum) {
            $spendAmount = $totalSum;
        }

        $rewardsItemsDiscountAmount = 0;

        /** @var \Magento\Tax\Model\Sales\Quote\ItemDetails $itemDetails */
        foreach ($returnValue as $itemDetails) {
            if (!isset($usedItems[$itemDetails->getCode()])) {
                continue;
            }
            $itemPrice       = $usedItems[$itemDetails->getCode()];
            $rewardsDiscount = $itemPrice / $totalSum * $spendAmount;

            $rewardsItemsDiscountAmount += $rewardsDiscount;

            $itemDetails->setDiscountAmount($itemDetails->getDiscountAmount() + (float)$rewardsDiscount);
        }

        $shippingAssignment->setItemRewardsSubtotalDiscountAmount($rewardsItemsDiscountAmount);
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);

        return $returnValue;
    }
}
