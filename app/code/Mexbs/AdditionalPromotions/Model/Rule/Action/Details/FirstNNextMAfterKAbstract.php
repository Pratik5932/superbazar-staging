<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

abstract class FirstNNextMAfterKAbstract extends \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine{
    abstract public function getDiscountType();
    abstract public function getNumberOfSeqs();

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

    protected function _getValidSetPartIndexes($discountType){
        $validSetPartIndexes = [];
        for($seqPartIndex = 1; $seqPartIndex <= $this->getNumberOfSeqs(); $seqPartIndex++){
            $discountAmount = $this->getData('discount_amount_value'.$seqPartIndex);
            if($discountAmount){
                if(!$this->_getIsDiscountAmountValid($discountAmount, $discountType)){
                    continue;
                }
            }
            $discountQty = $this->getData('discount_qty_value'.$seqPartIndex);
            if(!$discountQty){
                continue;
            }
            $validSetPartIndexes[] = $seqPartIndex;
        }
        return $validSetPartIndexes;
    }

    protected function _getCouponNotValidHintMessage(
        $validPriceToItemQty,
        $qtyLeftToAddForFullPrice,
        $followingDiscountedItemsDiscountAmount,
        $hintsSingular,
        $hintsPlural
    ){
        $discountExpression = $this->_getDiscountExpression($followingDiscountedItemsDiscountAmount);
        $hintMessage = sprintf(
            "Add %s%s %s to cart. Then try applying the coupon again. You should get %s %s %s!",
            ($qtyLeftToAddForFullPrice +  1 == 1 ? "one" : $qtyLeftToAddForFullPrice +  1),
            ($validPriceToItemQty > 0 ? " more" : ""),
            ($qtyLeftToAddForFullPrice +  1 == 1 ? $hintsSingular : $hintsPlural),
            ($qtyLeftToAddForFullPrice +  1 == 1 ? "the" : "one"),
            $hintsSingular,
            $discountExpression
        );
        return $hintMessage;
    }

    protected function _getHintMessage(
        $matchingItemsCount,
        $alreadyDiscountedItemsData,
        $qtyLeftToAddForFullPrice,
        $followingDiscountedItemsQty,
        $followingDiscountedItemsDiscountAmount,
        $itemsHintsSingular,
        $itemsHintsPlural
    ){
        $hintMessage = "";

        if(!empty($alreadyDiscountedItemsData)){
            $hintMessage .= "You've got ";
            $index = 0;
            foreach($alreadyDiscountedItemsData as $alreadyDiscountedItemsDataItem){
                $alreadyDiscountedItemsDataItemDiscountExpression = $this->_getDiscountExpression($alreadyDiscountedItemsDataItem['discount_amount']);
                if(count($alreadyDiscountedItemsData) > 1){
                    if($index == count($alreadyDiscountedItemsData) - 1){
                        $hintMessage .= " and ";
                    }elseif($index != 0){
                        $hintMessage .= ", ";
                    }
                }
                $hintMessage .= sprintf(
                    "%s %s %s",
                    ($alreadyDiscountedItemsDataItem['qty'] == 1 ? 'one' : $alreadyDiscountedItemsDataItem['qty']),
                    ($alreadyDiscountedItemsDataItem['qty'] == 1 ? $itemsHintsSingular : $itemsHintsPlural),
                    ($alreadyDiscountedItemsDataItem['qty'] == 1 ? $alreadyDiscountedItemsDataItemDiscountExpression : $alreadyDiscountedItemsDataItemDiscountExpression." each")
                );
                $index++;
            }
            $hintMessage.= ".";
        }

        if($hintMessage != ""){
            $hintMessage .= " ";
        }

        $followingDiscountedItemsDiscountExpression = $this->_getDiscountExpression($followingDiscountedItemsDiscountAmount);
        if($qtyLeftToAddForFullPrice > 0){
            if($matchingItemsCount > 0){
                $hintMessage .= sprintf(
                    "Add another %s%s for a full price, to get the following %s%s %s.",
                    ($qtyLeftToAddForFullPrice == 1 ? "" : $qtyLeftToAddForFullPrice." "),
                    ($qtyLeftToAddForFullPrice == 1 ? $itemsHintsSingular : $itemsHintsPlural),
                    ($followingDiscountedItemsQty == 1 ? "" : $followingDiscountedItemsQty." "),
                    ($followingDiscountedItemsQty == 1 ? $itemsHintsSingular : $itemsHintsPlural),
                    ($followingDiscountedItemsQty == 1 ? $followingDiscountedItemsDiscountExpression : $followingDiscountedItemsDiscountExpression." each")
                );
            }else{
                $hintMessage .= sprintf(
                    "Add %s %s for a full price, to get the following %s%s %s.",
                    ($qtyLeftToAddForFullPrice == 1 ? "one" : $qtyLeftToAddForFullPrice),
                    ($qtyLeftToAddForFullPrice == 1 ? $itemsHintsSingular : $itemsHintsPlural),
                    ($followingDiscountedItemsQty == 1 ? "" : $followingDiscountedItemsQty." "),
                    ($followingDiscountedItemsQty == 1 ? $itemsHintsSingular : $itemsHintsPlural),
                    ($followingDiscountedItemsQty == 1 ? $followingDiscountedItemsDiscountExpression : $followingDiscountedItemsDiscountExpression." each")
                );
            }

        }else{
            if($matchingItemsCount > 0){
                $hintMessage .= sprintf(
                    "Get your next %s%s %s.",
                    ($followingDiscountedItemsQty == 1 ? "" : $followingDiscountedItemsQty." "),
                    ($followingDiscountedItemsQty == 1 ? $itemsHintsSingular : $itemsHintsPlural),
                    ($followingDiscountedItemsQty == 1 ? $followingDiscountedItemsDiscountExpression : $followingDiscountedItemsDiscountExpression." each")
                );
            }else{
                $hintMessage .= sprintf(
                    "You can now add %s %s to cart %s.",
                    ($followingDiscountedItemsQty == 1 ? "one" : $followingDiscountedItemsQty),
                    ($followingDiscountedItemsQty == 1 ? $itemsHintsSingular : $itemsHintsPlural),
                    $followingDiscountedItemsDiscountExpression,
                    ($followingDiscountedItemsQty == 1 ? $followingDiscountedItemsDiscountExpression : $followingDiscountedItemsDiscountExpression." each")
                );
            }
        }

        return $hintMessage;
    }

