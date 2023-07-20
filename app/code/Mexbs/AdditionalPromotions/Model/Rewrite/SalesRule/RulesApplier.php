<?php
namespace Mexbs\AdditionalPromotions\Model\Rewrite\SalesRule;

class RulesApplier extends \Magento\SalesRule\Model\RulesApplier
{
    protected $itemValidationResultFactory;

    public function __construct(
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $calculatorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\SalesRule\Model\Utility $utility,
        \Mexbs\AdditionalPromotions\Model\ItemValidationResultFactory $itemValidationResultFactory
    ) {
        parent::__construct(
            $calculatorFactory,
            $eventManager,
            $utility
        );

        $this->itemValidationResultFactory = $itemValidationResultFactory;
    }

    public function applyRules($item, $rules, $skipValidation, $couponCode)
    {
        $address = $item->getAddress();
        $appliedRuleIds = [];
        /* @var $rule \Magento\SalesRule\Model\Rule */
        foreach ($rules as $rule) {
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }

            /**
             * @var \Mexbs\AdditionalPromotions\Model\ItemValidationResult $itemValidation
             */
            $itemValidationResult = $this->itemValidationResultFactory->create();
            $this->_eventManager->dispatch('salesrule_item_validate_for_rule',
                [
                    'item_validation_result' => $itemValidationResult,
                    'item' => $item,
                    'rule' => $rule,
                    'applies_rules' => true
                ]);

            if (!$skipValidation
                &&
                (
                    !$rule->getActions()->validate($item)
                    || ($itemValidationResult->getResult() == false)
                )
            ) {
                $childItems = $item->getChildren();
                $isContinue = true;
                if (!empty($childItems)) {
                    foreach ($childItems as $childItem) {
                        if ($rule->getActions()->validate($childItem)
                            && ($itemValidationResult->getResult() == true)) {
                            $isContinue = false;
                        }
                    }
                }
                if ($isContinue) {
                    continue;
                }
            }

            $this->applyRule($item, $rule, $address, $couponCode);
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }

        return $appliedRuleIds;
    }
}