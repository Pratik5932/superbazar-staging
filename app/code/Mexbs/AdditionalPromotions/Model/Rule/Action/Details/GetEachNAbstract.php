<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

abstract class GetEachNAbstract extends \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine{
    abstract public function getDiscountType();
    abstract public function isEachN();
    abstract public function isDiscountPriceTypeApplicable();
    abstract public function isDiscountOrderTypeApplicable();
    abstract public function isLimitApplicable();
    abstract public function isDiscountQtyApplicable();

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

    protected function _getCouponNotValidHintMessage(
        $promoItemNameSingular,
        $promoItemNamePlural,
        $promoItemDiscountAmount,
        $itemQtyToAddToGetDiscount,
        $atLeastOneMatchingProductAdded
    ){
        $discountExpression = $this->_getDiscountExpression($promoItemDiscountAmount);
        $hintMessage = sprintf(
            "Add %s%s %s to cart. Then try applying the coupon again. You should get %s %s %s!",
            (($itemQtyToAddToGetDiscount + 1 == 1) ? "one" : $itemQtyToAddToGetDiscount + 1),
            ($atLeastOneMatchingProductAdded ? " more" : ""),
            (($itemQtyToAddToGetDiscount + 1 == 1) ? $promoItemNameSingular : $promoItemNamePlural),
            (($itemQtyToAddToGetDiscount + 1 == 1) ? "the" : "one"),
            $promoItemNameSingular,
            $discountExpression
        );
        return $hintMessage;
    }

    protected function _getHintMessage(
        $promoItemNameSingular,
        $promoItemNamePlural,
        $promoItemDiscountAmount,
        $alreadyDiscountedItemQty,
        $itemQtyToAddToGetDiscount,
        $itemQtyOneCanGetIfAdds,
        $itemQtyOnCanGetNow,
        $atLeastOneMatchingProductAdded
   ){
        $hintMessage = "";
        $discountExpression = $this->_getDiscountExpression($promoItemDiscountAmount);
        if($alreadyDiscountedItemQty > 0){
            $hintMessage .= sprintf(
                "You've got %s %s %s.",
                ($alreadyDiscountedItemQty == 1 ? "one" : $alreadyDiscountedItemQty),
                ($alreadyDiscountedItemQty == 1 ? $promoItemNameSingular : $promoItemNamePlural),
                ($alreadyDiscountedItemQty == 1 ? $discountExpression : $discountExpression." each")
            );
        }

        if($hintMessage != ""){
            $hintMessage .= " ";
        }

        if($itemQtyToAddToGetDiscount > 0
            && $itemQtyOneCanGetIfAdds > 0){
            if($itemQtyOneCanGetIfAdds < INF){
                $hintMessage .= sprintf(
                    "Add %s%s %s, to get the next %s%s %s!",
                    ($itemQtyToAddToGetDiscount == 1 ? "one" : $itemQtyToAddToGetDiscount),
                    ($atLeastOneMatchingProductAdded ? " more" : ""),
                    ($itemQtyToAddToGetDiscount == 1 ? $promoItemNameSingular : $promoItemNamePlural),
                    ($itemQtyOneCanGetIfAdds == 1 ? "" : $itemQtyOneCanGetIfAdds." "),
                    ($itemQtyOneCanGetIfAdds == 1 ? $promoItemNameSingular : $promoItemNamePlural),
                    ($itemQtyOneCanGetIfAdds == 1 ? $discountExpression : $discountExpression." each")
                );
            }else{
                $hintMessage .= sprintf(
                    "Add %s%s %s, to get all of the next %s %s!",
                    ($itemQtyToAddToGetDiscount == 1 ? "one" : $itemQtyToAddToGetDiscount),
                    ($atLeastOneMatchingProductAdded ? " more" : ""),
                    ($itemQtyToAddToGetDiscount == 1 ? $promoItemNameSingular : $promoItemNamePlural),
                    $promoItemNamePlural,
                    $discountExpression." each"
                );
            }
        }elseif($itemQtyOnCanGetNow > 0){
            if($itemQtyOnCanGetNow < INF){
                $hintMessage .= sprintf(
                    "You can now add another %s%s %s!",
                    ($itemQtyOnCanGetNow == 1 ? "" : $itemQtyOnCanGetNow." "),
                    ($itemQtyOnCanGetNow == 1 ? $promoItemNameSingular : $promoItemNamePlural),
                    ($itemQtyOnCanGetNow == 1 ? $discountExpression : $discountExpression." each")
                );
            }else{
                if($alreadyDiscountedItemQty > 0){
                    $hintMessage .= sprintf(
                        "Add more %s %s!",
                        $promoItemNamePlural,
                        $discountExpression." each"
                    );
                }else{
                    $hintMessage .= sprintf(
                        "You can now add %s %s!",
                        $promoItemNamePlural,
                        $discountExpression." each"
                    );
                }
            }
        }

        return $hintMessage;
    }

