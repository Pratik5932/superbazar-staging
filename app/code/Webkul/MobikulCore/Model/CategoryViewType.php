<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Cmspages model
 */
class CategoryViewType implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $returnData[] =  [
            "value" => 0,
            "label" => __("Default Category View")
        ];
        $returnData[] =  [
            "value" => 1,
            "label" => __("Tab Category View")
        ];
        return $returnData;
    }
}
