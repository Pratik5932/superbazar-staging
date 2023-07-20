<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

class BuyABCGetNOfDFixedPriceDiscount extends \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetYAbstract{

    const SIMPLE_ACTION = 'buy_abc_get_n_of_different_d_fixed_price_discount_action';
    protected $type = 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDFixedPriceDiscount';

    public function getSimpleAction(){
        return self::SIMPLE_ACTION;
    }

    public function getDiscountType(){
        return self::DISCOUNT_TYPE_FIXED_PRICE;
    }

    public function getNumberOfSets(){
        return self::MAX_SETS_NUMBER;
    }

    public function asHtmlRecursive()
    {
        $html = __(
            "<div>Buy set of items consisting of the following parts:</div>"
        );

        for($setPartIndex = 1; $setPartIndex <= $this->getNumberOfSets(); $setPartIndex++){
            $html .=  __(
                    "Part#%1: %2 items [label for upsell cart hints - singular: %3, plural: %4] for which %5 of the following conditions are %6",
                    $setPartIndex,
                    $this->getSetPartSizeAttributeElement($setPartIndex)->getHtml(),
                    $this->getSetPartHintsSingularElement($setPartIndex)->getHtml(),
                    $this->getSetPartHintsPluralElement($setPartIndex)->getHtml(),
                    $this->getSetPartAggregatorElement($setPartIndex)->getHtml(),
                    $this->getSetPartAggregatorValueElement($setPartIndex)->getHtml()
                ).'<ul id="' .
                $this->getPrefix() .
                '_setpart'.$setPartIndex.'__' .
                $this->getId() .
                '__children" class="rule-param-children">';

            if($this->getData('set_part'.$setPartIndex.'_action_details')){
                foreach($this->getData('set_part'.$setPartIndex.'_action_details')->getActionDetails() as $actionDetail){
                    $html .= '<li>' . $actionDetail->asHtmlRecursive() . '</li>';
                }
            }

            $html .= '<li>' . $this->getSetPartNewChildElement($setPartIndex)->getHtml() . '</li></ul>';
        }

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