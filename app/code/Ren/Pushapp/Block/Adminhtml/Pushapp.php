<?php
namespace Ren\Pushapp\Block\Adminhtml;
class Pushapp extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_pushapp';/*block grid.php directory*/
        $this->_blockGroup = 'Ren_Pushapp';
        $this->_headerText = __('Pushapp');
        $this->_addButtonLabel = __('Add New Entry'); 
        parent::_construct();
		
    }
}
