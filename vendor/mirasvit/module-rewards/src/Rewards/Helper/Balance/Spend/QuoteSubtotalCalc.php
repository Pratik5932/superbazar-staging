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


class QuoteSubtotalCalc
{
    private $shippingAmount;

    private $config;

    private $moduleManager;

    private $shippingService;

    private $chargesCalc;

    public function __construct(
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Service\ShippingService $shippingService,
        ChargesCalc $chargesCalc,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->config          = $config;
        $this->moduleManager   = $moduleManager;
        $this->shippingService = $shippingService;
        $this->chargesCalc     = $chargesCalc;
    }

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
     * Get quote subtotal depends on rewards tax settings
     * @param \Magento\Quote\Model\Quote               $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $totals
     *
     * @return float
     */
    public function getQuoteSubtotal($quote, $totals)
    {
        if ($totals) {
            if ($this->config->getGeneralIsIncludeTaxSpending()) {
                // grand total
                $rewardsTotal = $totals->getBaseGrandTotal();
                if (!$this->config->getGeneralIsSpendShipping() && !$quote->isVirtual()) {
                    $rewardsTotal -= $totals->getBaseTotalAmount('shipping');
                }
                if ($rewardsTotal < 0) {
                    $rewardsTotal = 0;
                }
            } else {
                //subtotal + -discount
                $rewardsTotal = $totals->getBaseSubtotalWithDiscount();
                if ($this->config->getGeneralIsSpendShipping() && !$quote->isVirtual()) {
                    $rewardsTotal += $totals->getBaseTotalAmount('shipping');
                }
            }
        } else {
            if ($this->config->getGeneralIsIncludeTaxSpending()) {
                $rewardsTotal = $quote->getBaseGrandTotal();
            } else {
                $rewardsTotal = $quote->getBaseSubtotalWithDiscount();
            }

            $rewardsTotal += $this->chargesCalc->getShippingAmount($quote);
            $rewardsTotal = $this->chargesCalc->applyAdditionalCharges($quote, $rewardsTotal);
            $rewardsTotal += $quote->getBaseItemsRewardsDiscount();
        }

        return $rewardsTotal;
    }
}
