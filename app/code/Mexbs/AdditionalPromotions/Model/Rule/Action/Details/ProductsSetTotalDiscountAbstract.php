<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

abstract class ProductsSetTotalDiscountAbstract extends ProductsSetAbstract{

    public function getCartBlockType(){
        return 'Mexbs\AdditionalPromotions\Block\Cart\ProductsSetFixedPriceDiscount';
    }

    public function getCartOnAddModalContentBlockType(){
        return 'Mexbs\AdditionalPromotions\Block\Cart\Add\ProductsSetFixedPriceDiscount';
    }

    protected function _getDiscountExpression($discountAmount){
        $currencySymbol = $this->apHelper->getCurrentCurrencySymbol();

        if($this->getDiscountType() == self::DISCOUNT_TYPE_PERCENT){
            return ($discountAmount == 100 ? "for free" : "with " . $discountAmount ."% discount");
        }elseif($this->getDiscountType() == self::DISCOUNT_TYPE_FIXED){
            return sprintf("with %s%s discount", $currencySymbol, $discountAmount);
        }elseif($this->getDiscountType() == self::DISCOUNT_TYPE_FIXED_PRICE){
            return sprintf("for %s%s only", $currencySymbol, $discountAmount);
        }

        return '';
    }

    protected function _getItemsQtysDataForRulePartIndexes($validRuleSetPartsIndexes){
        $itemsQtysData = [];
        foreach($validRuleSetPartsIndexes as $validRuleSetPartsIndex){
            $setPartSize = $this->getData('set_part'.$validRuleSetPartsIndex.'_size');
            $setPartHintsSingular = $this->getData('set_part'.$validRuleSetPartsIndex.'_hints_singular');
            $setPartHintsPlural = $this->getData('set_part'.$validRuleSetPartsIndex.'_hints_plural');


            $itemsQtysData[] = [
                'qty' => $setPartSize,
                'hints_singular' => $setPartHintsSingular,
                'hints_plural' => $setPartHintsPlural
            ];
        }

        return $itemsQtysData;
    }

    protected function _getCouponNotValidHintMessage(
        $setsItemsQtysToAddToGetDiscount,
        $setsItemsQtysOfTheSetThatCanGet,
        $discountAmountOfTheSetThatCanGet
    ){
        $hintMessage = "";
        if(count($setsItemsQtysToAddToGetDiscount) > 0){
            $hintMessage .= "Add ";
            $index = 0;
            foreach($setsItemsQtysToAddToGetDiscount as $setsItemQtyToAddToGetDiscount){
                if(count($setsItemsQtysToAddToGetDiscount) > 1){
                    if($index == count($setsItemsQtysToAddToGetDiscount) - 1){
                        $hintMessage .= " and ";
                    }elseif($index != 0){
                        $hintMessage .= ", ";
                    }
                }
                $hintMessage .= sprintf(
                    "%s%s %s",
                    ($setsItemQtyToAddToGetDiscount['qty'] == 1 ? "one" : $setsItemQtyToAddToGetDiscount['qty']),
                    ($setsItemQtyToAddToGetDiscount['not_added_yet'] ? "" : " more"),
                    ($setsItemQtyToAddToGetDiscount['qty'] == 1 ? $setsItemQtyToAddToGetDiscount['hints_singular'] : $setsItemQtyToAddToGetDiscount['hints_plural'])
                );
                $index++;
            }

            $hintMessage .= " to cart. ";
            $hintMessage .= "Then try applying the coupon again. ";
            $hintMessage .= "You should get the set of ";

            $discountExpression = $this->_getDiscountExpression($discountAmountOfTheSetThatCanGet);

            $index = 0;
            foreach($setsItemsQtysOfTheSetThatCanGet as $setsItemQtyOfTheSetThatCanGet){
                if(count($setsItemsQtysOfTheSetThatCanGet) > 1){
                    if($index == count($setsItemsQtysOfTheSetThatCanGet) - 1){
                        $hintMessage .= " and ";
                    }elseif($index != 0){
                        $hintMessage .= ", ";
                    }
                }
                $hintMessage .= sprintf(
                    "%s %s",
                    ($setsItemQtyOfTheSetThatCanGet['qty'] == 1 ? "one" : $setsItemQtyOfTheSetThatCanGet['qty']),
                    ($setsItemQtyOfTheSetThatCanGet['qty'] == 1 ? $setsItemQtyOfTheSetThatCanGet['hints_singular'] : $setsItemQtyOfTheSetThatCanGet['hints_plural'])
                );
                $index++;
            }

            $hintMessage .= " ".$discountExpression."!";
        }

        return $hintMessage;
    }

