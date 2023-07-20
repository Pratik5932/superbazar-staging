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

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Customer\Model\CustomerFactory;
use Mirasvit\Rewards\Api\Config\Rule\SpendingStyleInterface;
use Mirasvit\Rewards\Helper\Balance;
use Mirasvit\Rewards\Helper\Balance\Spend\ChargesCalc;
use Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\CollectionFactory;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Service\RoundService;

/**
 * Used only for "Apply to items" method
 *
 * @package Mirasvit\Rewards\Helper\Balance
 */
class Spend extends \Magento\Framework\App\Helper\AbstractHelper
{
    public static $itemPoints = [];

    private $validItems = [];

    private $customerFactory;

    private $currencyHelper;

    private $rewardsBalance;

    private $spendingRuleCollectionFactory;

    private $config;

    private $roundService;

    private $spendChargesCalc;

    private $spendRulesHelper;

    private $context;

    public function __construct(
        CustomerFactory $customerFactory,
        CurrencyHelper $currencyHelper,
        Balance $rewardsBalance,
        SpendRulesList $spendRulesHelper,
        ChargesCalc $spendChargesCalc,
        CollectionFactory $spendingRuleCollectionFactory,
        Config $config,
        RoundService $roundService,
        Context $context
    ) {
        $this->customerFactory               = $customerFactory;
        $this->currencyHelper                = $currencyHelper;
        $this->rewardsBalance                = $rewardsBalance;
        $this->spendRulesHelper              = $spendRulesHelper;
        $this->spendingRuleCollectionFactory = $spendingRuleCollectionFactory;
        $this->config                        = $config;
        $this->roundService                  = $roundService;
        $this->spendChargesCalc              = $spendChargesCalc;
        $this->context                       = $context;

        parent::__construct($context);
    }

    /**
     * Calcs min and max amount of spend points for quote
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Framework\DataObject
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getCartRange($quote)
    {
        self::$itemPoints = [];

        $rules         = $this->spendRulesHelper->getRules($quote);
        $customer      = $quote->getCustomer();
        $balancePoints = $this->rewardsBalance->getBalancePoints($quote->getCustomerId());

        $minPoints     = 0;
        $totalPoints   = 0;
        $quoteSubTotal = $this->getQuoteSubtotal($quote);

        /** @var \Mirasvit\Rewards\Model\Spending\Rule $rule */
        foreach ($rules as $rule) {
            $rule->afterLoad();

            if ($quote->getItemVirtualQty() > 0) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }

            if (!$address->getCustomer()) {
                $customer = $this->customerFactory->create()->load($customer->getId());

                $address->setCustomer($customer);
            }

            if ($rule->validate($address)) {

                $ruleSubTotal = $this->getLimitedSubtotal($quote, $rule);
                if ($ruleSubTotal > $quoteSubTotal) {
                    $ruleSubTotal = $quoteSubTotal;
                }

                $tier = $rule->getTier($customer);

                $monetaryStep = $tier->getMonetaryStep($ruleSubTotal);
                if (!$monetaryStep) {
                    unset($this->validItems[$rule->getId()]);
                    continue;
                }

                $ruleMinPoints   = $tier->getSpendMinAmount($ruleSubTotal);
                $ruleMaxPoints   = $tier->getSpendMaxAmount($ruleSubTotal);
                $ruleSpendPoints = $tier->getSpendPoints();
                if (($ruleMinPoints && ($quoteSubTotal / $monetaryStep) < 1) || $ruleMinPoints > $ruleMaxPoints
                    || $ruleMinPoints > $balancePoints) {
                    unset($this->validItems[$rule->getId()]);
                    continue;
                }

                $ruleMinPoints = $ruleMinPoints ? max($ruleMinPoints, $ruleSpendPoints) : $ruleSpendPoints;

                $minPoints = $minPoints ? min($minPoints, $ruleMinPoints) : $ruleMinPoints;

                if ($ruleMinPoints <= $ruleMaxPoints) {
                    $quoteSubTotal -= $ruleMaxPoints / $ruleSpendPoints * $monetaryStep;
                    $totalPoints   += $ruleMaxPoints;
                }

                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }

        foreach ($this->validItems as $ruleId => $items) {
            self::$itemPoints = array_merge(self::$itemPoints, $items);
        }

        if ($minPoints > $totalPoints) {
            $minPoints = $totalPoints = 0;
        }

