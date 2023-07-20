<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

abstract class GetYForEachXSpentAbstract extends \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine{
    abstract public function getDiscountMaxQty();
    abstract public function isDiscountPriceTypeApplicable();
    abstract public function isDiscountOrderTypeApplicable();

    protected function _getCouponNotValidHintMessage(
        $amountToAddToGetNextDiscount,
        $discountedItemHintPlural,
        $eachXAmount,
        $getYAmount
    ){
        $hintMessage = sprintf(
            "Add some %s%s to cart (of %s$ or more total worth). Then try applying the coupon again. You should get $%s discount on each $%s spent on %s!",
            ($amountToAddToGetNextDiscount < $eachXAmount ? "more " : ""),
            $discountedItemHintPlural,
            $eachXAmount,
            $getYAmount,
            $eachXAmount,
            $discountedItemHintPlural
        );
        return $hintMessage;
    }

    protected function _getHintMessage(
        $discountAmountGot,
        $amountToAddToGetNextDiscount,
        $nextDiscountAmount,
        $discountedItemHintSingular,
        $discountedItemHintPlural
    )
    {
        $hintMessage = "";
        if($discountAmountGot > 0){
            $hintMessage .= sprintf(
                "You've got %s%s discount on %s.",
                $this->apHelper->getCurrentCurrencySymbol(),
                $discountAmountGot,
                $discountedItemHintPlural
            );
        }
        if($hintMessage != ""){
            $hintMessage .= " ";
        }
        if($amountToAddToGetNextDiscount > 0
            && $nextDiscountAmount > 0){
            $hintMessage .= sprintf(
                "Add %s%s worth (or more) of %s, to get your %s%s%s discount!",
                $this->apHelper->getCurrentCurrencySymbol(),
                $amountToAddToGetNextDiscount,
                $discountedItemHintPlural,
                ($discountAmountGot > 0 ? "next " : ""),
                $this->apHelper->getCurrentCurrencySymbol(),
                $nextDiscountAmount
            );
        }
        return $hintMessage;
    }


