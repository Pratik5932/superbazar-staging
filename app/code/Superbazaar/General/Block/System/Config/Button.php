<?php

namespace Superbazaar\General\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Button extends Field
{
    protected $_template = 'Superbazaar_General::system/config/button.phtml';

    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    public function getCustomUrl()
    {
        return $this->getUrl('superbazar_zipcode/email/send');
    }
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'url' => $this->getCustomUrl(),
                'html_id' => $element->getHtmlId(),
            ]
        );

        return $this->_toHtml();
    }
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'btn_id',
                'label' => __('Send Report'),
            ]
        );
        return $button->toHtml();
    }
}