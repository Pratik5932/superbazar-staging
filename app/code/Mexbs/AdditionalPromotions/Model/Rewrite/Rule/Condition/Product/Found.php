<?php
namespace Mexbs\AdditionalPromotions\Model\Rewrite\Rule\Condition\Product;

use Magento\Framework\App\ObjectManager;

class Found extends \Magento\SalesRule\Model\Rule\Condition\Product\Found
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
                "If an item [label for upsell cart hints - singular: %1 , plural: %2] is %3 in the cart with %4 of these conditions true:",
                $this->getHintSingularElement()->getHtml(),
                $this->getHintPluralElement()->getHtml(),
                $this->getValueElement()->getHtml(),
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
        $this->setHintSingular(isset($arr['hint_singular']) ? $arr['hint_singular'] : null);
        $this->setHintPlural(isset($arr['hint_plural']) ? $arr['hint_plural'] : null);
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

        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();
        $found = false;

        foreach ($model->getAllItems() as $item) {
            $found = $all;
            foreach ($this->getConditions() as $cond) {
                $validated = $cond->validate($item);
                if ($all && !$validated || !$all && $validated) {
                    $found = $validated;
                    break;
                }
            }
            if ($found && $true || !$true && $found) {
                break;
            }
        }
        $validationResult = false;
        $cartHintData = null;

        $cartHintData = array_merge([
                            'volume_type' => 'qty',
                            'hint_singular' => $this->getHintSingular(),
                            'hint_plural' => $this->getHintPlural()
                        ], $this->_getApHelper()->getCartHintAddData(
                            ($true ? '>=' : '=='),
                            ($true ? 1 : 0),
                            ($found ? 1 : 0)
                        ));

        // found an item and we're looking for existing one
        if ($found && $true) {
            $validationResult =  true;
        } elseif (!$found && !$true) {
            // not found and we're making sure it doesn't exist
            $validationResult = true;
        }

        $this->setLastCartHintData($cartHintData);

        return $validationResult;
    }
}