        return new \Magento\Framework\DataObject(['min_points' => $minPoints, 'max_points' => $totalPoints]);
    }

    /**
     * Check if spend points amount is valid for quote
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $pointsNumber
     * @return \Magento\Framework\DataObject
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getCartPoints($quote, $pointsNumber)
    {
        $customer = $quote->getCustomer();
        $rules    = $this->spendRulesHelper->getRules($quote);

        $totalAmount = 0;
        $totalPoints = 0;

        /** @var \Mirasvit\Rewards\Model\Spending\Rule $rule */
        foreach ($rules as $rule) {
            $rule->afterLoad();

            if ($quote->getItemVirtualQty() > 0) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }

            if ($pointsNumber > 0 && $rule->validate($address)) {
                $tier = $rule->getTier($customer);

                $subtotal         = $this->getLimitedSubtotal($quote, $rule);
                $ruleMaxPoints    = $tier->getSpendMaxAmount($subtotal);
                $rulePointsNumber = $pointsNumber;

                if ($ruleMaxPoints && $pointsNumber > $ruleMaxPoints) {
                    $rulePointsNumber = $ruleMaxPoints;
                }

                if ($tier->getSpendingStyle() == SpendingStyleInterface::STYLE_PARTIAL) {
                    $stepsSecond = round($rulePointsNumber / $tier->getSpendPoints(), 2, PHP_ROUND_HALF_DOWN);
                } else {
                    $spendPoints = $tier->getSpendPoints();
                    $rulePointsNumber = floor($rulePointsNumber / $spendPoints) * $spendPoints;
                    $stepsSecond = floor($rulePointsNumber / $spendPoints);
                }

                if ($rulePointsNumber < $tier->getSpendMinAmount($subtotal)) {
                    continue;
                }

                $stepsFirst = round($subtotal / $tier->getMonetaryStep($subtotal), 2, PHP_ROUND_HALF_DOWN);
                if ($stepsFirst != $subtotal / $tier->getMonetaryStep($subtotal)) {
                    ++$stepsFirst;
                }

                $steps = min($stepsFirst, $stepsSecond);

                $amount = $steps * $tier->getMonetaryStep($subtotal);
                $amount = min($amount, $subtotal);

                $totalAmount += $amount;

                $pointsNumber = $pointsNumber - $rulePointsNumber;
                $totalPoints += $rulePointsNumber;

                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }

        $quoteSubTotal = $this->getQuoteSubtotal($quote);
        if ($totalAmount > $quoteSubTotal) {//due to rounding we can have some error
            $totalAmount = $quoteSubTotal;
        }

        return new \Magento\Framework\DataObject(['points' => $totalPoints, 'amount' => $totalAmount]);
    }

    /**
     * Get quote subtotal depends on rewards tax settings
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return float
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getQuoteSubtotal($quote)
    {
        if ($this->isRewardsIncludeTax()) {
            $subtotal = $quote->getBaseGrandTotal();
        } else {
            $subtotal = $quote->getBaseSubtotalWithDiscount();
            if ($this->config->getGeneralIsSpendShipping() && !$quote->isVirtual()) {
                $subtotal += $this->getShippingAmount($quote);
            }
        }

        if ((!$quote->getAwUseStoreCredit() || $quote->getIncludeSurcharge()) &&
            $quote->getBaseMagecompSurchargeAmount() && $subtotal
        ) {
            $subtotal += $quote->getBaseMagecompSurchargeAmount();
        }

        if ($quote->getAwUseStoreCredit() &&
            !$quote->getIncludeSurcharge() &&
            $subtotal >= $quote->getBaseMagecompSurchargeAmount() &&
            ($quote->getBaseAwStoreCreditAmount() + $quote->getBaseMagecompSurchargeAmount()) > $subtotal
        ) {
            $subtotal -= $quote->getBaseMagecompSurchargeAmount();
        }

        if ($subtotal < 0) { // compatibility with Aheadworks Store Credit
            $subtotal = 0;
        }

        return $subtotal;
    }

    /**
     * Calcs quote subtotal for the rule
     * @param \Magento\Quote\Model\Quote            $quote
     * @param \Mirasvit\Rewards\Model\Spending\Rule $rule
     *
     * @return float
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getLimitedSubtotal($quote, $rule)
    {
        $subtotal = 0;
        $priceIncludesTax = $this->isRewardsIncludeTax();

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getItemsCollection() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            if ($rule->getActions()->validate($item)) {
                if ($priceIncludesTax) {
                    $itemPrice = $item->getBasePrice();
                    if ($this->isRewardsIncludeTax()) {
                        $itemPrice = $item->getBasePriceInclTax();
                    }
                    $itemPrice += (float)$item->getWeeeTaxAppliedAmountInclTax();
                    $itemPrice = $itemPrice * $item->getQty() - $item->getBaseDiscountAmount();
                } else {
                    $itemPrice = $item->getBasePrice() * $item->getQty() - $item->getBaseDiscountAmount();
                }

                if ($this->roundService->isFaonniRoundEnabled()) {
                    $subtotal += $this->roundService->faonniRound($itemPrice / $item->getQty()) * $item->getQty();
                } else {
                    $subtotal += $itemPrice;
                }

                $this->validItems[$rule->getId()][] = $item->getId();
            }
        }

        if ($this->config->getGeneralIsSpendShipping() && !$quote->isVirtual()) {
            $subtotal += $this->getShippingAmount($quote);
        }

        if ((!$quote->getAwUseStoreCredit() || $quote->getIncludeSurcharge()) &&
            $quote->getBaseMagecompSurchargeAmount() && $subtotal
        ) {
            $subtotal += $quote->getBaseMagecompSurchargeAmount();
        }

        if ($quote->getAwUseStoreCredit() &&
            !$quote->getIncludeSurcharge() &&
            $subtotal >= $quote->getBaseMagecompSurchargeAmount() &&
            ($quote->getBaseAwStoreCreditAmount() + $quote->getBaseMagecompSurchargeAmount()) > $subtotal
        ) {
            $subtotal -= $quote->getBaseMagecompSurchargeAmount();
        }

        $subtotal -= $quote->getBaseAmastyGift();

        if ($subtotal < 0) {
            $subtotal = 0;
        }

        return $subtotal;
    }

    /**
     * @return bool
     */
    public function isRewardsIncludeTax()
    {
        return $this->config->getGeneralIsIncludeTaxSpending();
    }

    /**
     * Get quote shipping amount depends on rewards ta settings
     * @param \Magento\Quote\Model\Quote $quote
     * @return float
     */
    private function getShippingAmount($quote)
    {
        $shippingAddress = $quote->getShippingAddress();

        if ($quote->getCartShippingMethod()) {
            $shippingAddress->setCollectShippingRates(true)->setShippingMethod(
                $quote->getCartShippingCarrier() . '_' . $quote->getCartShippingMethod()
            );
            $shippingAddress->collectShippingRates();
        }

        return $this->spendChargesCalc->getBaseRewardsShippingPrice($shippingAddress);
    }
}
