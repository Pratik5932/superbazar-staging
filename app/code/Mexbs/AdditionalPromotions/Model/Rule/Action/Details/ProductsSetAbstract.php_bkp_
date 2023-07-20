<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details;

abstract class ProductsSetAbstract extends \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine{
    const MAX_SETS_NUMBER = 5;

    abstract public function getDiscountType();
    abstract public function getNumberOfSets();

    protected function getMaximumAvailableQtyForItem($item){
        $apRuleMatches = $this->apHelper->getApRuleMatchesForItem($item);
        $itemQty = $item->getQty();
        $itemQtyUsedForThisPromo = 0;
        if(empty($apRuleMatches)){
            return $itemQty;
        }
        if(!is_array($apRuleMatches)
            || !isset($apRuleMatches[$this->getRule()->getId()])
            || !isset($apRuleMatches[$this->getRule()->getId()]['apply']['groups'])){
            return $itemQty;
        }

        foreach($apRuleMatches[$this->getRule()->getId()]['apply']['groups'] as $group){
            $itemQtyUsedForThisPromo += $group['qty'];
        }
        return max(0, $itemQty-$itemQtyUsedForThisPromo);
    }

    protected function _getValidSetPartIndexes(){
        $validRuleSetPartsIndexes = [];
        for($setPartIndex = 1; $setPartIndex <= $this->getNumberOfSets(); $setPartIndex++){
            $setPartSize = $this->getData('set_part'.$setPartIndex.'_size');

            if(
                !is_numeric($setPartSize)
                || ($setPartSize<=0)
            ){
                continue;
            }

            $setPartActionDetails = $this->getData('set_part'.$setPartIndex.'_action_details')->getActionDetails();
            if(!isset($setPartActionDetails[0])){
                continue;
            }

            $validRuleSetPartsIndexes[] = $setPartIndex;
        }

        return $validRuleSetPartsIndexes;
    }

    protected function _getSmallestCleanSetGroupIndex($fullSets, $validRuleSetPartsIndexes){
        $smallestCleanSetGroupIndex = INF;
        foreach($validRuleSetPartsIndexes as $validRuleSetPartsIndex){
            if(isset($fullSets[$validRuleSetPartsIndex])){
                $fullSetPartArray = $fullSets[$validRuleSetPartsIndex];
            }else{
                $fullSetPartArray = [];
            }
            $fullSetPartIndex = $validRuleSetPartsIndex;
            $fullSetPartSize = $this->getData('set_part'.$fullSetPartIndex.'_size');

            $fullSetPartArrayCount = count($fullSetPartArray);
            if($fullSetPartArrayCount == 0){
                $smallestCleanSetGroupIndex = -1;
                break;
            }
            $lastGroupOfFullSetPartArrayCount = $fullSetPartArray[$fullSetPartArrayCount-1]['items_qty'];
            if($lastGroupOfFullSetPartArrayCount < $fullSetPartSize){
                $smallestCleanSizeOfCurrentGroupSet = $fullSetPartArrayCount - 2;
            }else{
                $smallestCleanSizeOfCurrentGroupSet = $fullSetPartArrayCount - 1;
            }
            if($smallestCleanSetGroupIndex > $smallestCleanSizeOfCurrentGroupSet){
                $smallestCleanSetGroupIndex = $smallestCleanSizeOfCurrentGroupSet;
            }
        }

        return $smallestCleanSetGroupIndex;
    }

    protected function _getItemsQtysToCompleteToCleanByIndex($fullSets, $smallestCleanSetGroupIndex, $validRuleSetPartsIndexes){
        $itemsQtysToCompleteToClean = [];

        foreach($validRuleSetPartsIndexes as $validRuleSetPartsIndex){
            if(isset($fullSets[$validRuleSetPartsIndex])){
                $fullSetPartArray = $fullSets[$validRuleSetPartsIndex];
            }else{
                $fullSetPartArray = [];
            }

            $fullSetPartArrayCount = count($fullSetPartArray);

            $fullSetPartIndex = $validRuleSetPartsIndex;
            $fullSetPartSize = $this->getData('set_part'.$fullSetPartIndex.'_size');

            $fullSetPartHintSingular = $this->getData('set_part'.$fullSetPartIndex.'_hints_singular');
            $fullSetPartHintPlural = $this->getData('set_part'.$fullSetPartIndex.'_hints_plural');

            $itemsQtysToCompleteToCleanItem = [];
            $itemsQtysToCompleteToCleanItem['hints_singular'] = $fullSetPartHintSingular;
            $itemsQtysToCompleteToCleanItem['hints_plural'] = $fullSetPartHintPlural;
            $itemsQtysToCompleteToCleanItem['set_index'] = $fullSetPartIndex;

            $itemsQtysToCompleteToCleanItem['not_added_yet'] = false;
            if($fullSetPartArrayCount == 0){
                $itemsQtysToCompleteToCleanItem['not_added_yet'] = true;
            }
            if($fullSetPartArrayCount <= $smallestCleanSetGroupIndex + 1){
                $qtyToAdd = $fullSetPartSize;
            }else{
                $qtyToAdd = $fullSetPartSize - $fullSetPartArray[$smallestCleanSetGroupIndex+1]['items_qty'];
            }
            $qtyToAdd = ($qtyToAdd < 0 ? 0 : $qtyToAdd);
            $itemsQtysToCompleteToCleanItem['qty'] = $qtyToAdd;

            $itemsQtysToCompleteToClean[] = $itemsQtysToCompleteToCleanItem;
        }

        return $itemsQtysToCompleteToClean;
    }