    protected function _getHintMessage(
        $alreadyGotSetsQty,
        $alreadyGotSetsItemsQtys,
        $alreadyGotSetsDiscountAmount,
        $setsItemsQtysToAddToGetDiscount,
        $setsItemsQtysOfTheSetThatCanGet,
        $discountAmountOfTheSetThatCanGet
    )
    {
        $hintMessage = "";
        if($alreadyGotSetsQty > 0){
            $hintMessage .= sprintf(
                "You've got %s %s of ",
                ($alreadyGotSetsQty == 1 ? "a" : $alreadyGotSetsQty),
                ($alreadyGotSetsQty == 1 ? "set" : "sets")
            );
            $index = 0;
            foreach($alreadyGotSetsItemsQtys as $alreadyGotSetsItemQty){
                if(count($alreadyGotSetsItemsQtys) > 1){
                    if($index == count($alreadyGotSetsItemsQtys) - 1){
                        $hintMessage .= " and ";
                    }elseif($index != 0){
                        $hintMessage .= ", ";
                    }
                }
                $hintMessage .= sprintf(
                    "%s %s",
                    ($alreadyGotSetsItemQty['qty'] == 1 ? "one" : $alreadyGotSetsItemQty['qty']),
                    ($alreadyGotSetsItemQty['qty'] == 1 ? $alreadyGotSetsItemQty['hints_singular'] : $alreadyGotSetsItemQty['hints_plural'])
                );
                $index++;
            }
            $discountExpression = $this->_getDiscountExpression($alreadyGotSetsDiscountAmount);
            $hintMessage .= sprintf(
                ", %s%s.",
                $discountExpression,
                ($alreadyGotSetsQty == 1 ? "": " each")
            );
        }

        if($hintMessage != ""){
            $hintMessage .= " ";
        }

        if(count($setsItemsQtysToAddToGetDiscount) > 0){
            $hintMessage .= "Add ";
            $index = 0;
            foreach($setsItemsQtysToAddToGetDiscount as $setsItemQtyToAddToGetDiscount){
                if(count($setsItemsQtysToAddToGetDiscount) > 1){
                    if($index == count($setsItemsQtysToAddToGetDiscount) - 1){
                        $hintMessage .= " and ";
                    }elseif($index != 0){
                        $hintMessage .= ", ";
                    }
                }
                $hintMessage .= sprintf(
                    "%s%s %s",
                    ($setsItemQtyToAddToGetDiscount['qty'] == 1 ? "one" : $setsItemQtyToAddToGetDiscount['qty']),
                    ($setsItemQtyToAddToGetDiscount['not_added_yet'] ? "" : " more"),
                    ($setsItemQtyToAddToGetDiscount['qty'] == 1 ? $setsItemQtyToAddToGetDiscount['hints_singular'] : $setsItemQtyToAddToGetDiscount['hints_plural'])
                );
                $index++;
            }
            $discountExpression = $this->_getDiscountExpression($discountAmountOfTheSetThatCanGet);
            $hintMessage .= ", to get ";
            if($alreadyGotSetsQty > 0){
                $hintMessage .= "your next set ";
            }else{
                $hintMessage .= "a set of ";
                $index = 0;
                foreach($setsItemsQtysOfTheSetThatCanGet as $setsItemQtyOfTheSetThatCanGet){
                    if(count($setsItemsQtysOfTheSetThatCanGet) > 1){
                        if($index == count($setsItemsQtysOfTheSetThatCanGet) - 1){
                            $hintMessage .= " and ";
                        }elseif($index != 0){
                            $hintMessage .= ", ";
                        }
                    }
                    $hintMessage .= sprintf(
                        "%s %s",
                        ($setsItemQtyOfTheSetThatCanGet['qty'] == 1 ? "one" : $setsItemQtyOfTheSetThatCanGet['qty']),
                        ($setsItemQtyOfTheSetThatCanGet['qty'] == 1 ? $setsItemQtyOfTheSetThatCanGet['hints_singular'] : $setsItemQtyOfTheSetThatCanGet['hints_plural'])
                    );
                    $index++;
                }
            }

            $hintMessage .= " ".$discountExpression."!";
        }
        return $hintMessage;
    }

