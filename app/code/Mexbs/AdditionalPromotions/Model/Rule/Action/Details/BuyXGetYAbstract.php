<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

abstract class BuyXGetYAbstract extends ProductsSetAbstract{
    const MAX_SETS_NUMBER = 5;

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


    protected function _getHintMessage(
        $itemsAlreadyDiscountedData,
        $itemsToAddTillDiscountData,
        $itemYouCanGetDiscountedIfYouAddData,
        $itemYouCanGetDiscountedNowData
    )
    {
        $hintMessage = "";

        if(!empty($itemsAlreadyDiscountedData)){
            $hintMessage .= "You've got ";

            $index = 0;
            foreach($itemsAlreadyDiscountedData as $itemAlreadyDiscountedData){
                $discountExpression = $this->_getDiscountExpression($itemAlreadyDiscountedData['discount_amount']);
                if(count($itemsAlreadyDiscountedData) > 1){
                    if($index == count($itemsAlreadyDiscountedData) - 1){
                        $hintMessage .= " and ";
                    }elseif($index != 0){
                        $hintMessage .= ", ";
                    }
                }
                $hintMessage .= sprintf(
                    "%s %s %s",
                    ($itemAlreadyDiscountedData['qty'] == 1 ? "one" : $itemAlreadyDiscountedData['qty']),
                    ($itemAlreadyDiscountedData['qty'] == 1 ? $itemAlreadyDiscountedData['hints_singular'] : $itemAlreadyDiscountedData['hints_plural']),
                    (($itemAlreadyDiscountedData['qty'] == 1) || ($this->getDiscountType() == self::DISCOUNT_TYPE_PERCENT) ? $discountExpression : $discountExpression." each")
                );
                $index++;
            }
            $hintMessage .= ".";
        }

        if($hintMessage != ""){
            $hintMessage .= " ";
        }

        if(!empty($itemsToAddTillDiscountData)
            && !empty($itemYouCanGetDiscountedIfYouAddData)){
            $hintMessage .= "Add ";
            $index = 0;
            foreach($itemsToAddTillDiscountData as $itemToAddTillDiscountData){
                if(count($itemsToAddTillDiscountData) > 1){
                    if($index == count($itemsToAddTillDiscountData) - 1){
                        $hintMessage .= " and ";
                    }elseif($index != 0){
                        $hintMessage .= ", ";
                    }
                }

                $hintMessage .= sprintf(
                    "%s%s %s",
                    ($itemToAddTillDiscountData['qty'] == 1 ? "one" : $itemToAddTillDiscountData['qty']),
                    ($itemToAddTillDiscountData['not_added_yet'] ? "" : " more"),
                    ($itemToAddTillDiscountData['qty'] == 1 ? $itemToAddTillDiscountData['hints_singular'] : $itemToAddTillDiscountData['hints_plural'])
                );
                $index++;
            }
            if(!empty($itemsAlreadyDiscountedData)){
                $hintMessage .= ", to get your next ";
            }else{
                $hintMessage .= ", to get  ";
            }

            $discountExpression = $this->_getDiscountExpression($itemYouCanGetDiscountedIfYouAddData['discount_amount']);
            if(!empty($itemsAlreadyDiscountedData)){
                $hintMessage .= sprintf(
                    "%s%s %s",
                    ($itemYouCanGetDiscountedIfYouAddData['qty'] == 1 ? "" : $itemYouCanGetDiscountedIfYouAddData['qty']." "),
                    ($itemYouCanGetDiscountedIfYouAddData['qty'] == 1 ? $itemYouCanGetDiscountedIfYouAddData['hints_singular'] : $itemYouCanGetDiscountedIfYouAddData['hints_plural']),
                    ($itemYouCanGetDiscountedIfYouAddData['qty'] == 1 ? $discountExpression : $discountExpression." each")
                );
            }else{
                $hintMessage .= sprintf(
                    "%s %s %s",
                    ($itemYouCanGetDiscountedIfYouAddData['qty'] == 1 ? "one" : $itemYouCanGetDiscountedIfYouAddData['qty']),
                    ($itemYouCanGetDiscountedIfYouAddData['qty'] == 1 ? $itemYouCanGetDiscountedIfYouAddData['hints_singular'] : $itemYouCanGetDiscountedIfYouAddData['hints_plural']),
                    (($itemYouCanGetDiscountedIfYouAddData['qty'] == 1) || ($this->getDiscountType() == self::DISCOUNT_TYPE_PERCENT) ? $discountExpression : $discountExpression." each")
                );
            }

            $hintMessage .= "!";
        }elseif(!empty($itemYouCanGetDiscountedNowData)){
            $hintMessage .= "You can now add ";
            if(!empty($itemsAlreadyDiscountedData)){
                $hintMessage .= "another ";
            }
            $discountExpression = $this->_getDiscountExpression($itemYouCanGetDiscountedNowData['discount_amount']);

            $hintMessage .= sprintf(
                "%s%s %s",
                ($itemYouCanGetDiscountedNowData['qty'] == 1 ? (empty($itemsAlreadyDiscountedData) ? "one ": "") : $itemYouCanGetDiscountedNowData['qty']." "),
                ($itemYouCanGetDiscountedNowData['qty'] == 1 ? $itemYouCanGetDiscountedNowData['hints_singular'] : $itemYouCanGetDiscountedNowData['hints_plural']),
                (($itemYouCanGetDiscountedNowData['qty'] == 1) || ($this->getDiscountType() == self::DISCOUNT_TYPE_PERCENT) ? $discountExpression : $discountExpression." each")
            );
            $hintMessage .= "!";
        }
        return $hintMessage;
    }

