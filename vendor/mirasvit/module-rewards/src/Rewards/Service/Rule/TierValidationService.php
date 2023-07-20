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



namespace Mirasvit\Rewards\Service\Rule;

class TierValidationService
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function isValidNumberValue($value)
    {
        return !preg_match('/[^\d.]/', $value);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValidPercentValue($value)
    {
        if (preg_match('/[^\d.%]/', $value)) {
            return false;
        }

        if (strpos($value, '%') !== false) {
            $field = str_replace('%', '', $value);

            return $field > 100 ? false : true;
        }

        return true;
    }
}