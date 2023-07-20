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



namespace Mirasvit\Rewards\Plugin;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Validator;
use Mirasvit\Rewards\Service\Discount\ItemDiscountService;

/**
 * @see \Magento\SalesRule\Model\Validator::initTotals
 */
class ApplyItemDiscountValidatorTotalsPlugin
{
    private $isApplyAfterRule = false;

    private $itemDiscountService;

    public function __construct(
        ItemDiscountService $itemDiscountService
    ) {
        $this->itemDiscountService = $itemDiscountService;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterInitTotals(Validator $subject, ?Validator $result, array $items, Address $address): ?Validator
    {
        $rules = $subject->getRules($address);

        // getRules was added in m2.4.4. If we have rules, SalesRuleValidator plugin was called and calced discount.
        if ($rules && count($rules) && !$this->isApplyAfterRule) {
            $this->isApplyAfterRule = true;

            return $result;
        }

        /** @var AbstractItem $item */
        foreach ($items as $item) {
            $this->itemDiscountService->applyDiscount($item);
        }

        return $result;
    }
}