    public function markMatchingItemsAndGetHint($items, $address){
        if(!$this->getEachXProductsActionDetails()
            || !$this->getRule()
            || !$this->getRule()->getId()
        ){
            return null;
        }
        if($this->apHelper->getIsOneOfTheItemsMarkedByRule($items, $this->getRule()->getId())){
            return null;
        }


        $eachXProductsActionDetails = $this->getEachXProductsActionDetails()->getActionDetails();
        if(!isset($eachXProductsActionDetails[0])){
            return null;
        }
        $eachXProductsActionDetail = $this->getEachXProductsActionDetails();

        $maxDiscountQty = $this->getDiscountMaxQty();
        if($maxDiscountQty == 0){
            return null;
        }

        $eachXAmount = $this->getEachXAmount();
        if(!is_numeric($eachXAmount) || !$eachXAmount){
            return null;
        }

        $getYAmount = $this->getGetYAmount();
        if(!is_numeric($getYAmount) || !$getYAmount){
            return null;
        }

        $maxDiscountAmount = INF;
        if(is_numeric($this->getRule()->getMaxDiscountAmount())
            && ($this->getRule()->getMaxDiscountAmount() > 0)){
            $maxDiscountAmount = $this->getRule()->getMaxDiscountAmount();
        }

        $validPriceToItem = [];
        foreach($items as $item){
            $itemProductToCheckTierOrSpecialPrice = $this->_getItemToCheckTierOrSpecialPrice($item);
            if($this->_oneOfPriceTypesAppliedAndShouldSkip($item, $itemProductToCheckTierOrSpecialPrice, $this->getRule())){
                continue;
            }
            if($this->apHelper->validateActionDetail($eachXProductsActionDetail, $item)){
                $itemPrice = $this->apHelper->getItemPrice($item);
                $extendedArraySortableKey = strval($itemPrice*10000 + rand(0,9999));
                $validPriceToItem[$extendedArraySortableKey] = $item;
            }
        }

        $hintsSingular = $this->getHintsSingular();
        $hintsPlural = $this->getHintsPlural();

        if(count($validPriceToItem) == 0){
            $hintMessage = null;
            $hintCouponNotValidMessage = null;

            $addCartHints = (
                $this->getRule()->getDisplayCartHints()
                && $hintsSingular
                && $hintsPlural
            );

            if($addCartHints){
                $amountToAddToGetNextDiscount = $eachXAmount;

                $hintMessage = $this->_getHintMessage(
                    0,
                    $amountToAddToGetNextDiscount,
                    min($getYAmount, $maxDiscountAmount),
                    $hintsSingular,
                    $hintsPlural
                );

                if($this->getRule()->getDisplayCartHintsIfCouponInvalid()){
                    $hintCouponNotValidMessage = $this->_getCouponNotValidHintMessage(
                        $amountToAddToGetNextDiscount,
                        $hintsPlural,
                        $eachXAmount,
                        $getYAmount
                    );
                }
            }

            return [
                'cart_hint' => $hintMessage,
                'coupon_not_valid_cart_hint' => $hintCouponNotValidMessage
            ];
        }

        if($this->isDiscountPriceTypeApplicable()){
            if($this->getDiscountPriceType() == self::DISCOUNT_PRICE_TYPE_CHEAPEST ){
                ksort($validPriceToItem);
            }elseif($this->getDiscountPriceType() == self::DISCOUNT_PRICE_TYPE_MOST_EXPENSIVE){
                krsort($validPriceToItem);
            }
        }elseif($this->isDiscountOrderTypeApplicable()){
            if($this->getRule()->getDiscountOrderType() == self::DISCOUNT_PRICE_TYPE_CHEAPEST ){
                ksort($validPriceToItem);
            }elseif($this->getRule()->getDiscountOrderType() == self::DISCOUNT_PRICE_TYPE_MOST_EXPENSIVE){
                krsort($validPriceToItem);
            }
        }

        $appliedItemsTotalQty = 0;
        $eachXRemainder = 0;

        $ruleComprehensiveDescriptionLines = [];
        $affectedProductNames = [];

        $itemsQtys = [];

        $totalGetYAmountApplied = 0;
        $totalEachXAmountOnItems = 0;
        $itemsQtyLeftToApplyWithinMaxQty = $maxDiscountQty;
        $amountLeftToApplyWithinMaxAmount = $maxDiscountAmount;

        foreach($validPriceToItem as $item){
            $itemsQtyLeftToApplyWithinMaxQty = max($maxDiscountQty-$appliedItemsTotalQty, 0);
            $qtyToApplyOnItem = max(0, min($item->getQty(), $itemsQtyLeftToApplyWithinMaxQty));
            if($qtyToApplyOnItem == 0){
                break;
            }

            $appliedItemsTotalQty += $qtyToApplyOnItem;

            $itemPrice = $this->apHelper->getItemPrice($item);

            $currentItemEachXAmount = ($itemPrice*$qtyToApplyOnItem)+$eachXRemainder;
            $currentApplicableItemEachXAmount = floor($currentItemEachXAmount/$eachXAmount)*$eachXAmount;

            $totalEachXAmountOnItems += $currentApplicableItemEachXAmount;
            $eachXRemainder = $currentItemEachXAmount%$eachXAmount;


            $getYAmountOnItemWithoutLimit = ($currentApplicableItemEachXAmount/$eachXAmount)*$getYAmount;

            $amountLeftToApplyWithinMaxAmount = max($maxDiscountAmount - $totalGetYAmountApplied, 0);
            $getYAmountOnItem = min($getYAmountOnItemWithoutLimit, $amountLeftToApplyWithinMaxAmount);

            $totalGetYAmountApplied += max($getYAmountOnItem, 0);

            if($getYAmountOnItem <= 0){
                continue;
            }


            $itemApRuleMatches = $this->apHelper->getApRuleMatchesForItem($item);
            $itemApRuleMatches = (is_array($itemApRuleMatches) ? $itemApRuleMatches : []);

            $itemExpectedPricesArray = $this->_getItemExpectedPricesArray($item);
            $itemApRuleMatches[$this->getRule()->getId()]['apply'] = [
                'qty' => $qtyToApplyOnItem,
                'get_y_amount' => $getYAmountOnItem,
                'expected_prices' => $itemExpectedPricesArray
            ];

            $itemsQtys[] = [
                'qty' => $qtyToApplyOnItem,
                'item' => $item
            ];

            $ruleComprehensiveDescriptionLines[] =
                sprintf(
                    "Got %s%s for %s%s spent on %s\n",
                    $this->apHelper->getCurrentCurrencySymbol(),
                    $getYAmountOnItem,
                    $this->apHelper->getCurrentCurrencySymbol(),
                    $itemPrice*$qtyToApplyOnItem,
                    ($qtyToApplyOnItem > 1 ? $qtyToApplyOnItem." of ".$item->getName() : $item->getName())
                );

            $affectedProductNames[] = $item->getName();

            $item->setApRuleMatches($itemApRuleMatches);
        }

        $totalEachXAmountOnItems += $eachXRemainder;

        $itemsQtyLeftToApplyWithinMaxQty = max($maxDiscountQty-$appliedItemsTotalQty, 0);
        $amountLeftToApplyWithinMaxAmount = max($maxDiscountAmount - $totalGetYAmountApplied, 0);

        $hintMessage = null;
        $hintCouponNotValidMessage = null;

        $hideHintsAfterDiscountNumber = $this->getRule()->getHideHintsAfterDiscountNumber();

        $addCartHints = (
            $this->getRule()->getDisplayCartHints()
            && $hintsSingular
            && $hintsPlural
            && ($itemsQtyLeftToApplyWithinMaxQty > 0)
            && ($amountLeftToApplyWithinMaxAmount > 0)
            && ($hideHintsAfterDiscountNumber == 0 || ($appliedItemsTotalQty < $hideHintsAfterDiscountNumber))
        );

        if($addCartHints){
            $amountToAddToGetNextDiscount = $eachXAmount - ($totalEachXAmountOnItems%$eachXAmount);

            $hintMessage = $this->_getHintMessage(
                $totalGetYAmountApplied,
                $amountToAddToGetNextDiscount,
                max(min($getYAmount, $amountLeftToApplyWithinMaxAmount), 0),
                $hintsSingular,
                $hintsPlural
            );

            if($this->getRule()->getDisplayCartHintsIfCouponInvalid()
                && ($totalGetYAmountApplied < $eachXAmount)){
                $hintCouponNotValidMessage = $this->_getCouponNotValidHintMessage(
                    $amountToAddToGetNextDiscount,
                    $hintsPlural,
                    $eachXAmount,
                    $getYAmount
                );
            }
        }
        $this->_setRuleApComprehensiveDescriptionLines($this->getRule(), $ruleComprehensiveDescriptionLines, $address);

        return [
            'cart_hint' => $hintMessage,
            'coupon_not_valid_cart_hint' => $hintCouponNotValidMessage
        ];
    }


