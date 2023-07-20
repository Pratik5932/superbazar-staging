<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

class EachGroupOfNFixedPriceDiscount extends ProductsSetTotalDiscountAbstract{

    const SIMPLE_ACTION = 'each_group_of_n_fixed_price_discount_action';
    protected $type = 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNFixedPriceDiscount';

    public function getDiscountType(){
        return self::DISCOUNT_TYPE_FIXED_PRICE;
    }

    public function getNumberOfSets(){
        return 1;
    }

    public function getSimpleAction(){
        return self::SIMPLE_ACTION;
    }


    public function asHtmlRecursive()
    {
        $html =  __(
                "Get every group of %1 items [label for upsell cart hints - singular: %2, plural: %3] for which %4 of the following conditions are %5:",
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

        $html .=  __(
                "for %1%2",
                $this->getDiscountAmountValueElement()->getHtml(),
                $this->apHelper->getCurrentCurrencySymbol()
            ).'<ul id="' .
            $this->getPrefix() .
            '_groupproducts__' .
            $this->getId() .
            '__children" class="rule-param-children">';

        $html .= $this->getProductsSetTypeElement()->getHtml().
            $this->getSetPartTypeElement(1)->getHtml();

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return "<li>".$html."</li>";
    }
}