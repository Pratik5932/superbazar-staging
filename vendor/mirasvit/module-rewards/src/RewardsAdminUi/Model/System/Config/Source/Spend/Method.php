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



namespace Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend;

class Method
{
    const METHOD_ITEMS  = 'items';
    const METHOD_TOTALS = 'totals';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::METHOD_ITEMS,
                'label' => __('Apply to items'),
            ],
            [
                'value' => self::METHOD_TOTALS,
                'label' => __('Apply to totals'),
            ],
        ];
    }

    /************************/
}
