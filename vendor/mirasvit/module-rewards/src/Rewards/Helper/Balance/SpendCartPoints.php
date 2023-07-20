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

use Mirasvit\Rewards\Api\Config\Rule\SpendingStyleInterface;
use Mirasvit\Rewards\Helper\Calculation;

class SpendCartPoints
{
    private $customerFactory;

    /**
     * @var \Mirasvit\Rewards\Helper\Balance
     */
    private $rewardsBalance;
    /**
     * @var SpendRulesList
     */
    private $spendRulesHelper;
    /**
     * @var Spend\QuoteSubtotalCalc
     */
    private $quoteSubtotalCalc;
    /**
     * @var Spend\RuleQuoteSubtotalCalc
     */
    private $ruleQuoteSubtotalCalc;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        \Mirasvit\Rewards\Helper\Balance\SpendRulesList $spendRulesHelper,
        \Mirasvit\Rewards\Helper\Balance\Spend\QuoteSubtotalCalc $quoteSubtotalCalc,
        \Mirasvit\Rewards\Helper\Balance\Spend\RuleQuoteSubtotalCalc $ruleQuoteSubtotalCalc
    ) {
        $this->customerFactory       = $customerFactory;
        $this->spendRulesHelper      = $spendRulesHelper;
        $this->quoteSubtotalCalc     = $quoteSubtotalCalc;
        $this->ruleQuoteSubtotalCalc = $ruleQuoteSubtotalCalc;
        $this->rewardsBalance        = $rewardsBalance;
    }

    /**
     * Check if spend points amount is valid for quote
     *
     * @param \Magento\Quote\Model\Quote                    $quote
     * @param int                                           $pointsNumber
     * @param \Magento\Quote\Model\Quote\Address\Total|null $totals
     * @return \Magento\Framework\DataObject
     */
    public function getCartPoints($quote, $pointsNumber, $totals)
    {
        $totalPoints     = 0;
        $totalBaseAmount = 0;

        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->customerFactory->create()->load($quote->getCustomer()->getId());

        if ($quote->getItemVirtualQty() > 0) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        $address->setCustomer($customer);
        $rules = $this->spendRulesHelper->getRules($quote);
        /** @var \Mirasvit\Rewards\Model\Spending\Rule $rule */
        foreach ($rules as $rule) {
            $rule->afterLoad();
            if ($pointsNumber > 0 && $rule->validate($address)) {
                $subtotal = $this->ruleQuoteSubtotalCalc->getLimitedSubtotal($quote, $rule);
                $tier     = $rule->getTier($customer);

                $ruleMaxPoints    = $tier->getSpendMaxAmount($subtotal);
                $rulePointsNumber = $pointsNumber;
                if ($ruleMaxPoints && $pointsNumber > $ruleMaxPoints) {
                    $rulePointsNumber = $ruleMaxPoints;
                }
                $spendPoints = $tier->getSpendPoints();
                if (!$spendPoints) {
                    continue;
                }
                if ($tier->getSpendingStyle() == SpendingStyleInterface::STYLE_PARTIAL) {
                    $stepsSecond = round($rulePointsNumber / $spendPoints, 2, PHP_ROUND_HALF_DOWN);
                } else {
                    $roundedRulePointsNumber = floor(floor($rulePointsNumber / $spendPoints) * $spendPoints);
                    if ($roundedRulePointsNumber > 0 && $rulePointsNumber > $roundedRulePointsNumber) {
                        $rulePointsNumber = $roundedRulePointsNumber + $spendPoints;
                    } else {
                        $rulePointsNumber = $roundedRulePointsNumber;
                    }
                    $stepsSecond = floor($rulePointsNumber / $spendPoints);
                }

                $monetaryStep = $tier->getMonetaryStep($subtotal);
                if ($monetaryStep <= Calculation::ZERO_VALUE) {
                    continue;
                }

                if ($rulePointsNumber < $tier->getSpendMinAmount($subtotal)) {
                    continue;
                }
                $stepsFirst = round($subtotal / $monetaryStep, 2, PHP_ROUND_HALF_DOWN);
                if ($stepsFirst != $subtotal / $monetaryStep) {
                    ++$stepsFirst;
                }

                $steps = min($stepsFirst, $stepsSecond);

                $amount = $steps * $monetaryStep;
                $amount = min($amount, $subtotal);
                $totalBaseAmount += $amount;

                $pointsNumber = $pointsNumber - $rulePointsNumber;
                $totalPoints += $rulePointsNumber;

                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }
        $quoteSubTotal = $this->quoteSubtotalCalc->getQuoteSubtotal($quote, $totals);

        if ($totalBaseAmount > $quoteSubTotal) {//due to rounding we can have some error
            $totalBaseAmount = $quoteSubTotal;
        }
        $totalAmount = $totalBaseAmount;
        if ($quote->getBaseCurrencyCode() != $quote->getQuoteCurrencyCode()) {
            $totalAmount = $totalBaseAmount * $quote->getBaseToQuoteRate();
            $totalAmount = round($totalAmount, 2);
        }

        return new \Magento\Framework\DataObject([
            'points'      => $totalPoints,
            'base_amount' => $totalBaseAmount,
            'amount'      => $totalAmount,
        ]);
    }
}