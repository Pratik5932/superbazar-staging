<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\MobikulCore\Block;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Block Class Color
 */
class Color extends Field
{
    protected $_coreRegistry;

    public function __construct(
        Context $context, 
        Registry $coreRegistry,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->fileDriver = $fileDriver;
        parent::__construct($context, $data);
    }

    /**
     * Get Html Elements
     *
     * @param AbstractElement $element
     * @return void
     */
    protected function _getElementHtml(AbstractElement $element) {
        $html = $element->getElementHtml();
        $cpPath = $this->getViewFileUrl('Webkul_MobikulCore::js');
        if (!$this->_coreRegistry->registry('colorpicker_loaded')) {
            if($this->fileDriver->isExists($cpPath.'/'.'jscolor.min.js')) {
                $html .= '<script type="text/javascript" src="' . $cpPath.'/'.'jscolor.min.js"></script>';
            } else {
                $html .= '<script type="text/javascript" src="' . $cpPath.'/'.'jscolor.js"></script>';
            }
            $this->_coreRegistry->registry('colorpicker_loaded', 1);
        }
        $html .= '<script type="text/javascript">
                var el = document.getElementById("' . $element->getHtmlId() . '");
                el.className = el.className + " jscolor{hash:true}";
            </script>';
            
        return $html;
    }
}