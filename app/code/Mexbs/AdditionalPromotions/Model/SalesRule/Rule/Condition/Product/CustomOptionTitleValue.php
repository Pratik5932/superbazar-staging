<?php
namespace Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Product;

class CustomOptionTitleValue extends ProductAbstract{
    protected $productConfiguration;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Catalog\Helper\Product\Configuration $productConfiguration,
        array $data = []
    ) {
        $this->productConfiguration = $productConfiguration;
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data
        );
    }

    public function isArrayOperatorTypeByOperator($operator)
    {
        return $operator === '()' || $operator === '!()' || in_array($this->getInputType(), $this->_arrayInputTypes);
    }


    protected function _getComparisonResult($value, $validatedValue, $operator){
        $result = false;

        switch ($operator) {
            case '==':
            case '!=':
                if (is_array($value)) {
                    if (is_array($validatedValue)) {
                        $result = array_intersect($value, $validatedValue);
                        $result = !empty($result);
                    } else {
                        return false;
                    }
                } else {
                    if (is_array($validatedValue)) {
                        $result = count($validatedValue) == 1 && array_shift($validatedValue) == $value;
                    } else {
                        $result = $this->_compareValues($validatedValue, $value);
                    }
                }
                break;

            case '<=':
            case '>':
                if (!is_scalar($validatedValue)) {
                    return false;
                } else {
                    $result = $validatedValue <= $value;
                }
                break;

            case '>=':
            case '<':
                if (!is_scalar($validatedValue)) {
                    return false;
                } else {
                    $result = $validatedValue >= $value;
                }
                break;

            case '{}':
            case '!{}':
                if (is_scalar($validatedValue) && is_array($value)) {
                    foreach ($value as $item) {
                        if (stripos($validatedValue, (string)$item) !== false) {
                            $result = true;
                            break;
                        }
                    }
                } elseif (is_array($value)) {
                    if (is_array($validatedValue)) {
                        $result = array_intersect($value, $validatedValue);
                        $result = !empty($result);
                    } else {
                        return false;
                    }
                } else {
                    if (is_array($validatedValue)) {
                        $result = in_array($value, $validatedValue);
                    } else {
                        $result = $this->_compareValues($value, $validatedValue, false);
                    }
                }
                break;

            case '()':
            case '!()':
                if (is_array($validatedValue)) {
                    $result = count(array_intersect($validatedValue, (array)$value)) > 0;
                } else {
                    $value = (array)$value;
                    foreach ($value as $item) {
                        if ($this->_compareValues($validatedValue, $item)) {
                            $result = true;
                            break;
                        }
                    }
                }
                break;
        }

        if ('!=' == $operator || '>' == $operator || '<' == $operator || '!{}' == $operator || '!()' == $operator) {
            $result = !$result;
        }

        return $result;
    }

    public function validate(\Magento\Framework\Model\AbstractModel $item)
    {
        $customOptionRuleTitle = $this->getOptionTitle();

        $customOptionRuleValue = $this->getOptionValue();
        $customOptionRuleValueOperator = $this->getOptionValueOperator();

        if ($this->isArrayOperatorTypeByOperator($customOptionRuleValueOperator)
            xor is_array($customOptionRuleValue)) {
            return false;
        }

        $itemCustomOptions = $this->productConfiguration->getCustomOptions($item);


        foreach($itemCustomOptions as $itemCustomOption){
            if(isset($itemCustomOption['label'])
                && ($customOptionRuleTitle == $itemCustomOption['label'])){
                if(!isset($itemCustomOption['value'])){
                    return false;
                }
                return $this->_getComparisonResult(
                    $customOptionRuleValue,
                    $itemCustomOption['value'],
                    $customOptionRuleValueOperator
                );
            }
        }

        return false;
    }

    public function getOptionTitleElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__option_title',
            'text',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][option_title]',
                'value' => $this->getOptionTitle(),
                'value_name' => ($this->getOptionTitle() ? $this->getOptionTitle() : "..."),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getOptionValueElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__option_value',
            'text',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][option_value]',
                'value' => $this->getOptionValue(),
                'value_name' => ($this->getOptionValue() ? $this->getOptionValue() : "..."),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getOptionTitleElementHtml()
    {
        return $this->getOptionTitleElement()->getHtml();
    }

    public function getOptionValueElementHtml()
    {
        return $this->getOptionValueElement()->getHtml();
    }

    public function getOptionTitleOperatorName()
    {
        return $this->getOperatorOption($this->getOptionTitleOperator());
    }

    public function getOptionValueOperatorName()
    {
        return $this->getOperatorOption($this->getOptionValueOperator());
    }

    public function getOptionValueOperatorElement()
    {
        $options = $this->getOperatorSelectOptions();
        if ($this->getOptionValueOperator() === null) {
            foreach ($options as $option) {
                $this->setOptionValueOperator($option['value']);
                break;
            }
        }

        $elementId = sprintf('%s__%s__option_value_operator', $this->getPrefix(), $this->getId());
        $elementName = sprintf($this->elementName . '[%s][%s][option_value_operator]', $this->getPrefix(), $this->getId());
        $element = $this->getForm()->addField(
            $elementId,
            'select',
            [
                'name' => $elementName,
                'values' => $options,
                'value' => $this->getOptionValueOperator(),
                'value_name' => $this->getOptionValueOperatorName(),
                'data-form-part' => $this->getFormName()
            ]
        );
        $element->setRenderer($this->_layout->getBlockSingleton('Magento\Rule\Block\Editable'));

        return $element;
    }

    public function getOptionTitleOperatorElementHtml()
    {
        return $this->getOptionTitleOperatorElement()->getHtml();
    }

    public function getOptionValueOperatorElementHtml()
    {
        return $this->getOptionValueOperatorElement()->getHtml();
    }

    public function asHtml()
    {
        $html = $this->getTypeElementHtml();

        $html .= __(
            "If the value of custom option with title %1 %2 %3",
            $this->getOptionTitleElementHtml(),
            $this->getOptionValueOperatorElementHtml(),
            $this->getOptionValueElementHtml()
        );

        $html .= $this->getRemoveLinkHtml() .
            $this->getChooserContainerHtml();
        return $html;
    }

    public function getDirectAttributeKeys(){
        return [
            'option_title',
            'option_value',
            'option_value_operator'
        ];
    }
}