    protected function _calcCleanAndFullSets($priceToItem, $validRuleSetPartsIndexes){
        $cleanSetParts = [];
        $fullSetParts = [];

        $itemsQtysMatched = [];
        $activeSetsNumber = 0;

        foreach($validRuleSetPartsIndexes as $setPartIndex){
            $setPartSize = $this->getData('set_part'.$setPartIndex.'_size');

            $activeSetsNumber++;

            $setPartActionDetail = $this->getData('set_part'.$setPartIndex.'_action_details');

            $validPriceToItem = [];
            foreach($priceToItem as $item){
                if($this->apHelper->validateActionDetail($setPartActionDetail, $item)){
                    $validPriceToItem[] = $item;
                }
            }

            if(count($validPriceToItem) == 0){
                continue;
            }

            $setPartGroupsCount = 0;
            $fullSetPartGroups = [];


            $currentSetPartGroupItemQty = 0;

            $validPriceToItemKeys = array_keys($validPriceToItem);
            $currentKeyIndex = 0;

            $currentItemKey = $validPriceToItemKeys[$currentKeyIndex];
            $item = $validPriceToItem[$currentItemKey];

            $fullSetPartGroups[$setPartGroupsCount]['price_sum'] = 0;
            $fullSetPartGroups[$setPartGroupsCount]['items_qty'] = 0;

            while($currentKeyIndex < count($validPriceToItemKeys)){
                $matchedQty = 0;
                $itemId = $this->apHelper->getQuoteItemId($item);
                if(isset($itemsQtysMatched[$itemId])){
                    $matchedQty = $itemsQtysMatched[$itemId];
                }
                if($matchedQty >= $item->getQty()){
                    $currentKeyIndex++;

                    if($currentKeyIndex >= count($validPriceToItemKeys)){
                        break;
                    }

                    $currentItemKey = $validPriceToItemKeys[$currentKeyIndex];
                    $item = $validPriceToItem[$currentItemKey];
                    $itemId = $this->apHelper->getQuoteItemId($item);

                    $matchedQty = 0;
                    if(isset($itemsQtysMatched[$itemId])){
                        $matchedQty = $itemsQtysMatched[$itemId];
                    }
                }

                if($currentSetPartGroupItemQty >= $setPartSize){
                    $setPartGroupsCount += 1;

                    if($this->getNumberOfSets() == 1){
                        if(is_numeric($this->getRule()->getMaxGroupsNumber())
                            && ($this->getRule()->getMaxGroupsNumber() > 0)){
                            if($setPartGroupsCount >= $this->getRule()->getMaxGroupsNumber()){
                                $cleanSetParts[$setPartIndex] = $fullSetPartGroups;
                                $fullSetParts[$setPartIndex] = $fullSetPartGroups;

                                continue 2;
                            }
                        }
                    }else{
                        if(is_numeric($this->getRule()->getMaxSetsNumber())
                            && ($this->getRule()->getMaxSetsNumber() > 0)){
                            if($setPartGroupsCount >= $this->getRule()->getMaxSetsNumber()){
                                $cleanSetParts[$setPartIndex] = $fullSetPartGroups;
                                $fullSetParts[$setPartIndex] = $fullSetPartGroups;

                                continue 2;
                            }
                        }
                    }


                    $currentSetPartGroupItemQty = 0;

                    $fullSetPartGroups[$setPartGroupsCount] = [];
                    $fullSetPartGroups[$setPartGroupsCount]['price_sum'] = 0;
                    $fullSetPartGroups[$setPartGroupsCount]['items_qty'] = 0;
                }

                $maxItemQtyToMatch = max($item->getQty()-$matchedQty, 0);

                $qtyToApplyOnItem = max(
                    0,
                    min($maxItemQtyToMatch, $setPartSize-$currentSetPartGroupItemQty)
                );

                $currentSetPartGroupItemQty += $qtyToApplyOnItem;

                if(!isset($fullSetPartGroups[$setPartGroupsCount]['items'])){
                    $fullSetPartGroups[$setPartGroupsCount]['items'] = [];
                }
                $fullSetPartGroups[$setPartGroupsCount]['items'][$currentItemKey] = [];
                $fullSetPartGroups[$setPartGroupsCount]['items'][$currentItemKey]['qty'] = $qtyToApplyOnItem;
                $fullSetPartGroups[$setPartGroupsCount]['items'][$currentItemKey]['item'] = $item;

                if(!isset($itemsQtysMatched[$itemId])){
                    $itemsQtysMatched[$itemId] = $qtyToApplyOnItem;
                }else{
                    $itemsQtysMatched[$itemId] += $qtyToApplyOnItem;
                }

                $itemPrice = $this->apHelper->getItemPrice($item);
                $fullSetPartGroups[$setPartGroupsCount]['price_sum'] += ($itemPrice*$qtyToApplyOnItem);
                $fullSetPartGroups[$setPartGroupsCount]['items_qty'] += $qtyToApplyOnItem;
            }

            $lastSetPartGroupItemQty = $currentSetPartGroupItemQty;
            $lastSetPartGroupIndex = $setPartGroupsCount;

            $cleanSetPartGroups = $fullSetPartGroups;
            if($lastSetPartGroupItemQty < $setPartSize){
                unset($cleanSetPartGroups[$lastSetPartGroupIndex]);
            }

            $cleanSetParts[$setPartIndex] = $cleanSetPartGroups;
            $fullSetParts[$setPartIndex] = $fullSetPartGroups;
        }



        $sets = [];
        if(count($cleanSetParts) < $activeSetsNumber){
            return [
                'clean_sets' => $sets,
                'full_set_parts' => $fullSetParts,
            ];
        }

        $shortestSetPartsArrayCount = INF;
        foreach($cleanSetParts as $setPartArray){
            if(count($setPartArray) < $shortestSetPartsArrayCount){
                $shortestSetPartsArrayCount = count($setPartArray);
            }
        }

        for($setIndex = 0; $setIndex < $shortestSetPartsArrayCount; $setIndex++){
            $currentSet = [
                'items' => [],
                'price_sum' => 0
            ];
            for($setPartIndex = 1; $setPartIndex <= $this->getNumberOfSets(); $setPartIndex++){
                if(isset($cleanSetParts[$setPartIndex][$setIndex])){
                    $currentSet['items'] = array_merge($currentSet['items'], $cleanSetParts[$setPartIndex][$setIndex]['items']);
                    $currentSet['price_sum'] += $cleanSetParts[$setPartIndex][$setIndex]['price_sum'];
                }
            }
            $sets[$setIndex] = $currentSet;
        }

        return [
            'clean_sets' => $sets,
            'full_set_parts' => $fullSetParts,
        ];
    }

