<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ZipCodeValidator\Model\Config\Source;

use Magento\Framework\DataObject;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DB\Ddl\Table;

class ValidationOptions extends DataObject implements OptionSourceInterface
{
    /**
     * Retrieve all options
     *
     * @return array
     */
    public static function getAllOption()
    {
        $options = self::getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * Retrieve all options
     *
     * @return array
     */
    public static function getAllOptions()
    {
        $res = [];
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Retrieve option text
     *
     * @param  int $optionId
     * @return string
     */
    public static function getOptionText($optionId)
    {
        $options = self::getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            1 => __('None'),
            2 => __('Apply default Configuration'),
            0 => __('Particular Product')
        ];
    }
}
