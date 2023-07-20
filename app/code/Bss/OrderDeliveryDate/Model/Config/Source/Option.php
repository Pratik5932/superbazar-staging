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

namespace Bss\OrderDeliveryDate\Model\Config\Source;

class Option implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Render Config Show in Checkout Admin
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Shipping Address')],
            ['value' => 1, 'label' => __('Shipping Method')],
            ['value' => 2, 'label' => __('Review & Payments')]
        ];
    }
}
