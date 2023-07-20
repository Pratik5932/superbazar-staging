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

namespace Webkul\MobikulCore\Model\Config\Backend;
 
class LauncherZip extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @return string[]
     */
    public function _getAllowedExtensions() {
        return ['zip'];
    }
}