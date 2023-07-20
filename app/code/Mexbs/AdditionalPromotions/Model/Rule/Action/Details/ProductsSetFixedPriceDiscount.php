<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

class ProductsSetFixedPriceDiscount extends ProductsSetTotalDiscountAbstract{

    const SIMPLE_ACTION = 'products_set_fixed_price_discount_action';
    protected $type = 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetFixedPriceDiscount';

    public function getSimpleAction(){
        return self::SIMPLE_ACTION;
    }

    public function getDiscountType(){
        return self::DISCOUNT_TYPE_FIXED_PRICE;
    }

    public function getNumberOfSets(){
        return self::MAX_SETS_NUMBER;
    }

    public function getRuleDiscountDescription(){
        return sprintf(
            "for %s%s only!",
            $this->apHelper->getCurrentCurrencySymbol(),
            $this->getDiscountAmountValue()
        );
    }

    public function asHtmlRecursive()
    {
        $html = __(
            "<div>Get the set of items consisting of the following parts, for %1%2:</div>",
            $this->getDiscountAmountValueElement()->getHtml(),
            $this->apHelper->getCurrentCurrencySymbol()
        );

        for($setPartIndex = 1; $setPartIndex <= $this->getNumberOfSets(); $setPartIndex++){
            $html .=  __(
                    "Part#%1: %2 items [label for upsell cart hints / promo block - singular: %3, plural: %4] for which %5 of the following conditions are %6",
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


        $html = $html.$this->getProductsSetTypeElement()->getHtml();

        for($setPartIndex = 1; $setPartIndex <= $this->getNumberOfSets(); $setPartIndex++){
            $html = $html.$this->getSetPartTypeElement($setPartIndex)->getHtml();
        }

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return "<li>".$html."</li>";
    }
}