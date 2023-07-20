<?php
/**
 * Solwin Infotech
 * Solwin Featured Product Extension
 * 
 * @category   Solwin
 * @package    Solwin_FeaturedPro
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/
 */
namespace Solwin\FeaturedPro\Block;

class Link extends \Magento\Framework\View\Element\Html\Link
{

    /**
     * Call link.phtml template
     */
    
    protected $_template = 'Solwin_FeaturedPro::link.phtml';

    /**
     *  Returns featured product url
     */
    
    public function getHref() {
        return $this->getUrl('featuredproduct');
    }
    
    /**
     * Returns Featured Product label
     */

    public function getLabel() {
        return __('Featured Product');
    }

}