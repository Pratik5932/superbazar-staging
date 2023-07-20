<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ZipCodeValidator\Block\Adminhtml\Region;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Initialize ZipCodeValidator zipcode Edit Block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Webkul_ZipCodeValidator';
        $this->_controller = 'adminhtml_region';
        parent::_construct();
        if ($this->_isAllowedAction('Webkul_ZipCodeValidator::region')) {
            $this->buttonList->update('save', 'label', __('Save Entry'));
        } else {
            $this->buttonList->remove('save');
        }
    }

    /**
     * Retrieve text for header element depending on loaded Region
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('zipcodevalidator')->getId()) {
            $title = $this->_coreRegistry->registry('zipcodevalidator')->getTitle();
            $title = $this->escapeHtml($title);
            return __("Edit Region '%'", $title);
        } else {
            return __('New Region');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