    public function markMatchingItemsAndGetHint($items, $address){
        if(!$this->getRule()
            || !$this->getRule()->getId()
        ){
            return null;
        }

        if($this->apHelper->getIsOneOfTheItemsMarkedByRule($items, $this->getRule()->getId())){
            return null;
        }

        $discountType = $this->getDiscountType();
        if(!$this->_getIsDiscountTypeValid($discountType)){
            return null;
        }

        $atLeastOneSetDefined = false;
        $allOfDefinedSetsHasCartHints = true;
        for($setPartIndex = 1; $setPartIndex <= $this->getNumberOfSets(); $setPartIndex++){
            $setPartActionDetails = $this->getData('set_part'.$setPartIndex.'_action_details')->getActionDetails();
            if(isset($setPartActionDetails[0])){
                $atLeastOneSetDefined = true;

                $setPartHintsSingular = $this->getData('set_part'.$setPartIndex.'_hints_singular');
                $setPartHintsPlural = $this->getData('set_part'.$setPartIndex.'_hints_plural');

                if(!$setPartHintsSingular || !$setPartHintsPlural){
                    $allOfDefinedSetsHasCartHints = false;

                    break;
                }
            }
        }

        if(!$atLeastOneSetDefined){
            return null;
        }

        $discountAmount = $this->getDiscountAmountValue();
        if(!$this->_getIsDiscountAmountValid($discountAmount, $discountType)){
            return null;
        }

        $priceToItem = [];
        foreach($items as $item){
            $itemProductToCheckTierOrSpecialPrice = $this->_getItemToCheckTierOrSpecialPrice($item);
            if($this->_oneOfPriceTypesAppliedAndShouldSkip($item, $itemProductToCheckTierOrSpecialPrice, $this->getRule())){
                continue;
            }
            $itemPrice = $this->apHelper->getItemPrice($item);
            $extendedArraySortableKey = strval($itemPrice*10000 + rand(0,9999));
            $priceToItem[$extendedArraySortableKey] = $item;
        }

        $maxDiscountAmount = 0;
        if(is_numeric($this->getRule()->getMaxDiscountAmount())
            && ($this->getRule()->getMaxDiscountAmount() > 0)){
            $maxDiscountAmount = $this->getRule()->getMaxDiscountAmount();
        }

        if($this->getRule()->getDiscountOrderType() == self::DISCOUNT_PRICE_TYPE_CHEAPEST){
            ksort($priceToItem);
        }elseif($this->getRule()->getDiscountOrderType() == self::DISCOUNT_PRICE_TYPE_MOST_EXPENSIVE){
            krsort($priceToItem);
        }

        $validRuleSetPartsIndexes = $this->_getValidSetPartIndexes();
        $cleanAndFullSets = $this->_calcCleanAndFullSets($priceToItem, $validRuleSetPartsIndexes);
        $fullSetParts = $cleanAndFullSets['full_set_parts'];
        $cleanSets = $cleanAndFullSets['clean_sets'];

        $ruleComprehensiveDescriptionLines = [];
        $setComprehensiveDescriptionLines = [];

        $setsTotalDiscount = 0;
        $discountedSetsCount = 0;

        $exitedLoopBecauseNextSetWillExceedMaxDiscount = false;
        foreach($cleanSets as $cleanSet){
            $currentGroupItemsPriceSum = $cleanSet['price_sum'];

            if($this->getDiscountType() == self::DISCOUNT_TYPE_FIXED_PRICE){
                $discountPercentInDecimal = max(1-($discountAmount/$currentGroupItemsPriceSum), 0);
            }elseif($this->getDiscountType() == self::DISCOUNT_TYPE_FIXED){
                $discountPercentInDecimal = $discountAmount/$currentGroupItemsPriceSum;
            }elseif($this->getDiscountType() == self::DISCOUNT_TYPE_PERCENT){
                $discountPercentInDecimal = $discountAmount/100;
            }else{
                return null;
            }

            $currentSetDiscount = $currentGroupItemsPriceSum*$discountPercentInDecimal;
            if($maxDiscountAmount > 0){
                if(($setsTotalDiscount + $currentSetDiscount) > $maxDiscountAmount){
                    $exitedLoopBecauseNextSetWillExceedMaxDiscount = true;
                    break;
                }
            }
            $discountedSetsCount++;
            $setsTotalDiscount += $currentSetDiscount;

            foreach($cleanSet['items'] as $setItemData){
                $item = $setItemData['item'];
                $qtyToApplyOnItem = $setItemData['qty'];


                $itemApRuleMatches = $this->apHelper->getApRuleMatchesForItem($item);
                $itemApRuleMatches = (is_array($itemApRuleMatches) ? $itemApRuleMatches : []);

                if(!isset($itemApRuleMatches[$this->getRule()->getId()]['apply']['groups'])){
                    $itemApRuleMatches[$this->getRule()->getId()]['apply']['groups'] = [];
                }

                $itemExpectedPricesArray = $this->_getItemExpectedPricesArray($item);

                $itemApRuleMatches[$this->getRule()->getId()]['apply']['groups'][] = [
                    'qty' => $qtyToApplyOnItem,
                    'discount_percent_in_decimal' => $discountPercentInDecimal,
                    'expected_prices' => $itemExpectedPricesArray
                ];

                $item->setApRuleMatches($itemApRuleMatches);
            }

            $setComprehensiveDescription = $this->_getItemListCompDesc($cleanSet['items']);
            $setComprehensiveDescriptionLines[] = $setComprehensiveDescription;
        }

        $setComprehensiveDescriptionLinesToOccurrences = [];
        foreach($setComprehensiveDescriptionLines as $setComprehensiveDescriptionLine){
            if(!isset($setComprehensiveDescriptionLinesToOccurrences[$setComprehensiveDescriptionLine])){
                $setComprehensiveDescriptionLinesToOccurrences[$setComprehensiveDescriptionLine] = 1;
            }else{
                $setComprehensiveDescriptionLinesToOccurrences[$setComprehensiveDescriptionLine] += 1;
            }
        }

        $discountDescription = $this->_getDiscountCompDesc($this->getDiscountType(), $discountAmount);

        foreach($setComprehensiveDescriptionLinesToOccurrences as $setComprehensiveDescriptionLine => $occurrences){
            $ruleComprehensiveDescriptionLines[] = (
                $occurrences == 1 ?
                sprintf("Got %s %s", $setComprehensiveDescriptionLine, $discountDescription) :
                sprintf("Got (%s %s) x %s", $setComprehensiveDescriptionLine, $discountDescription, $occurrences)
            );
        }

        $this->_setRuleApComprehensiveDescriptionLines($this->getRule(), $ruleComprehensiveDescriptionLines, $address);

        $smallestCleanSetGroupIndex = $this->_getSmallestCleanSetGroupIndex($fullSetParts, $validRuleSetPartsIndexes);
        $itemsQtysLeftToAddUntilDiscount = [];
        if($smallestCleanSetGroupIndex != INF){
            $itemsQtysLeftToAddUntilDiscount = $this->_getItemsQtysToCompleteToCleanByIndex(
                $fullSetParts,
                $smallestCleanSetGroupIndex,
                $validRuleSetPartsIndexes
            );
        }

        $hintMessage = null;
        $hintCouponNotValidMessage = null;

        $hideHintsAfterDiscountNumber = $this->getRule()->getHideHintsAfterDiscountNumber();

        $addCartHints =
            $this->getRule()->getDisplayCartHints()
            && $allOfDefinedSetsHasCartHints
            && !$exitedLoopBecauseNextSetWillExceedMaxDiscount
            && (($this->getRule()->getMaxSetsNumber() == 0) || ($discountedSetsCount < $this->getRule()->getMaxSetsNumber()))
            && (($this->getRule()->getMaxGroupsNumber() == 0) || ($discountedSetsCount < $this->getRule()->getMaxGroupsNumber()))
            && ($hideHintsAfterDiscountNumber == 0 || ($discountedSetsCount < $hideHintsAfterDiscountNumber))
        ;

        if($addCartHints){
            $alreadyGotSetsItemsQtys = $this->_getItemsQtysDataForRulePartIndexes($validRuleSetPartsIndexes);
            $setsItemsQtysOfTheSetThatCanGet = $this->_getItemsQtysDataForRulePartIndexes($validRuleSetPartsIndexes);
            $itemsQtysLeftToAddUntilDiscountNoZeros = $this->_getItemsNamesQtysWithoutZeroQtys($itemsQtysLeftToAddUntilDiscount);
            $hintMessage = $this->_getHintMessage(
                $discountedSetsCount,
                $alreadyGotSetsItemsQtys,
                $discountAmount,
                $itemsQtysLeftToAddUntilDiscountNoZeros,
                $setsItemsQtysOfTheSetThatCanGet,
                $discountAmount
            );

            if(($discountedSetsCount == 0)
                && ($this->getRule()->getDisplayCartHintsIfCouponInvalid())){
                $hintCouponNotValidMessage = $this->_getCouponNotValidHintMessage(
                    $itemsQtysLeftToAddUntilDiscountNoZeros,
                    $setsItemsQtysOfTheSetThatCanGet,
                    $discountAmount
                );
            }
        }

        return [
            'cart_hint' => $hintMessage,
            'coupon_not_valid_cart_hint' => $hintCouponNotValidMessage
        ];
    }

