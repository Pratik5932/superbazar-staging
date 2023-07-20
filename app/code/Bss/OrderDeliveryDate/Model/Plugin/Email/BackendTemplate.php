<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\OrderDeliveryDate\Model\Plugin\Email;

class BackendTemplate extends \Magento\Email\Model\BackendTemplate
{
    /**
     * Add ODD Email Template Var Admin
     *
     * @param bool $withGroup
     * @return array
     */
    public function getVariablesOptionArray($withGroup = false)
    {
        $optionArray = [];
        $variables = $this->_parseVariablesString($this->getData('orig_template_variables'));
        $variables['var shipping_arrival_date'] = 'Delivery Date';
        $variables['var delivery_time_slot'] = 'Delivery Time Slot';
        $variables['var shipping_arrival_comments'] = 'Shipping Arrival Comment';
        if ($variables) {
            foreach ($variables as $value => $label) {
                $optionArray[] = ['value' => '{{' . $value . '}}', 'label' => __('%1', $label)];
            }
            if ($withGroup) {
                $optionArray = ['label' => __('Template Variables'), 'value' => $optionArray];
            }
        }
        return $optionArray;
    }
}
