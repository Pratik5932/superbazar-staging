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
 * Class IconType model
 */
class IconType implements OptionSourceInterface
{
    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $returnData[] =  [
            "value" => 1,
            "label" => "Type One"
        ];
        $returnData[] =  [
            "value" => 2,
            "label" => "Type Two"
        ];
        $returnData[] =  [
            "value" => 3,
            "label" => "Type Three"
        ];
        $returnData[] =  [
            "value" => 4,
            "label" => "Type Four"
        ];
        $returnData[] =  [
            "value" => 5,
            "label" => "Type Five"
        ];
        return $returnData;
    }
}
