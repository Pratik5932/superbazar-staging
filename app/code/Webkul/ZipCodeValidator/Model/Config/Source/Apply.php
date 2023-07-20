<?php
/**
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ZipCodeValidator\Model\Config\Source;

class Apply implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('Apply to Individual Products')],
            ['value' => '1', 'label' => __('Apply to all Products')]
        ];
    }
}