    protected function _setQtyOfItem(
        $hintSingular,
        $hintPlural,
        $qtyToSet,
        $itemsArray
    ){
        foreach($itemsArray as $index => $item){
            if(($item['hints_singular'] == $hintSingular)
                && ($item['hints_plural'] == $hintPlural)){
                if($qtyToSet == 0){
                    unset($itemsArray[$index]);
                }else{
                    $itemsArray[$index]['qty'] = $qtyToSet;
                }
            }
        }
        return $itemsArray;
    }

    protected function _getCouponNotValidHintMessage(
        $itemsToAddTillDiscountData,
        $itemYouCanGetDiscountedIfYouAddData,
        $itemYouCanGetDiscountedNowData,
        $qtyAddedOfItemYouCanGet
    )
    {
        $hintMessage = "";


        if(!empty($itemsToAddTillDiscountData)
            && !empty($itemYouCanGetDiscountedIfYouAddData)){
            $qtyYouCanGetLeftToAdd = max(0, $itemYouCanGetDiscountedIfYouAddData['qty'] - $qtyAddedOfItemYouCanGet);

            $itemsToAddToMakeDiscountApplicable = array_merge($itemsToAddTillDiscountData, [$itemYouCanGetDiscountedIfYouAddData]);
            $itemsToAddToMakeDiscountApplicable = $this->_setQtyOfItem(
                $itemYouCanGetDiscountedIfYouAddData['hints_singular'],
                $itemYouCanGetDiscountedIfYouAddData['hints_plural'],
                $qtyYouCanGetLeftToAdd,
                $itemsToAddToMakeDiscountApplicable
            );
        }elseif(!empty($itemYouCanGetDiscountedNowData)){
            $qtyYouCanGetLeftToAdd = $itemYouCanGetDiscountedNowData['qty'];
            $itemsToAddToMakeDiscountApplicable = [$itemYouCanGetDiscountedNowData];
        }

        if(isset($itemsToAddToMakeDiscountApplicable)
            && count($itemsToAddToMakeDiscountApplicable)
            && (count($itemYouCanGetDiscountedIfYouAddData) || count($itemYouCanGetDiscountedNowData))){
            $hintMessage = "Add ";
            $index = 0;
            foreach($itemsToAddToMakeDiscountApplicable as $itemToAddToMakeDiscountApplicable){
                if(count($itemsToAddToMakeDiscountApplicable) > 1){
                    if($index == count($itemsToAddToMakeDiscountApplicable) - 1){
                        $hintMessage .= " and ";
                    }elseif($index != 0){
                        $hintMessage .= ", ";
                    }
                }
                $hintMessage .= sprintf(
                    "%s%s %s",
                    ($itemToAddToMakeDiscountApplicable['qty'] == 1 ? "one" : $itemToAddToMakeDiscountApplicable['qty']),
                    (!isset($itemToAddToMakeDiscountApplicable['not_added_yet']) || $itemToAddToMakeDiscountApplicable['not_added_yet'] ? "" : " more"),
                    ($itemToAddToMakeDiscountApplicable['qty'] == 1 ? $itemToAddToMakeDiscountApplicable['hints_singular'] : $itemToAddToMakeDiscountApplicable['hints_plural'])
                );
                $index++;
            }
            $hintMessage .= " to cart. Then try applying the coupon again. You should get ";

            if(count($itemYouCanGetDiscountedIfYouAddData)){
                $discountExpression = $this->_getDiscountExpression($itemYouCanGetDiscountedIfYouAddData['discount_amount']);
                $hintMessage .= sprintf(
                    "%s %s %s",
                    (($qtyYouCanGetLeftToAdd == $itemYouCanGetDiscountedIfYouAddData['qty'])
                        ? "the"
                        : ($itemYouCanGetDiscountedIfYouAddData['qty'] == 1 ? "one" : $itemYouCanGetDiscountedIfYouAddData['qty'])),
                    ($itemYouCanGetDiscountedIfYouAddData['qty'] == 1 ? $itemYouCanGetDiscountedIfYouAddData['hints_singular'] : $itemYouCanGetDiscountedIfYouAddData['hints_plural']),
                    (($itemYouCanGetDiscountedIfYouAddData['qty'] == 1) || ($this->getDiscountType() == self::DISCOUNT_TYPE_PERCENT) ? $discountExpression : $discountExpression." each")
                );
            }elseif(count($itemYouCanGetDiscountedNowData)){
                    $discountExpression = $this->_getDiscountExpression($itemYouCanGetDiscountedNowData['discount_amount']);
                    $hintMessage .= sprintf(
                        "the %s %s",
                        ($itemYouCanGetDiscountedNowData['qty'] == 1 ? $itemYouCanGetDiscountedNowData['hints_singular'] : $itemYouCanGetDiscountedNowData['hints_plural']),
                        (($itemYouCanGetDiscountedNowData['qty'] == 1) || ($this->getDiscountType() == self::DISCOUNT_TYPE_PERCENT) ? $discountExpression : $discountExpression." each")
                    );
            }
            $hintMessage .= "!";
        }


        return $hintMessage;
    }

