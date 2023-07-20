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



namespace Mirasvit\Rewards\Model\Config\Source\Spending;

class ApplyTax implements \Magento\Framework\Option\ArrayInterface
{
    const APPLY_SPENDING_TAX_DEFAULT = 'default';
    const APPLY_SPENDING_AFTER_TAX   = 'after_tax';
    // we can not implement it now.
//    const APPLY_SPENDING_BEFORE_TAX  = 'before_tax';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::APPLY_SPENDING_TAX_DEFAULT, 'label' => __('Use Magento settings')],
            ['value' => self::APPLY_SPENDING_AFTER_TAX, 'label' => __('After Tax')],
//            ['value' => self::APPLY_SPENDING_BEFORE_TAX, 'label' => __('Before Tax')],
        ];
    }
}
