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
 * @see \Magento\SalesRule\Model\Validator::process()
 * @see \Magento\SalesRule\Model\Validator::prepareDescription()
 */
class SalesRuleValidator
{
    private $itemDiscountService;

    public function __construct(
        ItemDiscountService $itemDiscountService
    ) {
        $this->itemDiscountService = $itemDiscountService;
    }

    /**
     * @param Validator    $validator
     * @param AbstractItem $item
     *
     * @return Validator
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(Validator $validator, Validator $result, AbstractItem $item)
    {
        $this->itemDiscountService->applyDiscount($item);

        return $result;
    }

    /**
     * @param Validator $validator
     * @param Address   $address
     * @param string    $separator
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePrepareDescription(Validator $validator, $address, $separator = ', ')
    {
        $this->itemDiscountService->applyDiscountDescription($address);

        return [$address, $separator];
    }
}