    public function markMatchingItemsAndGetHint($items, $address){
        if(!$this->getEachNActionDetails()
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

        $discountAmount = $this->getDiscountAmountValue();
        if(!$this->_getIsDiscountAmountValid($discountAmount, $discountType)){
            return null;
        }

        $eachNActionDetails = $this->getEachNActionDetails()->getActionDetails();
        if(!isset($eachNActionDetails[0])){
            return null;
        }
        $eachNActionDetail = $this->getEachNActionDetails();

        if(!$this->isEachN()){
            $nNumber = 0;
            $mNumber = 1;
        }else{
            $nNumber = $this->getNNumber();
            if(!is_numeric($nNumber)
                || !$nNumber){
                return null;
            }

            $mNumber = $this->getMNumber();
            if(!is_numeric($mNumber)
                || !$mNumber){
                return null;
            }
        }

        $afterMQty = 0;
        if(
            is_numeric($this->getAfterMQty())
            && $this->getAfterMQty()
            ){
            $afterMQty = $this->getAfterMQty();
        }


        $maxQty = INF;
        if($this->isDiscountQtyApplicable()){
            if(is_numeric($this->getRule()->getDiscountQty())
                && ($this->getRule()->getDiscountQty() > 0)
            ){
                $maxQty = $this->getRule()->getDiscountQty();
            }
        }elseif($this->isLimitApplicable()){
            if(is_numeric($this->getEachNLimit())
                && ($this->getEachNLimit() > 0)
            ){
                $maxQty = $this->getEachNLimit();
            }
        }


        $validEachNPriceToItem = [];
        $validEachNItemsTotalQty = 0;
        foreach($items as $item){
            $itemProductToCheckTierOrSpecialPrice = $this->_getItemToCheckTierOrSpecialPrice($item);
            if($this->_oneOfPriceTypesAppliedAndShouldSkip($item, $itemProductToCheckTierOrSpecialPrice, $this->getRule())){
                continue;
            }
            if($this->apHelper->validateActionDetail($eachNActionDetail, $item)){
                $itemPrice = $this->apHelper->getItemPrice($item);
                $extendedArraySortableKey = strval($itemPrice*10000 + rand(0,9999));
                $validEachNPriceToItem[$extendedArraySortableKey] = $item;
                $validEachNItemsTotalQty += $item->getQty();
            }
        }

        $NHintsSingular = $this->getNHintsSingular();
        $NHintsPlural = $this->getNHintsPlural();

        $hintMessage = null;
        $hintCouponNotValidMessage = null;

        $addCartHints = (
            $this->getRule()->getDisplayCartHints()
            && $NHintsSingular
            && $NHintsPlural
        );

        if($validEachNItemsTotalQty <= $afterMQty
            || ($validEachNItemsTotalQty == 0)){
            $qtyToAddUntilDiscount = $afterMQty - $validEachNItemsTotalQty + $nNumber;

            if($addCartHints){
                $itemQtyOneCanGetIfAdds = ($qtyToAddUntilDiscount > 0 ? ($this->isEachN() ? $mNumber : INF) : 0);
                $itemQtyOnCanGetNow = ($qtyToAddUntilDiscount > 0 ? 0 : ($this->isEachN() ? $mNumber : INF));

                $hintMessage = $this->_getHintMessage(
                    $NHintsSingular,
                    $NHintsPlural,
                    $discountAmount,
                    0,
                    $qtyToAddUntilDiscount,
                    $itemQtyOneCanGetIfAdds,
                    $itemQtyOnCanGetNow,
                    ($validEachNItemsTotalQty > 0)
                );

                if($this->getRule()->getDisplayCartHintsIfCouponInvalid()){
                    $hintCouponNotValidMessage = $this->_getCouponNotValidHintMessage(
                        $NHintsSingular,
                        $NHintsPlural,
                        $discountAmount,
                        $qtyToAddUntilDiscount,
                        ($validEachNItemsTotalQty > 0)
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
                ksort($validEachNPriceToItem);
            }elseif($this->getDiscountPriceType() == self::DISCOUNT_PRICE_TYPE_MOST_EXPENSIVE){
                krsort($validEachNPriceToItem);
            }
        }elseif($this->isDiscountOrderTypeApplicable()){
            if($this->getRule()->getDiscountOrderType() == self::DISCOUNT_PRICE_TYPE_CHEAPEST ){
                ksort($validEachNPriceToItem);
            }elseif($this->getRule()->getDiscountOrderType() == self::DISCOUNT_PRICE_TYPE_MOST_EXPENSIVE){
                krsort($validEachNPriceToItem);
            }
        }


        $maxDiscountAmount = 0;
        if(is_numeric($this->getRule()->getMaxDiscountAmount())
            && ($this->getRule()->getMaxDiscountAmount() > 0)){
            $maxDiscountAmount = $this->getRule()->getMaxDiscountAmount();
        }

        $appliedItemsTotalAmount = 0;
        $appliedItemsTotalQty = 0;
        $fullPricePurchasedQty = 0;

        $eachNItemsQtys = [];

        $affectedProductNames = [];

        $undiscountedRemainderQty = 0;
        $discountedRemainderQty = 0;

        $currentItemIndex = 0;
        $itemsCount = count($validEachNPriceToItem);

        $qtyToAddUntilDiscount = 0;

        $loopExitedBecauseExceededMaxAmount = false;


        foreach($validEachNPriceToItem as $item){
            $currentItemIndex++;

            $itemPrice = $this->apHelper->getItemPrice($item);
            $maxQtyForDiscountOnItem = $item->getQty();

            if($afterMQty){
                $fullPriceQtyOnItem = min(max($afterMQty - $fullPricePurchasedQty, 0), $item->getQty());
                $fullPricePurchasedQty += $fullPriceQtyOnItem;

                if($fullPriceQtyOnItem > 0){
                    $maxQtyForDiscountOnItem = $item->getQty() - $fullPriceQtyOnItem;
                }else{
                    $maxQtyForDiscountOnItem = $item->getQty();
                }
            }

            if($maxQtyForDiscountOnItem <= 0){
                continue;
            }

            $maxQtyForDiscountOnItemAfterBothRemaindersDeduct = $maxQtyForDiscountOnItem;

            $itemQtyDiscountedFromDiscRemainder = 0;
            if($undiscountedRemainderQty < $nNumber){
                $qtyDeductedFromItemForRemainderUndiscounted = min(
                    max($nNumber-$undiscountedRemainderQty, 0),
                    $maxQtyForDiscountOnItem
                );

                $undiscountedRemainderQty += $qtyDeductedFromItemForRemainderUndiscounted;

                $maxQtyForDiscountOnItemAfterBothRemaindersDeduct = $maxQtyForDiscountOnItem - $qtyDeductedFromItemForRemainderUndiscounted;
            }

            if(($undiscountedRemainderQty >= $nNumber)
                &&($discountedRemainderQty < $mNumber)){

                $itemQtyDiscountedFromDiscRemainder = min(
                    $maxQtyForDiscountOnItemAfterBothRemaindersDeduct,
                    max($mNumber - $discountedRemainderQty, 0)
                );

                $discountedRemainderQty += $itemQtyDiscountedFromDiscRemainder;

                $maxQtyForDiscountOnItemAfterBothRemaindersDeduct = $maxQtyForDiscountOnItemAfterBothRemaindersDeduct - $itemQtyDiscountedFromDiscRemainder;
            }

            if(($undiscountedRemainderQty >= $nNumber)
                && ($discountedRemainderQty >= $mNumber)){
                $undiscountedRemainderQty = 0;
                $discountedRemainderQty = 0;
            }

            $itemQtyDiscountedFromCicles = 0;
            $itemQtyDiscountedFromSelfReminder = 0;
            if($maxQtyForDiscountOnItemAfterBothRemaindersDeduct > 0){
                $fullItemQtyCicles = floor($maxQtyForDiscountOnItemAfterBothRemaindersDeduct / ($nNumber + $mNumber));
                $itemQtyCiclesRemainder = $maxQtyForDiscountOnItemAfterBothRemaindersDeduct % ($nNumber + $mNumber);

                $itemQtyDiscountedFromCicles = $fullItemQtyCicles*$mNumber;
                $undiscountedRemainderQty = min(
                    $nNumber,
                    $itemQtyCiclesRemainder
                );


                $itemQtyDiscountedFromSelfReminder = min(
                    $mNumber,
                    max($itemQtyCiclesRemainder-$undiscountedRemainderQty, 0)
                );

                $discountedRemainderQty = $itemQtyDiscountedFromSelfReminder;

                if(($undiscountedRemainderQty >= $nNumber)
                    && ($discountedRemainderQty >= $mNumber)){
                    $undiscountedRemainderQty = 0;
                    $discountedRemainderQty = 0;
                }
            }

            $discountQtyOnItemFromRemaindersAndCycles = $itemQtyDiscountedFromDiscRemainder
                + $itemQtyDiscountedFromCicles
                + $itemQtyDiscountedFromSelfReminder;

            if($maxQty != INF){
                $itemsTotalQtyLeftToApply = max($maxQty-$appliedItemsTotalQty, 0);
                $qtyToApplyOnItemWithinQtyLimit = min($discountQtyOnItemFromRemaindersAndCycles, $itemsTotalQtyLeftToApply);
            }else{
                $qtyToApplyOnItemWithinQtyLimit = $discountQtyOnItemFromRemaindersAndCycles;
            }

            if($this->getDiscountType() == self::DISCOUNT_TYPE_FIXED_PRICE){
                $discountPercentInDecimal = max(1-($discountAmount/$itemPrice), 0);
            }elseif($this->getDiscountType() == self::DISCOUNT_TYPE_FIXED){
                $discountPercentInDecimal = $discountAmount/$itemPrice;
            }elseif($this->getDiscountType() == self::DISCOUNT_TYPE_PERCENT){
                $discountPercentInDecimal = $discountAmount/100;
            }else{
                $discountPercentInDecimal = 0; //@TODO - log error
            }

            if($maxDiscountAmount > 0){
                if(($itemPrice * $discountPercentInDecimal) == 0){
                    $qtyToApplyOnItemWithinAmountLimit = $qtyToApplyOnItemWithinQtyLimit;
                }else{
                    $qtyToApplyOnItemWithinAmountLimit = floor(max($maxDiscountAmount - $appliedItemsTotalAmount, 0) / ($itemPrice * $discountPercentInDecimal));
                }

                $qtyToApplyOnItem = min($qtyToApplyOnItemWithinQtyLimit, $qtyToApplyOnItemWithinAmountLimit);

                if($qtyToApplyOnItemWithinQtyLimit > $qtyToApplyOnItemWithinAmountLimit){
                    $loopExitedBecauseExceededMaxAmount = true;
                }
            }else{
                $qtyToApplyOnItem = $qtyToApplyOnItemWithinQtyLimit;
            }

            if($currentItemIndex == $itemsCount){
                if($discountedRemainderQty == 0){
                    $qtyToAddUntilDiscount = $nNumber-$undiscountedRemainderQty;
                }
            }

            if($qtyToApplyOnItem <= 0){
                continue;
            }

            $appliedItemsTotalQty += $qtyToApplyOnItem;

            $discountAmountOnItemUnit = $this->_getDiscountAmountOnItemUnit($itemPrice, $this->getDiscountType(), $discountAmount);
            $appliedItemsTotalAmount += $discountAmountOnItemUnit*$qtyToApplyOnItem;

            $itemApRuleMatches = $this->apHelper->getApRuleMatchesForItem($item);
            $itemApRuleMatches = (is_array($itemApRuleMatches) ? $itemApRuleMatches : []);

            $itemExpectedPricesArray = $this->_getItemExpectedPricesArray($item);
            $itemApRuleMatches[$this->getRule()->getId()]['apply'] = [
                'qty' => $qtyToApplyOnItem,
                'expected_prices' => $itemExpectedPricesArray
            ];


            $item->setApRuleMatches($itemApRuleMatches);

            $affectedProductNames[] = $item->getName();

            $eachNItemsQtys[] = [
                'qty' => $qtyToApplyOnItem,
                'item' => $item
            ];
        }

        $hideHintsAfterDiscountNumber = $this->getRule()->getHideHintsAfterDiscountNumber();

        $addCartHints = $addCartHints
            && (!$loopExitedBecauseExceededMaxAmount)
            && ($appliedItemsTotalQty < $maxQty)
            && ($hideHintsAfterDiscountNumber == 0 || ($appliedItemsTotalQty < $hideHintsAfterDiscountNumber));

        if($addCartHints){
            $itemQtyOneCanGetIfAdds = ($qtyToAddUntilDiscount > 0 ? ($this->isEachN() ? $mNumber : INF) : 0);
            $itemQtyOnCanGetNow = ($qtyToAddUntilDiscount > 0 ? 0 : ($this->isEachN() ? $mNumber-($appliedItemsTotalQty % $mNumber) : INF));
            $hintMessage = $this->_getHintMessage(
                $NHintsSingular,
                $NHintsPlural,
                $discountAmount,
                $appliedItemsTotalQty,
                $qtyToAddUntilDiscount,
                $itemQtyOneCanGetIfAdds,
                $itemQtyOnCanGetNow,
                true
            );

            if($this->getRule()->getDisplayCartHintsIfCouponInvalid()
                && ($appliedItemsTotalQty == 0)){
                $hintCouponNotValidMessage = $this->_getCouponNotValidHintMessage(
                    $NHintsSingular,
                    $NHintsPlural,
                    $discountAmount,
                    $qtyToAddUntilDiscount,
                    true
                );
            }
        }

        $itemsListEachNDescription = $this->_getItemListCompDesc($eachNItemsQtys);
        $discountDescription = $this->_getDiscountCompDesc($this->getDiscountType(), $this->getDiscountAmountValue());

        $ruleComprehensiveDescriptionLines = ["Got ".$itemsListEachNDescription." ".$discountDescription];

        $this->_setRuleApComprehensiveDescriptionLines($this->getRule(), $ruleComprehensiveDescriptionLines, $address);

        return [
            'cart_hint' => $hintMessage,
            'coupon_not_valid_cart_hint' => $hintCouponNotValidMessage
        ];
    }


    public function getEachNAggregatorName()
    {
        return $this->getAggregatorOption($this->getEachNAggregator());
    }

    public function getEachNAggregatorElement()
    {
        if ($this->getEachNAggregator() === null) {
            foreach (array_keys($this->getAggregatorOption()) as $key) {
                $this->setEachNAggregator($key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_eachn__' . $this->getId() . '__aggregator',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][eachn][' . $this->getId() . '][aggregator]',
                'values' => $this->getAggregatorSelectOptions(),
                'value' => $this->getEachNAggregator(),
                'value_name' => $this->getEachNAggregatorName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getEachNAggregatorValueName()
    {
        if(isset($this->aggregatorValueOptions[$this->getEachNAggregatorValue()])){
            return $this->aggregatorValueOptions[$this->getEachNAggregatorValue()];
        }
        return $this->getEachNAggregatorValue();
    }

    public function getEachNAggregatorValueElement()
    {
        if ($this->getEachNAggregatorValue() === null) {
            foreach (array_keys($this->aggregatorValueOptions) as $key) {
                $this->setEachNAggregatorValue($key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_eachn__' . $this->getId() . '__aggregator_value',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][eachn][' . $this->getId() . '][aggregator_value]',
                'values' => $this->aggregatorValueOptions,
                'value' => $this->getEachNAggregatorValue(),
                'value_name' => $this->getEachNAggregatorValueName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getNNumberElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][n_number]',
            'value' => $this->getNNumber(),
            'value_name' => ($this->getNNumber() ? $this->getNNumber() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__n_number',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getNHintsSingularElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][n_hints_singular]',
            'value' => $this->getNHintsSingular(),
            'value_name' => ($this->getNHintsSingular() ? $this->getNHintsSingular() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__n_hints_singular',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getNHintsPluralElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][n_hints_plural]',
            'value' => $this->getNHintsPlural(),
            'value_name' => ($this->getNHintsPlural() ? $this->getNHintsPlural() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__n_hints_plural',
            'text',
            $elementParams
        )->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    public function getMNumberElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][m_number]',
            'value' => $this->getMNumber(),
            'value_name' => ($this->getMNumber() ? $this->getMNumber() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__m_number',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getEachNLimitElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][each_n_limit]',
            'value' => $this->getEachNLimit(),
            'value_name' => ($this->getEachNLimit() ? $this->getEachNLimit() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__each_n_limit',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getEachNNewChildElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_eachn__'.$this->getId() . '__new_child',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][eachn][' . $this->getId() . '][new_child]',
                'values' => $this->getNewChildSelectOptions(),
                'value_name' => $this->getNewChildName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Newchild')
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

    public function getUpToNNumberElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][up_to_n_number]',
            'value' => $this->getUpToNNumber(),
            'value_name' => ($this->getUpToNNumber() ? $this->getUpToNNumber() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__up_to_n_number',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getEachNTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_eachn__' . $this->getId() . '__type',
            'hidden',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][eachn][' . $this->getId() . '][type]',
                'value' => 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine',
                'no_span' => true,
                'class' => 'hidden',
                'data-form-part' => $this->getFormName()
            ]
        );
    }

    public function getEachNWrapperTypeElement()
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

    public function asSubActionArray($subActionDetailsKey){
        $out = [];
        if($subActionDetailsKey == 'eachn'){
            $out = $this->getEachNActionDetails()->asArray();
        }
        return $out;
    }

    public function loadSubActionArray($subActionDetailsKey, $arr, $key = 'action_details'){
        if($subActionDetailsKey == 'eachn'){
            $this->setEachNAggregator($arr[$key][1]['aggregator']);
            $this->setEachNAggregatorValue($arr[$key][1]['aggregator_value']);
            $eachNActionDetails = $this->_conditionFactory->create($arr[$key][1]['type']);
            $eachNActionDetails->setRule($this->getRule())
                ->setObject($this->getObject())
                ->setPrefix($this->getPrefix())
                ->setSubPrefix('eachn')
                ->setType('Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine')
                ->setId('1--1');

            $this->setEachNActionDetails($eachNActionDetails);
            $eachNActionDetails->loadArray($arr[$key][1], $key);
        }
    }

    public function asArray(array $arrAttributes = [])
    {
        //this method shouldn't be used, instead loadSubActionArray should be
    }

    public function getSubActionDetailsKeys(){
        return [
            'eachn'
        ];
    }
}
