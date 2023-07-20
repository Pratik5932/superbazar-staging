<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

class GetYForEachXSpentUpToN extends \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetYForEachXSpentAbstract{

    const SIMPLE_ACTION = 'get_y_for_each_x_spent_up_to_n_action';
    protected $type = 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetYForEachXSpentUpToN';

    public function getDiscountMaxQty(){
        return $this->getData('discount_max_qty');
    }

    public function isDiscountPriceTypeApplicable(){
        return true;
    }

    public function isDiscountOrderTypeApplicable(){
        return false;
    }

    public function getSimpleAction(){
        return self::SIMPLE_ACTION;
    }

    public function getDiscountMaxQtyAttributeElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][discount_max_qty]',
            'value' => $this->getDiscountMaxQty(),
            'value_name' => ($this->getDiscountMaxQty() ? $this->getDiscountMaxQty() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__discount_max_qty',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }


    public function asHtmlRecursive()
    {
        $html =  __(
                "Get %1%2 for each %3%4 spent on up to %5 %6 items [label for upsell cart hints - singular: %7, plural: %8] for which %9 of the following conditions are %10:",
                $this->getGetYAmountAttributeElement()->getHtml(),
                $this->apHelper->getCurrentCurrencySymbol(),
                $this->getEachXAmountAttributeElement()->getHtml(),
                $this->apHelper->getCurrentCurrencySymbol(),
                $this->getDiscountMaxQtyAttributeElement()->getHtml(),
                $this->getDiscountPriceTypeAttributeElement()->getHtml(),
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
            'hints_plural',
            'discount_max_qty',
            'discount_price_type'
        ];
    }

}