    public function markMatchingItemsAndGetHint($items, $address){
        if(!$this->getGetYActionDetails()
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

        $getYActionDetails = $this->getGetYActionDetails()->getActionDetails();
        if(!isset($getYActionDetails[0])){
            return null;
        }
        $getYActionDetail = $this->getGetYActionDetails();

        $getYQty = $this->getGetYQty();
        if(!is_numeric($getYQty)
                || !$getYQty){
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
        krsort($priceToItem);

        $maxDiscountAmount = 0;
        if(is_numeric($this->getRule()->getMaxDiscountAmount())
            && ($this->getRule()->getMaxDiscountAmount() > 0)){
            $maxDiscountAmount = $this->getRule()->getMaxDiscountAmount();
        }

        $yHintsSingular = $this->getGetYHintsSingular();
        $yHintsPlural = $this->getGetYHintsPlural();

        $addCartHints =
            $this->getRule()->getDisplayCartHints()
            && $allOfDefinedSetsHasCartHints
            && $yHintsSingular
            && $yHintsPlural;

        $hintMessage = null;
        $hintCouponNotValidMessage = null;
        $validRuleSetPartsIndexes = $this->_getValidSetPartIndexes();
        $cleanAndFullSets = $this->_calcCleanAndFullSets($priceToItem, $validRuleSetPartsIndexes);
        $fullSetParts = $cleanAndFullSets['full_set_parts'];
        $cleanSets = $cleanAndFullSets['clean_sets'];

        $boughtXQty = count($cleanSets);

        $validGetYQtyWithoutLimit = $boughtXQty*$getYQty;
        $validGetYQty = $validGetYQtyWithoutLimit;

        $maxDiscountQty = $this->getRule()->getDiscountQty();

        if(is_numeric($maxDiscountQty)
            && $maxDiscountQty){
            $validGetYQty = min($validGetYQtyWithoutLimit, intval($maxDiscountQty));
        }


        $validGetYPriceToItem = [];
        foreach($priceToItem as $item){
            if($this->apHelper->validateActionDetail($getYActionDetail, $item)){
                $itemPrice = $this->apHelper->getItemPrice($item);
                $extendedArraySortableKey = strval($itemPrice*10000 + rand(0,9999));
                $validGetYPriceToItem[$extendedArraySortableKey] = $item;
            }
        }

        if($this->getDiscountPriceType() == self::DISCOUNT_PRICE_TYPE_CHEAPEST ){
            ksort($validGetYPriceToItem);
        }elseif($this->getDiscountPriceType() == self::DISCOUNT_PRICE_TYPE_MOST_EXPENSIVE){
            krsort($validGetYPriceToItem);
        }

        $validGetYPriceToItemKeys = array_keys($validGetYPriceToItem);
        $validGetYItemsCount = count($validGetYPriceToItem);

        $keyIndex = 0;
        $getYMatches = [];
        $getYSetsToMatches = [];
        $totalDiscountAmount = 0;
        $remainingGetYQtyPerSetMultiply = $getYQty;

        $totalDiscountedGetYQty = 0;

        foreach($cleanSets as $setIndex => $set){
            $remainingGetYQtyPerSetMultiply = $getYQty;
            if((($setIndex + 1) * $getYQty) > $validGetYQty){
                break;
            }

            $setsBuyXItemsQtys = $set['items'];

            while($keyIndex < count($validGetYPriceToItemKeys)){
                if($remainingGetYQtyPerSetMultiply <= 0){
                    break;
                }

                if(($maxDiscountAmount != 0)
                    && ($totalDiscountAmount >= $maxDiscountAmount)){
                    break 2;
                }

                $itemIndex = $validGetYPriceToItemKeys[$keyIndex];
                $item = $validGetYPriceToItem[$itemIndex];


                $itemAvailableQty = $item->getQty();
                if(isset($getYMatches[$item->getId()]['qty'])){
                    $itemAvailableQty -= $getYMatches[$item->getId()]['qty'];
                }
                if($itemAvailableQty <= 0){
                    $keyIndex++;
                    continue;
                }

                $itemGetYQty = min($itemAvailableQty, $remainingGetYQtyPerSetMultiply);

                $totalDiscountedGetYQty += $itemGetYQty;

                $itemApRuleMatches = $this->apHelper->getApRuleMatchesForItem($item);
                $itemApRuleMatches = (is_array($itemApRuleMatches) ? $itemApRuleMatches : []);

                $itemPrice = $this->apHelper->getItemPrice($item);
                $discountAmountOnItemUnit = $this->_getDiscountAmountOnItemUnit($itemPrice, $this->getDiscountType(), $discountAmount);
                $totalDiscountAmount += $discountAmountOnItemUnit*$itemGetYQty;


                $itemUsedBeforeGetYQty = 0;
                if(array_key_exists($item->getId(), $getYMatches)){
                    $itemUsedBeforeGetYQty = $getYMatches[$item->getId()]['qty'];
                }

                $getYMatches[$item->getId()] = [
                    'item' => $item,
                    'qty' => ($itemUsedBeforeGetYQty + $itemGetYQty)
                ];

                if(!isset($getYSetsToMatches[$setIndex])){
                    $getYSetsToMatches[$setIndex] = [
                        'buy_x_items_qtys' => $setsBuyXItemsQtys,
                        'items' => []
                    ];
                }
                $getYSetsToMatches[$setIndex]['items'][$item->getId()] = [
                    'item' => $item,
                    'qty' => $itemGetYQty
                ];

                $itemExpectedPricesArray = $this->_getItemExpectedPricesArray($item);
                $itemApRuleMatches[$this->getRule()->getId()]['apply'] = [
                    'qty' => ($itemUsedBeforeGetYQty + $itemGetYQty),
                    'expected_prices' => $itemExpectedPricesArray
                ];

                $item->setApRuleMatches($itemApRuleMatches);

                $remainingGetYQtyPerSetMultiply -= $itemGetYQty;
            }
        }

        $smallestCleanSetGroupIndex =
            min(
                $this->_getSmallestCleanSetGroupIndex($fullSetParts, $validRuleSetPartsIndexes),
                floor($totalDiscountedGetYQty / $getYQty) - 1);


        $itemsQtysLeftToAddUntilDiscount = [];
        if($smallestCleanSetGroupIndex != INF){
            $itemsQtysLeftToAddUntilDiscount = $this->_getItemsQtysToCompleteToCleanByIndex(
                $fullSetParts,
                $smallestCleanSetGroupIndex,
                $validRuleSetPartsIndexes
            );
            $itemsQtysLeftToAddUntilDiscount = $this->_getItemsNamesQtysWithoutZeroQtys($itemsQtysLeftToAddUntilDiscount);
        }


        $getYItemQtyYouCanGetDiscountedIfYouAddData = (!empty($itemsQtysLeftToAddUntilDiscount) ? $getYQty : 0);
        $getYItemQtyYouCanGetNow = (empty($itemsQtysLeftToAddUntilDiscount) ? ($validGetYQty - $totalDiscountedGetYQty) : 0);

        $hideHintsAfterDiscountNumber = $this->getRule()->getHideHintsAfterDiscountNumber();

        $addCartHints = $addCartHints
            && ((($getYItemQtyYouCanGetDiscountedIfYouAddData > 0) || ($getYItemQtyYouCanGetNow > 0))
            && ($maxDiscountAmount == 0 || ($totalDiscountAmount < $maxDiscountAmount)))
            && ($maxDiscountQty == 0 || ($totalDiscountedGetYQty < $maxDiscountQty))
            && ($hideHintsAfterDiscountNumber == 0 || ($totalDiscountedGetYQty < $hideHintsAfterDiscountNumber));


        if($addCartHints){

            $hintMessage = $this->_getHintMessage(
                (
                    $totalDiscountedGetYQty > 0
                    ? [
                        [
                        'hints_singular' => $yHintsSingular,
                        'hints_plural' => $yHintsPlural,
                        'qty' => $totalDiscountedGetYQty,
                        'discount_amount' => $discountAmount
                        ]
                    ]
                    : []
                ),
                $itemsQtysLeftToAddUntilDiscount,
                (
                    $getYItemQtyYouCanGetDiscountedIfYouAddData > 0
                    ?
                    [
                        'hints_singular' => $yHintsSingular,
                        'hints_plural' => $yHintsPlural,
                        'qty' => $getYItemQtyYouCanGetDiscountedIfYouAddData,
                        'discount_amount' => $discountAmount
                    ]

                    : []
                ),
                (
                    $getYItemQtyYouCanGetNow > 0
                    ?
                    [
                        'hints_singular' => $yHintsSingular,
                        'hints_plural' => $yHintsPlural,
                        'qty' => $getYItemQtyYouCanGetNow,
                        'discount_amount' => $discountAmount
                    ]

                    : []
                )
            );

            if($this->getRule()->getDisplayCartHintsIfCouponInvalid()
                && ($totalDiscountedGetYQty == 0)
            ){
                $hintCouponNotValidMessage = $this->_getCouponNotValidHintMessage(
                    $itemsQtysLeftToAddUntilDiscount,
                    (
                    $getYItemQtyYouCanGetDiscountedIfYouAddData > 0
                        ?
                        [
                            'hints_singular' => $yHintsSingular,
                            'hints_plural' => $yHintsPlural,
                            'qty' => $getYItemQtyYouCanGetDiscountedIfYouAddData,
                            'discount_amount' => $discountAmount
                        ]
                        : []
                    ),
                    (
                    $getYItemQtyYouCanGetNow > 0
                        ?
                        [
                            'hints_singular' => $yHintsSingular,
                            'hints_plural' => $yHintsPlural,
                            'qty' => $getYItemQtyYouCanGetNow,
                            'discount_amount' => $discountAmount
                        ]
                        : []
                    ),
                    $validGetYItemsCount
                );
            }
        }

        if(empty($cleanSets)){
            return [
                'cart_hint' => $hintMessage,
                'coupon_not_valid_cart_hint' => $hintCouponNotValidMessage
            ];
        }


        $setComprehensiveDescriptionLines = [];
        $discountDescription = $this->_getDiscountCompDesc($this->getDiscountType(), $discountAmount);

        foreach($getYSetsToMatches as $setIndex => $setData){
            $setComprehensiveDescriptionLine = "Got ";

            $itemIndex = 0;
            $itemsCount = count($setData['items']);
            foreach($setData['items'] as $itemId => $setItemData){
                $setComprehensiveDescriptionLine .= sprintf(
                    "%s%s",
                    ($setItemData['qty'] > 1 ? $setItemData['qty']." of " : ""),
                    $setItemData['item']->getName()
                );
                if(($itemIndex == ($itemsCount-2))
                    && ($itemsCount > 1)){
                    $setComprehensiveDescriptionLine .= " and ";
                }elseif($itemIndex < ($itemsCount-1)){
                    $setComprehensiveDescriptionLine .= ", ";
                }

                $itemIndex++;
            }

            $itemsListDescription = $this->_getItemListCompDesc($setData['buy_x_items_qtys']);
            $setComprehensiveDescriptionLine .= sprintf(
                " %s for buying %s",
                $discountDescription,
                $itemsListDescription
            );

            $setComprehensiveDescriptionLines[] = $setComprehensiveDescriptionLine;
        }

        $setComprehensiveDescriptionLinesToOccurrences = [];
        foreach($setComprehensiveDescriptionLines as $setComprehensiveDescriptionLine){
            if(!isset($setComprehensiveDescriptionLinesToOccurrences[$setComprehensiveDescriptionLine])){
                $setComprehensiveDescriptionLinesToOccurrences[$setComprehensiveDescriptionLine] = 1;
            }else{
                $setComprehensiveDescriptionLinesToOccurrences[$setComprehensiveDescriptionLine] += 1;
            }
        }

        $ruleComprehensiveDescriptionLines = [];

        foreach($setComprehensiveDescriptionLinesToOccurrences as $setComprehensiveDescriptionLine => $occurrences){
            $ruleComprehensiveDescriptionLines[] = (
            $occurrences == 1 ?
                sprintf("%s", $setComprehensiveDescriptionLine) :
                sprintf("(%s) x %s", $setComprehensiveDescriptionLine, $occurrences)
            );
        }

        if(!empty($ruleComprehensiveDescriptionLines)){
            $this->_setRuleApComprehensiveDescriptionLines($this->getRule(), $ruleComprehensiveDescriptionLines, $address);
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

    public function getGetYQtyAttributeElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][get_y_qty]',
            'value' => $this->getGetYQty(),
            'value_name' => ($this->getGetYQty() ? $this->getGetYQty() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__get_y_qty',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getGetYHintsSingularElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][get_y_hints_singular]',
            'value' => $this->getGetYHintsSingular(),
            'value_name' => ($this->getGetYHintsSingular() ? $this->getGetYHintsSingular() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__get_y_hints_singular',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getGetYHintsPluralElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][get_y_hints_plural]',
            'value' => $this->getGetYHintsPlural(),
            'value_name' => ($this->getGetYHintsPlural() ? $this->getGetYHintsPlural() : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__get_y_hints_plural',
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

    public function getGetYAggregatorName()
    {
        return $this->getAggregatorOption($this->getGetYAggregator());
    }


    public function getGetYAggregatorElement()
    {
        if ($this->getGetYAggregator() === null) {
            foreach (array_keys($this->getAggregatorOption()) as $key) {
                $this->setGetYAggregator($key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_gety__' . $this->getId() . '__aggregator',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][gety][' . $this->getId() . '][aggregator]',
                'values' => $this->getAggregatorSelectOptions(),
                'value' => $this->getGetYAggregator(),
                'value_name' => $this->getGetYAggregatorName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    public function getGetYAggregatorValueName()
    {
        if(isset($this->aggregatorValueOptions[$this->getGetYAggregatorValue()])){
            return $this->aggregatorValueOptions[$this->getGetYAggregatorValue()];
        }
        return $this->getGetYAggregatorValue();
    }

    public function getGetYAggregatorValueElement()
    {
        if ($this->getGetYAggregatorValue() === null) {
            foreach (array_keys($this->aggregatorValueOptions) as $key) {
                $this->setGetYAggregatorValue($key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_gety__' . $this->getId() . '__aggregator_value',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][gety][' . $this->getId() . '][aggregator_value]',
                'values' => $this->aggregatorValueOptions,
                'value' => $this->getGetYAggregatorValue(),
                'value_name' => $this->getGetYAggregatorValueName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getGetYNewChildElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_gety__' . $this->getId() . '__new_child',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][gety][' . $this->getId() . '][new_child]',
                'values' => $this->getNewChildSelectOptions(),
                'value_name' => $this->getNewChildName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Newchild')
        );
    }

    public function getBuyXGetYTypeElement()
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

    public function getGetYTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_gety__' . $this->getId() . '__type',
            'hidden',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][gety][' . $this->getId() . '][type]',
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
        }elseif($subActionDetailsKey == 'gety'){
            $this->setGetYAggregator($arr[$key][1]['aggregator']);
            $this->setGetYAggregatorValue($arr[$key][1]['aggregator_value']);
            $getYActionDetails = $this->_conditionFactory->create($arr[$key][1]['type']);
            $getYActionDetails->setRule($this->getRule())
                ->setObject($this->getObject())
                ->setPrefix($this->getPrefix())
                ->setSubPrefix('gety')
                ->setType('Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine')
                ->setId('1--1');
            $this->setGetYActionDetails($getYActionDetails);
            $getYActionDetails->loadArray($arr[$key][1], $key);
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
        }elseif($subActionDetailsKey == 'gety'){
            $out = $this->getGetYActionDetails()->asArray();
        }

        return $out;
    }

    public function getSubActionDetailsKeys(){
        $subActionDetailKeys = [
            'gety'
        ];
        for($setPartIndex = 1; $setPartIndex <= $this->getNumberOfSets(); $setPartIndex++){
            $subActionDetailKeys[] = 'setpart'.$setPartIndex;
        }
        return $subActionDetailKeys;
    }

    public function getDirectAttributeKeys(){
        $directAttributeKeys = [
            'discount_amount_value',
            'discount_price_type',
            'get_y_qty',
            'get_y_hints_singular',
            'get_y_hints_plural',
            'buy_x_qty'
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