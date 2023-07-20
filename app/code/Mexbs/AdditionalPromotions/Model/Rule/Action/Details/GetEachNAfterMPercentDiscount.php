<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

class GetEachNAfterMPercentDiscount extends \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAbstract{

    const SIMPLE_ACTION = 'get_each_n_after_m_percent_discount_action';
    protected $type = 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMPercentDiscount';

    public function getDiscountType(){
        return self::DISCOUNT_TYPE_PERCENT;
    }

    public function isEachN(){
        return true;
    }

    public function isDiscountPriceTypeApplicable(){
        return true;
    }

    public function isDiscountOrderTypeApplicable(){
        return false;
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
                "Buy %1 items [label for upsell cart hints - singular: %2, plural: %3], for which %4 of the following conditions are %5",
                $this->getNNumberElement()->getHtml(),
                $this->getNHintsSingularElement()->getHtml(),
                $this->getNHintsPluralElement()->getHtml(),
                $this->getEachNAggregatorElement()->getHtml(),
                $this->getEachNAggregatorValueElement()->getHtml()
            ).
            '<ul id="' .
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
                "Get the subsequent %1 items (matching the same conditions) (ordered by %2), with %3% discount, after %4 such items has been added to cart for full price",
                $this->getMNumberElement()->getHtml(),
                $this->getDiscountPriceTypeAttributeElement()->getHtml(),
                $this->getDiscountAmountValueElement()->getHtml(),
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
            'n_number',
            'n_hints_singular',
            'n_hints_plural',
            'm_number',
            'discount_amount_value',
            'discount_price_type',
            'after_m_qty'
        ];
    }
}