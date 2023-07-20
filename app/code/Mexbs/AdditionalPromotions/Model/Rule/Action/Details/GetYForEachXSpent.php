<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

class GetYForEachXSpent extends \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetYForEachXSpentAbstract{

    const SIMPLE_ACTION = 'get_y_for_each_x_spent_action';
    protected $type = 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetYForEachXSpent';

    public function getDiscountMaxQty(){
        return INF;
    }

    public function getSimpleAction(){
        return self::SIMPLE_ACTION;
    }

    public function isDiscountPriceTypeApplicable(){
        return false;
    }

    public function isDiscountOrderTypeApplicable(){
        return true;
    }

    public function asHtmlRecursive()
    {
        $html =  __(
                "Get %1%2 for each %3%4 spent on all items [label for upsell cart hints - singular: %5, plural: %6] for which %7 of the following conditions are %8:",
                $this->getGetYAmountAttributeElement()->getHtml(),
                $this->apHelper->getCurrentCurrencySymbol(),
                $this->getEachXAmountAttributeElement()->getHtml(),
                $this->apHelper->getCurrentCurrencySymbol(),
                $this->getHintsSingularElement()->getHtml(),
                $this->getHintsPluralElement()->getHtml(),
                $this->getEachXProductsAggregatorElement()->getHtml(),
                $this->getEachXProductsAggregatorValueElement()->getHtml()
            ).'<ul id="' .
            $this->getPrefix() .
            '_eachxproducts__' .
            $this->getId() .
            '__children" class="rule-param-children">';

        if($this->getEachXProductsActionDetails()){
            foreach($this->getEachXProductsActionDetails()->getActionDetails() as $actionDetail){
                $html .= '<li>' . $actionDetail->asHtmlRecursive() . '</li>';
            }
        }

        $html .= '<li>' . $this->getEachXProductsNewChildElement()->getHtml() . '</li></ul>';

        $html .= $this->getGetYForEachXSpentTypeElement()->getHtml().
            $this->getEachXProductsTypeElement()->getHtml();

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return "<li>".$html."</li>";
    }

    public function getDirectAttributeKeys(){
        return [
            'get_y_amount',
            'each_x_amount',
            'hints_singular',
            'hints_plural'
        ];
    }
}