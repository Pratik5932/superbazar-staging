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
use Mirasvit\Rewards\Model\Config;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\QuoteFactory;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Apply rewards discount before tax calculations
 * @package Mirasvit\Rewards\Plugin
 */
class ShippingDataObject
{
    private   $config;

    private   $purchaseHelper;

    private   $quoteFactory;

    protected $taxConfig;

    public function __construct(
        Config $config,
        PurchaseHelper $purchaseHelper,
        QuoteFactory $quoteFactory,
        TaxConfig $taxConfig
    ) {
        $this->config         = $config;
        $this->purchaseHelper = $purchaseHelper;
        $this->quoteFactory   = $quoteFactory;
        $this->taxConfig      = $taxConfig;
    }

    /**
     * @param CommonTaxCollector          $commonTaxCollector
     * @param \callable                   $proceed
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param QuoteAddress\Total          $total
     * @param bool                        $useBaseCurrency
     *
     * @return QuoteDetailsItemInterface[]
     */
    public function aroundGetShippingDataObject(
        CommonTaxCollector $commonTaxCollector,
        $proceed,
        ShippingAssignmentInterface $shippingAssignment,
        QuoteAddress\Total $total,
        $useBaseCurrency
    ) {
        \Magento\Framework\Profiler::start(__CLASS__ . ':' . __METHOD__);
        $returnValue = $proceed($shippingAssignment, $total, $useBaseCurrency);
        $items       = $shippingAssignment->getItems();

        if (!count($items)) {
            return $returnValue;
        }

        $item = array_shift($items);
        $quote = $item->getQuote();

        $purchase = $this->purchaseHelper->getByQuote($quote->getId());

        if ($purchase && $purchase->getSpendAmount() > 0 && !$this->taxConfig->discountTax($quote->getStore())
            && $this->config->getGeneralIsIncludeTaxSpending($quote->getStore())
            && $this->config->getGeneralApplyTaxAfterSpendingDiscount($quote->getStore()))
        {
            $baseShippingSpendAmount = $purchase->getBaseSpendAmount() - $shippingAssignment->getItemRewardsSubtotalDiscountAmount();
            if ($baseShippingSpendAmount > 0) {
                $returnValue['discount_amount'] += $baseShippingSpendAmount;
            }
        }

        \Magento\Framework\Profiler::stop(__CLASS__ . ':' . __METHOD__);

        return $returnValue;
    }
}
