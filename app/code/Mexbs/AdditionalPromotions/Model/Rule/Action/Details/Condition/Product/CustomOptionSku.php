<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product;

class CustomOptionSku extends \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product
{
    protected function getCustomOptionSkuHtml(){
        return ' <input type="hidden" class="hidden" id="' .
        $this->getPrefix() . '_' . $this->getSubPrefix() . '__' . $this->getId() . '__attribute' .
        '" name="' .
        $this->elementName . '[' . $this->getPrefix() . '][' . $this->getSubPrefix() . '][' . $this->getId() . '][attribute]' .
        '" value="custom_option_sku" data-form-part="' .
        $this->getFormName() .
        '"/> ';
    }

    public function asHtml()
    {
        $html = $this->getTypeElementHtml();

        $html .=  $this->getCustomOptionSkuHtml();

        $html .= sprintf(
            "Custom option SKU %s %s",
            $this->getOperatorElementHtml(),
            $this->getValueElementHtml()
        );
        $html .= $this->getRemoveLinkHtml();

        return $html;
    }

    public function validate(\Magento\Framework\Model\AbstractModel $item)
    {
        if ($item->getParentItemId()) {
            return false;
        }

        $attributeValue = $item->getSku();
        return $this->validateAttribute($attributeValue);
    }
}