<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition;

use Magento\Catalog\Model\ProductCategoryList;

class Product extends \Magento\SalesRule\Model\Rule\Condition\Product{

    protected $apHelper;

    public function getApHelper(){
        if(!$this->apHelper){
            $this->apHelper = \Magento\Framework\App\ObjectManager::getInstance()->get("Mexbs\\AdditionalPromotions\\Helper\\Data");
        }
        return $this->apHelper;
    }

    public function getTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_' . $this->getSubPrefix() . '__' . $this->getId() . '__type',
            'hidden',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . ']['.$this->getSubPrefix().'][' . $this->getId() . '][type]',
                'value' => $this->getType(),
                'no_span' => true,
                'class' => 'hidden',
                'data-form-part' => $this->getFormName()
            ]
        );
    }

    public function getAttributeElement()
    {
        if (null === $this->getAttribute()) {
            foreach (array_keys($this->getAttributeOption()) as $option) {
                $this->setAttribute($option);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_' . $this->getSubPrefix() . '__' . $this->getId() . '__attribute',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . ']['.$this->getSubPrefix().'][' . $this->getId() . '][attribute]',
                'values' => $this->getAttributeSelectOptions(),
                'value' => $this->getAttribute(),
                'value_name' => $this->getAttributeName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getOperatorElement()
    {
        $options = $this->getOperatorSelectOptions();
        if ($this->getOperator() === null) {
            foreach ($options as $option) {
                $this->setOperator($option['value']);
                break;
            }
        }

        $elementId = sprintf('%s_%s__%s__operator', $this->getPrefix(), $this->getSubPrefix(), $this->getId());
        $elementName = sprintf($this->elementName . '[%s][%s][%s][operator]', $this->getPrefix(), $this->getSubPrefix(), $this->getId());
        $element = $this->getForm()->addField(
            $elementId,
            'select',
            [
                'name' => $elementName,
                'values' => $options,
                'value' => $this->getOperator(),
                'value_name' => $this->getOperatorName(),
                'data-form-part' => $this->getFormName()
            ]
        );
        $element->setRenderer($this->_layout->getBlockSingleton('Magento\Rule\Block\Editable'));

        return $element;
    }

    public function getValueElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . ']['.$this->getSubPrefix().'][' . $this->getId() . '][value]',
            'value' => $this->getValue(),
            'values' => $this->getValueSelectOptions(),
            'value_name' => $this->getValueName(),
            'after_element_html' => $this->getValueAfterElementHtml(),
            'explicit_apply' => $this->getExplicitApply(),
            'data-form-part' => $this->getFormName()
        ];
        if ($this->getInputType() == 'date') {
            // date format intentionally hard-coded
            $elementParams['input_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
            $elementParams['date_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_' . $this->getSubPrefix() . '__' . $this->getId() . '__value',
            $this->getValueElementType(),
            $elementParams
        )->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                if(version_compare($this->getApHelper()->getMagentoVersion(), "2.3.2", "<")){
                    $url = 'catalog_rule/promo_widget/chooser/attribute/' . $this->getAttribute().'/form/simple_action_container';
                }else{
                    $url = 'sales_rule/promo_widget/chooser/attribute/' . $this->getAttribute().'/form/simple_action_container';
                }
                break;
            default:
                break;
        }
        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }

    public function validateProductWithoutQuote($product){
        $attrCode = $this->getAttribute();

        if ('category_ids' != $attrCode) {
            $attr = $product->getResource()->getAttribute($attrCode);
            if ($attr && in_array($attr->getFrontendInput(), ['select', 'multiselect'])) {
                if (!$product->hasData($attrCode)) {
                    $attrValue = $this->getApHelper()->getProductAttributeValue($product->getId(), $attrCode);
                    $product->setData($attrCode, $attrValue);
                }
            }
        }
        return \Magento\Rule\Model\Condition\Product\AbstractProduct::validate($product);
    }

    public function hasAddressConditionsInAction(){
        return
            ($this instanceof \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\CustomOptionSku)
            || ($this instanceof \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\CustomOptionTitleValue)
            || in_array($this->getAttribute(),
                [
                    'quote_item_price',
                    'quote_item_qty',
                    'quote_item_row_total',
                ]
            );
    }

    public function getAttributeElementHtml()
    {
        $html = \Magento\Rule\Model\Condition\AbstractCondition::getAttributeElementHtml() .
            $this->getAttributeScopeElement()->getHtml();
        return $html;
    }

    private function getAttributeScopeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_' . $this->getSubPrefix() . '__' . $this->getId() . '__attribute_scope',
            'hidden',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . ']['.$this->getSubPrefix().'][' . $this->getId() . '][attribute_scope]',
                'value' => $this->getAttributeScope(),
                'no_span' => true,
                'class' => 'hidden',
                'data-form-part' => $this->getFormName()
            ]
        );
    }
}