    public function getEachXProductsAggregatorName()
    {
        return $this->getAggregatorOption($this->getEachXProductsAggregator());
    }

    public function getEachXProductsAggregatorElement()
    {
        if ($this->getEachXProductsAggregator() === null) {
            foreach (array_keys($this->getAggregatorOption()) as $key) {
                $this->setEachXProductsAggregator($key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_eachxproducts__' . $this->getId() . '__aggregator',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][eachxproducts][' . $this->getId() . '][aggregator]',
                'values' => $this->getAggregatorSelectOptions(),
                'value' => $this->getEachXProductsAggregator(),
                'value_name' => $this->getEachXProductsAggregatorName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getEachXProductsValueName()
    {
        if(isset($this->aggregatorValueOptions[$this->getEachXProductsAggregatorValue()])){
            return $this->aggregatorValueOptions[$this->getEachXProductsAggregatorValue()];
        }
        return $this->getEachXProductsValue();
    }

    public function getEachXProductsAggregatorValueElement()
    {
        if ($this->getEachXProductsAggregatorValue() === null) {
            foreach (array_keys($this->aggregatorValueOptions) as $key) {
                $this->setEachXProductsAggregatorValue($key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_eachxproducts__' . $this->getId() . '__aggregator_value',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][eachxproducts][' . $this->getId() . '][aggregator_value]',
                'values' => $this->aggregatorValueOptions,
                'value' => $this->getEachXProductsAggregatorValue(),
                'value_name' => $this->getEachXProductsValueName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getEachXProductsNewChildElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_eachxproducts__' . $this->getId() . '__new_child',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][eachxproducts][' . $this->getId() . '][new_child]',
                'values' => $this->getNewChildSelectOptions(),
                'value_name' => $this->getNewChildName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Newchild')
            );
    }

    public function getGetYAmountAttributeElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][get_y_amount]',
            'value' => $this->getGetYAmount(),
            'value_name' => ($this->getGetYAmount() ? $this->getGetYAmount() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__get_y_amount',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getEachXAmountAttributeElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][each_x_amount]',
            'value' => $this->getEachXAmount(),
            'value_name' => ($this->getEachXAmount() ? $this->getEachXAmount() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__each_x_amount',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getGetYForEachXSpentTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__type',
            'hidden',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][type]',
                'value' => $this->getType(),
                'no_span' => true,
                'class' => 'hidden',
                'data-form-part' => $this->getFormName()
            ]
        );
    }

    public function getEachXProductsTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_eachxproducts__' . $this->getId() . '__type',
            'hidden',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][eachxproducts][' . $this->getId() . '][type]',
                'value' => 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine',
                'no_span' => true,
                'class' => 'hidden',
                'data-form-part' => $this->getFormName()
            ]
        );
    }

    public function getHintsSingularElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][hints_singular]',
            'value' => $this->getHintsSingular(),
            'value_name' => ($this->getHintsSingular() ? $this->getHintsSingular() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__hints_singular',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getHintsPluralElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][hints_plural]',
            'value' => $this->getHintsPlural(),
            'value_name' => ($this->getHintsPlural() ? $this->getHintsPlural() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__hints_plural',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function loadSubActionArray($subActionDetailsKey, $arr, $key = 'action_details'){
        if($subActionDetailsKey == 'eachxproducts'){
            $this->setEachXProductsAggregator($arr[$key][1]['aggregator']);
            $this->setEachXProductsAggregatorValue($arr[$key][1]['aggregator_value']);
            $eachXProductsActionDetails = $this->_conditionFactory->create($arr[$key][1]['type']);
            $eachXProductsActionDetails->setRule($this->getRule())
                ->setObject($this->getObject())
                ->setPrefix($this->getPrefix())
                ->setSubPrefix('eachxproducts')
                ->setType('Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine')
                ->setId('1--1');

            $this->setEachXProductsActionDetails($eachXProductsActionDetails);
            $eachXProductsActionDetails->loadArray($arr[$key][1], $key);
        }
    }

    public function asSubActionArray($subActionDetailsKey){
        $out = [];
        if($subActionDetailsKey == 'eachxproducts'){
            $out = $this->getEachXProductsActionDetails()->asArray();
        }
        return $out;
    }

    public function getSubActionDetailsKeys(){
        return [
            'eachxproducts'
        ];
    }

    public function asArray(array $arrAttributes = [])
    {
        //this method shouldn't be used, instead loadSubActionArray should be
    }

}