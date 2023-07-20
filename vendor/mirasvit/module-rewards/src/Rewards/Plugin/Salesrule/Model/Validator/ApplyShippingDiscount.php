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


namespace Mirasvit\Rewards\Plugin\Salesrule\Model\Validator;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\Validator;
use Mirasvit\Rewards\Helper\Calculation;
use Mirasvit\Rewards\Model\Purchase;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class ApplyShippingDiscount
{
    /**
     * @var Address
     */
    private $address;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var Purchase
     */
    private $purchase;

    private $rewardsPurchase;
    private $config;
    private $shippingService;
    private $moduleManager;

    public function __construct(
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Service\ShippingService $shippingService,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->rewardsPurchase = $rewardsPurchase;
        $this->config          = $config;
        $this->shippingService = $shippingService;
        $this->moduleManager   = $moduleManager;
    }

    /**
     * @param Validator $validator
     * @param \callable $proceed
     * @param Address   $address
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundProcessShippingAmount(Validator $validator, $proceed, Address $address)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $returnValue = $proceed($address);

        if ($this->config->getAdvancedSpendingCalculationMethod() != Method::METHOD_ITEMS) {
            return $returnValue;
        }

        if (!$this->config->getDisableRewardsCalculation()) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return $returnValue;
        }

        $this->address = $address;
        $this->quote = $address->getQuote();
        $purchase = $this->getPurchase($this->quote);
        if ($this->quote->getItemsRewardsDiscount() > 0 ||
            (//coupon was used and discount applies only to shipping
                $purchase->getSpendAmount() > 0 &&
                $purchase->getSpendAmount() > $this->quote->getItemsRewardsDiscount()
            )
        ) {
            $this->process();
        }
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);

        return $returnValue;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function process()
    {
        if (!$this->canProcess() || !$this->applyToShipping()) {
            // clear here for next recalculation call
            $this->resetCalcInfo($this->quote);
            return $this;
        }

        $purchase = $this->getPurchase($this->quote);

        $currencyRate = 1;
        if (!$this->isCustomRoundingEnabled()) {
            $currencyRate = $this->quote->getBaseToQuoteRate();
        }

        $baseShippingSpendAmount = $purchase->getBaseSpendAmount() - $this->quote->getBaseItemsRewardsDiscount() -
            $this->quote->getBaseItemsRewardsTaxDiscount();
        if ($baseShippingSpendAmount <= Calculation::ZERO_VALUE) {
            $baseShippingSpendAmount = 0;
        }
        if ($baseShippingSpendAmount <= 0) {
            // clear here for next recalculation call
            $this->resetCalcInfo($this->quote);
            return $this;
        }

        $baseShippingDiscount = $baseShippingSpendAmount;
        $baseShippingDiscount += $this->fixBaseShippingTaxRounding();
        $shippingDiscount = $baseShippingDiscount * $currencyRate;
        $shippingDiscount += $this->fixShippingTaxRounding();
        $shippingDiscount = round($shippingDiscount, 2, PHP_ROUND_HALF_DOWN);

        $shippingDiscount += $this->address->getShippingDiscountAmount();
        $baseShippingDiscount += $this->address->getBaseShippingDiscountAmount();

        $this->address->setShippingDiscountAmount($shippingDiscount);
        $this->address->setBaseShippingDiscountAmount($baseShippingDiscount);

        // clear here for next recalculation call
        $this->resetCalcInfo($this->quote);

        return $this;
    }

    /**
     * @return float
     */
    protected function fixShippingTaxRounding()
    {
        $delta = 0;
        $taxes = $this->address->getItemsAppliedTaxes();
        if (empty($taxes['shipping'])) {
            return $delta;
        }
        $tax = array_shift($taxes['shipping']);
        $shippingTax = $this->getShippingAmount() * $tax['percent'] / 100;

        return abs($this->address->getShippingTaxAmount() - round($shippingTax, 2));
    }

    /**
     * @return float
     */
    protected function fixBaseShippingTaxRounding()
    {
        $delta = 0;
        $taxes = $this->address->getItemsAppliedTaxes();
        if (empty($taxes['shipping'])) {
            return $delta;
        }
        $tax = array_shift($taxes['shipping']);
        $shippingTax = $this->getBaseShippingAmount() * $tax['percent'] / 100;

        return abs($this->address->getBaseShippingTaxAmount() - round($shippingTax, 2));
    }

    /**
     * @return bool
     */
    protected function applyToShipping()
    {
        return $this->config->getGeneralIsSpendShipping();
    }

    /**
     * We need this because Faonni_Price changes total without basetotal
     *
     * @return bool
     */
    protected function isCustomRoundingEnabled()
    {
        return $this->moduleManager->isEnabled('Faonni_Price') &&
            $this->quote->getBaseCurrencyCode() == $this->quote->getQuoteCurrencyCode();
    }

    /**
     * @return float
     */
    private function getShippingAmount()
    {
        return $this->shippingService->getSpendShippingPrice($this->address);
    }

    /**
     * @return float
     */
    private function getBaseShippingAmount()
    {
        return $this->shippingService->getBaseSpendShippingPrice($this->address);
    }

    /**
     * @return bool
     */
    private function canProcess()
    {
        $quote = $this->quote;
        if (!$quote->getId()) {
            return false;
        }

        $purchase = $this->rewardsPurchase->getByQuote($quote);

        if (!$purchase->getSpendAmount()) {
            return false;
        }
        $spendAmount = $purchase->getSpendAmount();
        if ($spendAmount == 0) {
            return false;
        }

        return true;
    }

    /**
     * @param Quote $quote
     *
     * @return bool|Purchase
     */
    private function getPurchase($quote)
    {
        if (empty($this->purchase)) {
            $this->purchase = $this->rewardsPurchase->getByQuote($quote);
        }

        return $this->purchase;
    }

    /**
     * @param Quote $quote
     *
     * @return void
     */
    private function resetCalcInfo($quote)
    {
        $quote->setItemsRewardsDiscount(0);
        $quote->setBaseItemsRewardsDiscount(0);
        $quote->setItemsRewardsTaxDiscount(0);
        $quote->setBaseItemsRewardsTaxDiscount(0);
    }
}
