<?php
namespace Mexbs\AdditionalPromotions\Model\Rewrite\Rule\Condition\Product;

use Magento\Framework\App\ObjectManager;

class Subselect extends \Magento\SalesRule\Model\Rule\Condition\Product\Subselect
{
    private $apHelper;

    protected function _getApHelper(){
        if(!$this->apHelper){
            $this->apHelper = ObjectManager::getInstance()->get("Mexbs\\AdditionalPromotions\\Helper\\Data");
        }
        return $this->apHelper;
    }

    public function getSupportsCartHints(){
        return ($this->getHintSingular()
            && $this->getHintPlural());
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . __(
                "If %1 %2 %3 for a subselection of items in cart [label for upsell cart hints - singular: %4 , plural: %5 ] matching %6 of these conditions:",
                $this->getAttributeElement()->getHtml(),
                $this->getOperatorElement()->getHtml(),
                $this->getValueElement()->getHtml(),
                $this->getHintSingularElement()->getHtml(),
                $this->getHintPluralElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    public function getHintSingularElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][hint_singular]',
            'value' => $this->getHintSingular(),
            'value_name' => ($this->getHintSingular() ? $this->getHintSingular() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__hint_singular',
            'text',
            $elementParams
        )->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    public function getHintPluralElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][hint_plural]',
            'value' => $this->getHintPlural(),
            'value_name' => ($this->getHintPlural() ? $this->getHintPlural() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__hint_plural',
            'text',
            $elementParams
        )->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    public function loadArray($arr, $key = 'conditions')
    {
        $this->setHintSingular($arr['hint_singular']);
        $this->setHintPlural($arr['hint_plural']);
        parent::loadArray($arr, $key);
        return $this;
    }

    public function asArray(array $arrAttributes = [])
    {
        $out = parent::asArray();
        $out['hint_singular'] = $this->getHintSingular();
        $out['hint_plural'] = $this->getHintPlural();

        return $out;
    }

    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $this->setLastCartHintData(null);

        if (!$this->getConditions()) {
            return false;
        }
        $attr = $this->getAttribute();
        $total = 0;
        foreach ($model->getQuote()->getAllVisibleItems() as $item) {
            if (\Magento\SalesRule\Model\Rule\Condition\Product\Combine::validate($item)) {
                $total += $item->getData($attr);
            }
        }

        $validationResult =  $this->validateAttribute($total);

        $cartHintData = array_merge([
                            'volume_type' => ($attr == 'base_total_amount' ? 'amount' : 'qty'),
                            'hint_singular' => $this->getHintSingular(),
                            'hint_plural' => $this->getHintPlural()
                        ], $this->_getApHelper()->getCartHintAddData($this->getOperator(), $this->getValue(), $total));

        $this->setLastCartHintData($cartHintData);

        return $validationResult;
    }
}