    public function getDiscountAmountValueElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][discount_amount_value]',
            'value' => $this->getDiscountAmountValue(),
            'value_name' => ($this->getDiscountAmountValue() ? $this->getDiscountAmountValue() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__discount_amount_value',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getSetPartAggregatorElement($setPartIndex)
    {
        if ($this->getData('set_part'.$setPartIndex.'_aggregator') === null) {
            foreach (array_keys($this->getAggregatorOption()) as $key) {
                $this->setData('set_part'.$setPartIndex.'_aggregator', $key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_setpart'.$setPartIndex.'__' . $this->getId() . '__aggregator',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][setpart'.$setPartIndex.'][' . $this->getId() . '][aggregator]',
                'values' => $this->getAggregatorSelectOptions(),
                'value' => $this->getData('set_part'.$setPartIndex.'_aggregator'),
                'value_name' => $this->getSetPartAggregatorName($setPartIndex),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    public function getSetPartAggregatorName($setPartIndex)
    {
        return $this->getAggregatorOption($this->getData('set_part'.$setPartIndex.'_aggregator'));
    }

    public function getSetPartAggregatorValueName($setPartIndex)
    {
        if(isset($this->aggregatorValueOptions[$this->getData('set_part'.$setPartIndex.'aggregator_value')])){
            return $this->aggregatorValueOptions[$this->getData('set_part'.$setPartIndex.'aggregator_value')];
        }
        return $this->getData('set_part'.$setPartIndex.'aggregator_value');
    }

    public function getSetPartAggregatorValueElement($setPartIndex)
    {
        if ($this->getData('set_part'.$setPartIndex.'aggregator_value') === null) {
            foreach (array_keys($this->aggregatorValueOptions) as $key) {
                $this->setData('set_part'.$setPartIndex.'aggregator_value', $key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_setpart'.$setPartIndex.'__' . $this->getId() . '__aggregator_value',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][setpart'.$setPartIndex.'][' . $this->getId() . '][aggregator_value]',
                'values' => $this->aggregatorValueOptions,
                'value' => $this->getData('set_part'.$setPartIndex.'aggregator_value'),
                'value_name' => $this->getSetPartAggregatorValueName($setPartIndex),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getSetPartNewChildElement($setPartIndex)
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_setpart'.$setPartIndex.'__'.$this->getId() . '__new_child',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][setpart'.$setPartIndex.'][' . $this->getId() . '][new_child]',
                'values' => $this->getNewChildSelectOptions(),
                'value_name' => $this->getNewChildName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Newchild')
        );
    }

    public function getSetPartSizeAttributeElement($setPartIndex)
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][set_part'.$setPartIndex.'_size]',
            'value' => $this->getData('set_part'.$setPartIndex.'_size'),
            'value_name' => ($this->getData('set_part'.$setPartIndex.'_size') ? $this->getData('set_part'.$setPartIndex.'_size') : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__set_part'.$setPartIndex.'_size',
            'text',
            $elementParams
        )->setRenderer(
             $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    public function getProductsSetTypeElement()
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

    public function getSetPartTypeElement($setPartIndex)
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_setpart'.$setPartIndex.'__' . $this->getId() . '__type',
            'hidden',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][setpart'.$setPartIndex.'][' . $this->getId() . '][type]',
                'value' => 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine',
                'no_span' => true,
                'class' => 'hidden',
                'data-form-part' => $this->getFormName()
            ]
        );
    }


    public function loadSubActionArray($subActionDetailsKey, $arr, $key = 'action_details'){
        if(substr($subActionDetailsKey, 0, 7) == 'setpart'){
            $setPartIndex = substr($subActionDetailsKey, 7);
            if(is_numeric($setPartIndex)
                && $setPartIndex >=1
                && $setPartIndex<=$this->getNumberOfSets()){
                $this->setData('set_part'.$setPartIndex.'_aggregator', ($arr[$key][1]['aggregator']));
                $this->setData('set_part'.$setPartIndex.'_aggregator_value', ($arr[$key][1]['aggregator_value']));
                $setPartActionDetails = $this->_conditionFactory->create($arr[$key][1]['type']);
                $setPartActionDetails->setRule($this->getRule())
                    ->setObject($this->getObject())
                    ->setPrefix($this->getPrefix())
                    ->setSubPrefix('setpart'.$setPartIndex)
                    ->setType('Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine')
                    ->setId('1--1');

                $this->setdata('set_part'.$setPartIndex.'_action_details', $setPartActionDetails);
                $setPartActionDetails->loadArray($arr[$key][1], $key);
            }
        }
    }

    public function asSubActionArray($subActionDetailsKey){
        $out = [];

        if(substr($subActionDetailsKey, 0, 7) == 'setpart'){
            $setPartIndex = substr($subActionDetailsKey, 7);
            if(is_numeric($setPartIndex)
                && $setPartIndex >=1
                && $setPartIndex<=$this->getNumberOfSets()){
                $out = $this->getData('set_part'.$setPartIndex.'_action_details')->asArray();
            }
        }

        return $out;
    }

    public function getSubActionDetailsKeys(){
        $subActionDetailKeys = [];
        for($setPartIndex = 1; $setPartIndex <= $this->getNumberOfSets(); $setPartIndex++){
            $subActionDetailKeys[] = 'setpart'.$setPartIndex;
        }
        return $subActionDetailKeys;
    }

    public function getDirectAttributeKeys(){
        $directAttributeKeys = [
            'discount_amount_value'
        ];
        for($setPartIndex = 1; $setPartIndex <= $this->getNumberOfSets(); $setPartIndex++){
            $directAttributeKeys[] = 'set_part'.$setPartIndex.'_size';
            $directAttributeKeys[] = 'set_part'.$setPartIndex.'_hints_singular';
            $directAttributeKeys[] = 'set_part'.$setPartIndex.'_hints_plural';
        }
        return $directAttributeKeys;
    }

    public function asArray(array $arrAttributes = [])
    {
        //this method shouldn't be used, instead loadSubActionArray should be
    }

}