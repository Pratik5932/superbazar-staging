<?php
namespace Mexbs\AdditionalPromotions\Model\Rewrite\Rule\Condition;

class Combine extends \Magento\SalesRule\Model\Rule\Condition\Combine
{
    protected function _combineCartHintLines($cartHintLinesArr, $aggregator = 'all'){
        $cartHintLinesCount = count($cartHintLinesArr);
        $cartHintLinesIndex = 0;
        $cartHint = "";

        foreach($cartHintLinesArr as $cartHintLine){
            if($cartHintLinesIndex == ($cartHintLinesCount-1)
                && ($cartHintLinesIndex > 0)){
                if($aggregator == 'all'){
                    $cartHint .= " and ".$cartHintLine;
                }else{
                    $cartHint .= " or ".$cartHintLine;
                }
            }elseif($cartHintLinesIndex == 0){
                $cartHint .= ucfirst($cartHintLine);
            }else{
                $cartHint .= ", ".$cartHintLine;
            }
            $cartHintLinesIndex++;
        }

        return $cartHint;
    }

    protected function _invertCartHintData($subRuleCartHintData){
        $invertedCartHintData = [];
        $logicalOperatorKeysToInverter = [
            'and' => 'or',
            'or' => 'and'
        ];
        $keyToInvertedMapping = [
            'qty_to_add' => 'qty_to_add_inverted',
            'qty_to_add_more' => 'qty_to_add_more_inverted',
            'qty_to_add_reduce' => 'qty_to_add_reduce_inverted',
            'qty_to_add_reduce_more' => 'qty_to_add_reduce_more_inverted',
            'qty_to_add_inverted' => 'qty_to_add',
            'qty_to_add_more_inverted' => 'qty_to_add_more',
            'qty_to_add_reduce_inverted' => 'qty_to_add_reduce',
            'qty_to_add_reduce_more_inverted' => 'qty_to_add_reduce_more'
        ];
        if(!is_array($subRuleCartHintData)){
            return $subRuleCartHintData;
        }else{
            $subRuleCartHintDataArrayKeys = array_keys($subRuleCartHintData);
            if(count($subRuleCartHintDataArrayKeys) > 0){
                if(array_key_exists($subRuleCartHintDataArrayKeys[0], $logicalOperatorKeysToInverter)){
                    $invertedCartHintData[$logicalOperatorKeysToInverter[$subRuleCartHintDataArrayKeys[0]]] =
                        $this->_invertCartHintData($subRuleCartHintData[$subRuleCartHintDataArrayKeys[0]]);
                }else{
                    foreach($subRuleCartHintData as $subRuleCartHintDataKey => $subRuleCartHintDataValue){
                        if(array_key_exists($subRuleCartHintDataKey, $keyToInvertedMapping)){
                            $invertedCartHintData[$subRuleCartHintDataKey] = $invertedCartHintData[$keyToInvertedMapping[$subRuleCartHintDataKey]];
                        }
                    }
                }
            }
        }
        return $invertedCartHintData;
    }


    protected function _isValid($entity)
    {
        $this->setLastCartHintData(null);
        if (!$this->getConditions()) {
            return true;
        }

        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();

        $logicalRuleOperator = ($all ? 'and' : 'or');
        $cartHintData = [
            $logicalRuleOperator => []
        ];

        $validated = false;
        $allValidated = true;

        foreach ($this->getConditions() as $cond) {
            if(!$cond->getSupportsCartHints()){
                $this->setSupportsCartHints(false);
            }

            if ($entity instanceof \Magento\Framework\Model\AbstractModel) {
                $validated = $cond->validate($entity);
            } else {
                $validated = $cond->validateByEntityId($entity);
            }

            if($cond->getSupportsCartHints()){
                $subRuleCartHintData = $cond->getLastCartHintData();

                if(!$true && $subRuleCartHintData){
                    $subRuleCartHintData = $this->_invertCartHintData($subRuleCartHintData);
                }
                $cartHintData[$logicalRuleOperator][] = $subRuleCartHintData;
            }

            $allValidated = ($allValidated && $validated);

            if (!$all && $validated === $true) {
                $this->setLastCartHintData($cartHintData);
                return true;
            }
        }

        if($this->getSupportsCartHints()){
            $this->setLastCartHintData($cartHintData);
        }

        if($all && $allValidated){
            return true;
        }else{
            return false;
        }
    }

    public function getSupportsCartHints(){
        return true;
    }
}