    public function getGroupTitleSingular($groupNumber){
        return $this->getData('set_part'.$groupNumber.'_hints_singular');
    }

    public function getGroupTitlePlural($groupNumber){
        return $this->getData('set_part'.$groupNumber.'_hints_plural');
    }

    public function getGroupQty($groupNumber){
        return $this->getData('set_part'.$groupNumber.'_size');
    }

    public function getNonEmptyGroupNumbers(){
        $nonEmptyGroupNumbers = [];
        for($groupNumber=1; $groupNumber<=$this->getNumberOfSets(); $groupNumber++){
            if(is_numeric($this->getData('set_part'.$groupNumber.'_size'))){
                $nonEmptyGroupNumbers[] = $groupNumber;
            }
        }
        return $nonEmptyGroupNumbers;
    }

    public function getGroupActionDetail($groupNumber){
        return $this->getData('set_part'.$groupNumber.'_action_details');
    }

    public function getSetPartHintsSingularElement($setPartIndex)
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][set_part'.$setPartIndex.'_hints_singular]',
            'value' => $this->getData('set_part'.$setPartIndex.'_hints_singular'),
            'value_name' => ($this->getData('set_part'.$setPartIndex.'_hints_singular') ? $this->getData('set_part'.$setPartIndex.'_hints_singular') : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__set_part'.$setPartIndex.'_hints_singular',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getSetPartHintsPluralElement($setPartIndex)
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][set_part'.$setPartIndex.'_hints_plural]',
            'value' => $this->getData('set_part'.$setPartIndex.'_hints_plural'),
            'value_name' => ($this->getData('set_part'.$setPartIndex.'_hints_plural') ? $this->getData('set_part'.$setPartIndex.'_hints_plural') : "..."),
            'data-form-part' => $this->getFormName()
        ];
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__set_part'.$setPartIndex.'_hints_plural',
            'text',
            $elementParams
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }
}