<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

class GetAllAfterMFixedDiscount extends \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAbstract{

    const SIMPLE_ACTION = 'get_all_after_m_fixed_discount_action';
    protected $type = 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMFixedDiscount';

    public function getDiscountType(){
        return self::DISCOUNT_TYPE_FIXED;
    }

    public function isEachN(){
        return false;
    }

    public function isDiscountPriceTypeApplicable(){
        return false;
    }

    public function isDiscountOrderTypeApplicable(){
        return true;
    }

    public function isLimitApplicable(){
        return false;
    }

    public function isDiscountQtyApplicable(){
        return true;
    }

    public function getSimpleAction(){
        return self::SIMPLE_ACTION;
    }

    public function asHtmlRecursive()
    {
        $getEachNHtml =  __(
                "Get all subsequent items [label for upsell cart hints - singular: %1, plural: %2], for which %3 of the following conditions are %4",
                $this->getNHintsSingularElement()->getHtml(),
                $this->getNHintsPluralElement()->getHtml(),
                $this->getEachNAggregatorElement()->getHtml(),
                $this->getEachNAggregatorValueElement()->getHtml()
            ).'<ul id="' .
            $this->getPrefix() .
            '_eachn__' .
            $this->getId() .
            '__children" class="rule-param-children">';

        if($this->getEachNActionDetails()){
            foreach($this->getEachNActionDetails()->getActionDetails() as $actionDetail){
                $getEachNHtml .= '<li>' . $actionDetail->asHtmlRecursive() . '</li>';
            }
        }

        $getEachNHtml .= '<li>' . $this->getEachNNewChildElement()->getHtml() . '</li></ul>';

        $getEachNHtml .=  __(
                "With %1%2 discount, after %3 such items has been added to cart for full price ",
                $this->getDiscountAmountValueElement()->getHtml(),
                $this->apHelper->getCurrentCurrencySymbol(),
                $this->getAfterMQtyElement()->getHtml()
            ).'<ul id="' .
            $this->getPrefix() .
            '_eachn__' .
            $this->getId() .
            '__children" class="rule-param-children">';

        $html = $this->getEachNWrapperTypeElement()->getHtml() .
            $this->getEachNTypeElement()->getHtml() .
            $getEachNHtml;

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return "<li>".$html."</li>";
    }

    public function getDirectAttributeKeys(){
        return [
            'n_hints_singular',
            'n_hints_plural',
            'discount_amount_value',
            'after_m_qty'
        ];
    }
}