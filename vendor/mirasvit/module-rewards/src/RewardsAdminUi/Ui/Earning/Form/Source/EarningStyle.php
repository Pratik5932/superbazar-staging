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


namespace Mirasvit\RewardsAdminUi\Ui\Earning\Form\Source;

class EarningStyle
{
    /**
     * @param array $options
     * @return array
     */
    public static function toEarningOptionArray($options)
    {
        $result = [];

        foreach ($options as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => is_object($value) ? $value->getText() : $value,
            ];
        }

        return $result;
    }
}
