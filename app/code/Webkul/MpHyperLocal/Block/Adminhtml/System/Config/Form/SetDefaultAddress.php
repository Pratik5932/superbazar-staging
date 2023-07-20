<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field as FormField;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SetDefaultAddress extends FormField
{
    const BUTTON_TEMPLATE = 'system/config/button/default-address.phtml';

    /**
     * Set template to itself.
     * @return $this
     */

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }

    /**
     * Render button.
     * @param AbstractElement $element
     * @return string
     */

    public function render(AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return get Google ApiKey.
     *
     * @return string
     */

    public function getGoogleApiKey()
    {
        return $this->_scopeConfig->getValue('mphyperlocal/general_settings/google_api_key');
    }

    /**
     * Return ajax url for button.
     *
     * @return string
     */

    public function getSavedAddress()
    {
        return $this->_scopeConfig->getValue('mphyperlocal/general_settings/address');
    }

    /**
     * Get the button and scripts contents.
     * @param AbstractElement $element
     * @return string
     */
    
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
