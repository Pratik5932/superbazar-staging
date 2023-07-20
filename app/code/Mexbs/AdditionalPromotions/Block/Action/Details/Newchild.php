<?php
namespace Mexbs\AdditionalPromotions\Block\Action\Details;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Newchild extends \Magento\Framework\View\Element\AbstractBlock implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->addClass('element-value-changer');
        $html = '&nbsp;<span class="rule-param rule-param-new-child action-details-selection not-selected"' .
            ($element->getParamId() ? ' id="' .
                $element->getParamId() .
                '"' : '') .
            '>';
        $html .= '<span class="element">';
        $html .= $element->getElementHtml();
        $html .= '</span></span>&nbsp;';
        return $html;
    }
}
