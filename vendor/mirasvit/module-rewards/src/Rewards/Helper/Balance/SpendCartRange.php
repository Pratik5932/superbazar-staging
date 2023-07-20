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

class SpendCartRange
{
    private $customerFactory;

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

    /**
     * @var \Mirasvit\Rewards\Helper\Balance
     */
    private $rewardsBalance;


    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        \Mirasvit\Rewards\Helper\Balance\SpendRulesList $spendRulesHelper,
        \Mirasvit\Rewards\Helper\Balance\Spend\QuoteSubtotalCalc $quoteSubtotalCalc,
        \Mirasvit\Rewards\Helper\Balance\Spend\RuleQuoteSubtotalCalc $ruleQuoteSubtotalCalc
    ) {
        $this->customerFactory       = $customerFactory;
        $this->rewardsBalance        = $rewardsBalance;
        $this->spendRulesHelper      = $spendRulesHelper;
        $this->quoteSubtotalCalc     = $quoteSubtotalCalc;
        $this->ruleQuoteSubtotalCalc = $ruleQuoteSubtotalCalc;
    }

    /**
     * Calcs min and max amount of spend points for quote
     *
     * @param \Magento\Quote\Model\Quote               $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $totals
     *
     * @return \Magento\Framework\DataObject
     */
    public function getCartRange($quote, $totals)
    {
        $rules = $this->spendRulesHelper->getRules($quote);
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer      = $this->customerFactory->create()->load($quote->getCustomer()->getId());
        $balancePoints = $this->rewardsBalance->getBalancePoints($quote->getCustomerId());

        if ($quote->getItemVirtualQty() > 0) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        $address->setCustomer($customer);

        $minPoints     = 0;
        $totalPoints   = 0;
        $quoteSubTotal = $this->quoteSubtotalCalc->getQuoteSubtotal($quote, $totals);

        $data = new SpendCartRangeData($quoteSubTotal, $balancePoints, $minPoints, $totalPoints);
        /** @var \Mirasvit\Rewards\Model\Spending\Rule $rule */
        foreach ($rules as $rule) {
            $rule->afterLoad();

            if ($rule->validate($address)) {
                $data = $this->calcPointsPerRule($customer, $quote, $rule, $data);

                if (!$data) {
                    $this->ruleQuoteSubtotalCalc->unsetItemsForRule($rule->getId());
                    continue;
                }

                if (($rule->getIsStopProcessing() && ($data->minPoints > 0 || $data->maxPoints > 0)) || $quoteSubTotal <= 0) {
                    break;
                }
            }
        }

        if ($data->minPoints > $data->maxPoints) {
            $data->minPoints = $data->maxPoints = 0;
        }

        return new \Magento\Framework\DataObject([
            'min_points'  => $data->minPoints,
            'max_points'  => $data->maxPoints,
            'item_points' => $this->ruleQuoteSubtotalCalc->getItemIds(),
        ]);
    }

    /**
     * @param \Magento\Customer\Model\Customer      $customer
     * @param \Magento\Quote\Model\Quote            $quote
     * @param \Mirasvit\Rewards\Model\Spending\Rule $rule
     * @param SpendCartRangeData                    $data
     *
     * @return SpendCartRangeData
     */
    private function calcPointsPerRule($customer, $quote, $rule, SpendCartRangeData $data)
    {
        $ruleSubTotal = $this->ruleQuoteSubtotalCalc->getLimitedSubtotal($quote, $rule);
        if ($ruleSubTotal <= Calculation::ZERO_VALUE) {
            return $data;
        }
        if ($ruleSubTotal > $data->subtotal) {
            $ruleSubTotal = $data->subtotal;
        }
        $tier = $rule->getTier($customer);

        $monetaryStep    = $tier->getMonetaryStep($ruleSubTotal);
        $ruleMinPoints   = $tier->getSpendMinAmount($ruleSubTotal);
        $ruleMaxPoints   = $tier->getSpendMaxAmount($ruleSubTotal);
        $ruleSpendPoints = $tier->getSpendPoints();

        if (!$this->isRuleValid($ruleMinPoints, $ruleMaxPoints, $monetaryStep, $ruleSpendPoints, $data)) {
            return $data;
        }

        $ruleMinPoints = $ruleMinPoints ? max($ruleMinPoints, $ruleSpendPoints) : $ruleSpendPoints;

        $data->minPoints = $data->minPoints ? min($data->minPoints, $ruleMinPoints) : $ruleMinPoints;

        if ($tier->getSpendingStyle() == SpendingStyleInterface::STYLE_FULL) {
            $roundedTotalPoints = floor($ruleMaxPoints / $ruleSpendPoints) * $ruleSpendPoints;
            if ($roundedTotalPoints < $ruleMaxPoints) {
                $ruleMaxPoints = $roundedTotalPoints + $ruleSpendPoints;
            } else {
                $ruleMaxPoints = $roundedTotalPoints;
            }
            if ($ruleMinPoints <= $ruleMaxPoints) {
                $data->subtotal  -= $ruleMaxPoints / $ruleSpendPoints * $monetaryStep;
                $data->maxPoints += $ruleMaxPoints;
            }
            if ($data->maxPoints > $data->balancePoints) {
                $data->maxPoints = floor($data->balancePoints / $ruleSpendPoints) * $ruleSpendPoints;
            }
        } elseif ($ruleMinPoints <= $ruleMaxPoints) {
            $data->subtotal  -= $ruleMaxPoints / $ruleSpendPoints * $monetaryStep;
            $data->maxPoints += $ruleMaxPoints;
        }

        return $data;
    }

    /**
     * @param float              $ruleMinPoints
     * @param float              $ruleMaxPoints
     * @param float              $monetaryStep
     * @param float              $ruleSpendPoints
     * @param SpendCartRangeData $data
     *
     * @return bool
     */
    private function isRuleValid($ruleMinPoints, $ruleMaxPoints, $monetaryStep, $ruleSpendPoints, SpendCartRangeData $data)
    {
        if ($ruleMinPoints > $ruleMaxPoints) {
            return false;
        }
        if ($ruleMinPoints && ($data->subtotal / $monetaryStep) < 1) {
            return false;
        }
        if ($ruleMinPoints > $data->balancePoints || $ruleSpendPoints <= Calculation::ZERO_VALUE) {
            return false;
        }
        if ($monetaryStep <= Calculation::ZERO_VALUE) {
            return false;
        }

        return true;
    }
}
