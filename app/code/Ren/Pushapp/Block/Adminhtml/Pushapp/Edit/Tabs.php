<?php
namespace Ren\Pushapp\Block\Adminhtml\Pushapp\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		
        parent::_construct();
        $this->setId('checkmodule_pushapp_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Pushapp Information'));
    }
}