<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

class BuyXGetNOfYFixedPriceDiscount extends \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetYAbstract{

    const SIMPLE_ACTION = 'buy_x_get_n_of_different_y_fixed_price_discount_action';
    protected $type = 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYFixedPriceDiscount';

    public function getSimpleAction(){
        return self::SIMPLE_ACTION;
    }


    public function getDiscountType(){
        return self::DISCOUNT_TYPE_FIXED_PRICE;
    }

    public function getNumberOfSets(){
        return 1;
    }

    public function asHtmlRecursive()
    {
        $html =  __(
                "Buy %1 items [label for upsell cart hints - singular: %2, plural: %3] for which %4 of the following conditions are %5",
                $this->getSetPartSizeAttributeElement(1)->getHtml(),
                $this->getSetPartHintsSingularElement(1)->getHtml(),
                $this->getSetPartHintsPluralElement(1)->getHtml(),
                $this->getSetPartAggregatorElement(1)->getHtml(),
                $this->getSetPartAggregatorValueElement(1)->getHtml()
            ).'<ul id="' .
            $this->getPrefix() .
            '_setpart1__' .
            $this->getId() .
            '__children" class="rule-param-children">';

        if($this->getData('set_part1_action_details')){
            foreach($this->getData('set_part1_action_details')->getActionDetails() as $actionDetail){
                $html .= '<li>' . $actionDetail->asHtmlRecursive() . '</li>';
            }
        }

        $html .= '<li>' . $this->getSetPartNewChildElement(1)->getHtml() . '</li></ul>';

        $getYHtml =  __(
                "Get the %1 first %2 items [label for upsell cart hints - singular: %3, plural: %4] for which %5 of the following conditions are %6, for %7%8",
                $this->getGetYQtyAttributeElement()->getHtml(),
                $this->getDiscountPriceTypeAttributeElement()->getHtml(),
                $this->getGetYHintsSingularElement()->getHtml(),
                $this->getGetYHintsPluralElement()->getHtml(),
                $this->getGetYAggregatorElement()->getHtml(),
                $this->getGetYAggregatorValueElement()->getHtml(),
                $this->getDiscountAmountValueElement()->getHtml(),
                $this->apHelper->getCurrentCurrencySymbol()
            ).'<ul id="' .
            $this->getPrefix() .
            '_gety__' .
            $this->getId() .
            '__children" class="rule-param-children">';

        if($this->getGetYActionDetails()){
            foreach($this->getGetYActionDetails()->getActionDetails() as $actionDetail){
                $getYHtml .= '<li>' . $actionDetail->asHtmlRecursive() . '</li>';
            }
        }

        $getYHtml .= '<li>' . $this->getGetYNewChildElement()->getHtml() . '</li></ul>';

        $html .= $getYHtml;

        $html .= $this->getBuyXGetYTypeElement()->getHtml();
        for($setPartIndex = 1; $setPartIndex <= $this->getNumberOfSets(); $setPartIndex++){
            $html = $html.$this->getSetPartTypeElement($setPartIndex)->getHtml();
        }
        $html = $html.$this->getGetYTypeElement()->getHtml();

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return "<li>".$html."</li>";
    }
}