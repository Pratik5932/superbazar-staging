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


namespace Mirasvit\RewardsAdminUi\Ui\General\Form\Source;

class Groups extends \Magento\Customer\Model\ResourceModel\Group\Collection
{
    const RULE_ALL_GROUPS_KEY = 'all';
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options, ['value' => self::RULE_ALL_GROUPS_KEY, 'label' => __('All Groups')->getText()]);

        return $options;
    }
}