    public function markMatchingItemsAndGetHint($items, $address){
        if(!$this->getFirstNNextMActionDetails()
            || !$this->getRule()
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

        $firstNNextMActionDetails = $this->getFirstNNextMActionDetails()->getActionDetails();
        if(!isset($firstNNextMActionDetails[0])){
            return null;
        }
        $firstNNextMActionDetail = $this->getFirstNNextMActionDetails();

        $validSetPartIndexes = $this->_getValidSetPartIndexes($discountType);
        if(!count($validSetPartIndexes)){
            return null;
        }

        $afterMQty = 0;
        if(
            is_numeric($this->getAfterMQty())
            && $this->getAfterMQty()
        ){
            $afterMQty = $this->getAfterMQty();
        }

        $validPriceToItem = [];
        $validPriceToItemQty = 0;
        foreach($items as $item){
            $itemProductToCheckTierOrSpecialPrice = $this->_getItemToCheckTierOrSpecialPrice($item);
            if($this->_oneOfPriceTypesAppliedAndShouldSkip($item, $itemProductToCheckTierOrSpecialPrice, $this->getRule())){
                continue;
            }

            if($this->apHelper->validateActionDetail($firstNNextMActionDetail, $item)){
                $itemPrice = $this->apHelper->getItemPrice($item);
                $extendedArraySortableKey = strval($itemPrice*10000 + rand(0,9999));
                $validPriceToItem[$extendedArraySortableKey] = $item;
                $validPriceToItemQty += $item->getQty();
            }
        }

        $hintCouponNotValidMessage = null;

        $hintsSingular = $this->getHintsSingular();
        $hintsPlural = $this->getHintsPlural();

        $addCartHints = (
            $this->getRule()->getDisplayCartHints()
            && $hintsSingular
            && $hintsPlural
        );

        if($validPriceToItemQty <= $afterMQty
            || ($validPriceToItemQty == 0)){
            if($addCartHints){
                $qtyLeftToAddForFullPrice = $afterMQty - $validPriceToItemQty;

                $firstValidSetIndex = $validSetPartIndexes[0];
                $followingDiscountedItemsQty =  $this->getData('discount_qty_value'.$firstValidSetIndex);
                $followingDiscountedItemsDiscountAmount =  $this->getData('discount_amount_value'.$firstValidSetIndex);

                $alreadyDiscountedItemsData = [];

                if($followingDiscountedItemsQty > 0){
                    $hintMessage = $this->_getHintMessage(
                        $validPriceToItemQty,
                        $alreadyDiscountedItemsData,
                        $qtyLeftToAddForFullPrice,
                        $followingDiscountedItemsQty,
                        $followingDiscountedItemsDiscountAmount,
                        $hintsSingular,
                        $hintsPlural
                    );
                }

                if((count($alreadyDiscountedItemsData) == 0)
                    && $this->getRule()->getDisplayCartHintsIfCouponInvalid()){
                    $hintCouponNotValidMessage = $this->_getCouponNotValidHintMessage(
                        $validPriceToItemQty,
                        $qtyLeftToAddForFullPrice,
                        $followingDiscountedItemsDiscountAmount,
                        $hintsSingular,
                        $hintsPlural
                    );
                }
            }

            if(isset($hintMessage)
                && $hintMessage){
                return [
                    'cart_hint' => $hintMessage,
                    'coupon_not_valid_cart_hint' => $hintCouponNotValidMessage
                ];
            }else{
                return null;
            }
        }

        if($this->getRule()->getDiscountOrderType() == self::DISCOUNT_PRICE_TYPE_CHEAPEST ){
            ksort($validPriceToItem);
        }elseif($this->getRule()->getDiscountOrderType() == self::DISCOUNT_PRICE_TYPE_MOST_EXPENSIVE){
            krsort($validPriceToItem);
        }

        $maxDiscountAmount = 0;
        if(is_numeric($this->getRule()->getMaxDiscountAmount())
            && ($this->getRule()->getMaxDiscountAmount() > 0)){
            $maxDiscountAmount = $this->getRule()->getMaxDiscountAmount();
        }

        $appliedItemsTotalQty = 0;
        $appliedItemsTotalAmount = 0;

        $validPriceToItemKeys = array_keys($validPriceToItem);
        $currentKeyIndex = 0;

        $validSetPartArrayIndex = 0;
        $qtyDiscountedOnCurrentDiscountIndex = 0;
        $qtyDiscountedOnLastDiscountIndex = 0;
        $qtyDiscountedOnCurrentItem = 0;
        $fullPriceQtyOnCurrentItem = 0;

        $fullPriceItemsQty = 0;

        $itemsQtysPerDiscAmount = [];
        $itemsQtysAlreadyDiscountedPerDiscountIndex = [];

        $lastUsedDiscountIndex = -1;
        $oneOfTheItemsLimitedByMaxDiscountAmount = false;

        while($validSetPartArrayIndex < count($validSetPartIndexes)){
            $currentPriceDiscountIndex = $validSetPartIndexes[$validSetPartArrayIndex];
            if($currentKeyIndex >= count($validPriceToItemKeys)){
                break;
            }
            $currentItemKey = $validPriceToItemKeys[$currentKeyIndex];
            $item = $validPriceToItem[$currentItemKey];

            $discountAmount = $this->getData('discount_amount_value'.$currentPriceDiscountIndex);
            $discountQty = $this->getData('discount_qty_value'.$currentPriceDiscountIndex);


            if($qtyDiscountedOnCurrentDiscountIndex >= $discountQty){
                $validSetPartArrayIndex++;

                $qtyDiscountedOnCurrentDiscountIndex = 0;
                continue;
            }

            if(($fullPriceQtyOnCurrentItem + $qtyDiscountedOnCurrentItem) >= $item->getQty()){
                $currentKeyIndex++;

                $qtyDiscountedOnCurrentItem = 0;
                $fullPriceQtyOnCurrentItem = 0;
                continue;
            }

            $lastUsedDiscountIndex = $currentPriceDiscountIndex;

            $itemPrice = $this->apHelper->getItemPrice($item);
            $maxQtyForDiscountOnItem = max($item->getQty() - $qtyDiscountedOnCurrentItem - $fullPriceQtyOnCurrentItem, 0);

            if($afterMQty
                && ($fullPriceQtyOnCurrentItem  == 0)){
                $fullPriceQtyOnCurrentItem = min(max($afterMQty - $fullPriceItemsQty, 0), $item->getQty());
                $fullPriceItemsQty += $fullPriceQtyOnCurrentItem;

                if($fullPriceQtyOnCurrentItem > 0){
                    $maxQtyForDiscountOnItem = $maxQtyForDiscountOnItem - $fullPriceQtyOnCurrentItem;
                }
            }

            if($this->getDiscountType() == self::DISCOUNT_TYPE_FIXED_PRICE){
                $discountPercentInDecimal = 1-($discountAmount/$itemPrice);
            }elseif($this->getDiscountType() == self::DISCOUNT_TYPE_FIXED){
                $discountPercentInDecimal = $discountAmount/$itemPrice;
            }elseif($this->getDiscountType() == self::DISCOUNT_TYPE_PERCENT){
                $discountPercentInDecimal = $discountAmount/100;
            }else{
                $discountPercentInDecimal = 0; //@TODO - log error
            }

            if($maxDiscountAmount > 0){
                $qtyToApplyOnItemWithinAmountLimit = floor(max($maxDiscountAmount - $appliedItemsTotalAmount, 0) / ($discountPercentInDecimal*$itemPrice));
                $qtyToApplyOnItem = min($maxQtyForDiscountOnItem, $qtyToApplyOnItemWithinAmountLimit);
                if($qtyToApplyOnItemWithinAmountLimit < $maxQtyForDiscountOnItem){
                    $oneOfTheItemsLimitedByMaxDiscountAmount = true;
                }
            }else{
                $qtyToApplyOnItem = $maxQtyForDiscountOnItem;
            }

            if($qtyToApplyOnItem <= 0){
                $currentKeyIndex++;

                $qtyDiscountedOnCurrentItem = 0;
                $fullPriceQtyOnCurrentItem = 0;
                continue;
            }

            $qtyToApplyOnItemForCurrentDiscIndex = min(
                max($discountQty - $qtyDiscountedOnCurrentDiscountIndex, 0),
                $qtyToApplyOnItem
            );

            $qtyDiscountedOnCurrentItem += $qtyToApplyOnItemForCurrentDiscIndex;
            $qtyDiscountedOnCurrentDiscountIndex += $qtyToApplyOnItemForCurrentDiscIndex;
            $qtyDiscountedOnLastDiscountIndex = $qtyDiscountedOnCurrentDiscountIndex;

            $appliedItemsTotalQty += $qtyToApplyOnItemForCurrentDiscIndex;

            $discountAmountOnItemUnit = $this->_getDiscountAmountOnItemUnit($itemPrice, $this->getDiscountType(), $discountAmount);
            $appliedItemsTotalAmount += $discountAmountOnItemUnit*$qtyToApplyOnItemForCurrentDiscIndex;

            $qtysDiscsOnItem = [];

            $itemApRuleMatches = $this->apHelper->getApRuleMatchesForItem($item);
            $itemApRuleMatches = (is_array($itemApRuleMatches) ? $itemApRuleMatches : []);

            if(isset($itemApRuleMatches[$this->getRule()->getId()]['apply']['qtys_discs'])){
                $qtysDiscsOnItem = $itemApRuleMatches[$this->getRule()->getId()]['apply']['qtys_discs'];
            }

            $qtysDiscsOnItem[] = [
                'qty' => $qtyToApplyOnItemForCurrentDiscIndex,
                'discount_percent_in_decimal' => $discountPercentInDecimal
            ];

            $itemExpectedPricesArray = $this->_getItemExpectedPricesArray($item);
            $itemApRuleMatches[$this->getRule()->getId()]['apply'] = [
                'qtys_discs' => $qtysDiscsOnItem,
                'expected_prices' => $itemExpectedPricesArray
            ];

            $item->setApRuleMatches($itemApRuleMatches);

            if(!isset($itemsQtysPerDiscAmount[$discountAmount])){
                $itemsQtysPerDiscAmount[$discountAmount] = [];
            }

            $itemsQtysPerDiscAmount[$discountAmount] = array_merge(
                $itemsQtysPerDiscAmount[$discountAmount],
                [
                    [
                        'qty' => $qtyToApplyOnItemForCurrentDiscIndex,
                        'item' => $item
                    ]
                ]
            );

            if(!isset($itemsQtysAlreadyDiscountedPerDiscountIndex[$currentPriceDiscountIndex])){
                $itemsQtysAlreadyDiscountedPerDiscountIndex[$currentPriceDiscountIndex] = [];

                $itemsQtysAlreadyDiscountedPerDiscountIndex[$currentPriceDiscountIndex]['qty'] = 0;
                $itemsQtysAlreadyDiscountedPerDiscountIndex[$currentPriceDiscountIndex]['discount_amount'] = $discountAmount;
            }

            $itemsQtysAlreadyDiscountedPerDiscountIndex[$currentPriceDiscountIndex]['qty'] = $qtyDiscountedOnCurrentDiscountIndex;
        }

        $hintMessage = null;

        $hideHintsAfterDiscountNumber = $this->getRule()->getHideHintsAfterDiscountNumber();

        $addCartHints = (
            $addCartHints
            && ($lastUsedDiscountIndex != -1)
            && (!$oneOfTheItemsLimitedByMaxDiscountAmount)
            && ($hideHintsAfterDiscountNumber == 0 || ($appliedItemsTotalQty < $hideHintsAfterDiscountNumber))
        );

        if($addCartHints){
            $qtyLeftToAddForFullPrice = max($afterMQty - $fullPriceItemsQty, 0);

            $followingDiscountedItemsQty = $this->getData('discount_qty_value'.$lastUsedDiscountIndex);
            $followingDiscountedItemsDiscountAmount = $this->getData('discount_amount_value'.$lastUsedDiscountIndex);

            $followingDiscountedItemsQtyLeftInSet =  $followingDiscountedItemsQty - $qtyDiscountedOnLastDiscountIndex;

            if($followingDiscountedItemsQtyLeftInSet == 0
                && count($validSetPartIndexes) > $lastUsedDiscountIndex){
                $followingDiscountedItemsQty = $this->getData('discount_qty_value'.($lastUsedDiscountIndex + 1));
                $followingDiscountedItemsDiscountAmount = $this->getData('discount_amount_value'.($lastUsedDiscountIndex + 1));

                $followingDiscountedItemsQtyLeftInSet = $followingDiscountedItemsQty;
            }

            if($followingDiscountedItemsQtyLeftInSet > 0){
                $hintMessage = $this->_getHintMessage(
                    $validPriceToItemQty,
                    $itemsQtysAlreadyDiscountedPerDiscountIndex,
                    $qtyLeftToAddForFullPrice,
                    $followingDiscountedItemsQtyLeftInSet,
                    $followingDiscountedItemsDiscountAmount,
                    $hintsSingular,
                    $hintsPlural
                );
            }
        }

        $ruleComprehensiveDescriptionLines = [];

        foreach($itemsQtysPerDiscAmount as $discountAmount => $itemsQtys){
            $itemsListDescription = $this->_getItemListCompDesc($itemsQtys);
            $discountDescription = $this->_getDiscountCompDesc($this->getDiscountType(), $discountAmount);

            $ruleComprehensiveDescriptionLines[] = "Got ".$itemsListDescription." ".$discountDescription;
        }

        $this->_setRuleApComprehensiveDescriptionLines($this->getRule(), $ruleComprehensiveDescriptionLines, $address);

        return [
            'cart_hint' => $hintMessage
        ];
    }


    public function getFirstNNextMAggregatorName()
    {
        return $this->getAggregatorOption($this->getFirstNNextMAggregator());
    }

    public function getFirstNNextMAggregatorElement()
    {
        if ($this->getFirstNNextMAggregator() === null) {
            foreach (array_keys($this->getAggregatorOption()) as $key) {
                $this->setFirstNNextMAggregator($key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_firstnnextm__' . $this->getId() . '__aggregator',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][firstnnextm][' . $this->getId() . '][aggregator]',
                'values' => $this->getAggregatorSelectOptions(),
                'value' => $this->getFirstNNextMAggregator(),
                'value_name' => $this->getFirstNNextMAggregatorName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getFirstNNextMAggregatorValueName()
    {
        if(isset($this->aggregatorValueOptions[$this->getFirstNNextMAggregatorValue()])){
            return $this->aggregatorValueOptions[$this->getFirstNNextMAggregatorValue()];
        }
        return $this->getFirstNNextMAggregatorValue();
    }

    public function getFirstNNextMAggregatorValueElement()
    {
        if ($this->getFirstNNextMAggregatorValue() === null) {
            foreach (array_keys($this->aggregatorValueOptions) as $key) {
                $this->setFirstNNextMAggregatorValue($key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_firstnnextm__' . $this->getId() . '__aggregator_value',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][firstnnextm][' . $this->getId() . '][aggregator_value]',
                'values' => $this->aggregatorValueOptions,
                'value' => $this->getFirstNNextMAggregatorValue(),
                'value_name' => $this->getFirstNNextMAggregatorValueName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }


    public function getFirstNNextMNewChildElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_firstnnextm__'.$this->getId() . '__new_child',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][firstnnextm][' . $this->getId() . '][new_child]',
                'values' => $this->getNewChildSelectOptions(),
                'value_name' => $this->getNewChildName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Newchild')
            );
    }

    public function getFirstNNextMTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_firstnnextm__' . $this->getId() . '__type',
            'hidden',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][firstnnextm][' . $this->getId() . '][type]',
                'value' => 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine',
                'no_span' => true,
                'class' => 'hidden',
                'data-form-part' => $this->getFormName()
            ]
        );
    }

    public function getFirstNNextMWrapperTypeElement()
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

    public function getDiscountAmountValueElement($seqPartIndex)
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][discount_amount_value' . $seqPartIndex . ']',
            'value' => $this->getData('discount_amount_value'.$seqPartIndex),
            'value_name' => ($this->getData('discount_amount_value'.$seqPartIndex) ? $this->getData('discount_amount_value'.$seqPartIndex) : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__discount_amount_value'.$seqPartIndex,
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getDiscountQtyValueElement($seqPartIndex)
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][discount_qty_value' . $seqPartIndex . ']',
            'value' => $this->getData('discount_qty_value'.$seqPartIndex),
            'value_name' => ($this->getData('discount_qty_value'.$seqPartIndex) ? $this->getData('discount_qty_value'.$seqPartIndex) : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__discount_qty_value'.$seqPartIndex,
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getAfterMQtyElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][after_m_qty]',
            'value' => $this->getAfterMQty(),
            'value_name' => ($this->getAfterMQty() ? $this->getAfterMQty() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__after_m_qty',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
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

    public function asSubActionArray($subActionDetailsKey){
        $out = [];
        if($subActionDetailsKey == 'firstnnextm'){
            $out = $this->getFirstNNextMActionDetails()->asArray();
        }
        return $out;
    }

    public function loadSubActionArray($subActionDetailsKey, $arr, $key = 'action_details'){
        if($subActionDetailsKey == 'firstnnextm'){
            $this->setFirstNNextMAggregator($arr[$key][1]['aggregator']);
            $this->setFirstNNextMAggregatorValue($arr[$key][1]['aggregator_value']);
            $firstNNextMActionDetails = $this->_conditionFactory->create($arr[$key][1]['type']);
            $firstNNextMActionDetails->setRule($this->getRule())
                ->setObject($this->getObject())
                ->setPrefix($this->getPrefix())
                ->setSubPrefix('firstnnextm')
                ->setType('Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine')
                ->setId('1--1');

            $this->setFirstNNextMActionDetails($firstNNextMActionDetails);
            $firstNNextMActionDetails->loadArray($arr[$key][1], $key);
        }
    }

    public function asArray(array $arrAttributes = [])
    {
        //this method shouldn't be used, instead loadSubActionArray should be
    }

    public function getSubActionDetailsKeys(){
        return [
            'firstnnextm'
        ];
    }

    public function getDirectAttributeKeys(){
        $directAttributeKeys = [
            'after_m_qty',
            'hints_singular',
            'hints_plural'
        ];
        for($seqPartIndex = 1; $seqPartIndex <= $this->getNumberOfSeqs(); $seqPartIndex++){
            $directAttributeKeys[] = 'discount_amount_value'.$seqPartIndex;
            $directAttributeKeys[] = 'discount_qty_value'.$seqPartIndex;
        }

        return $directAttributeKeys;
    }
}
