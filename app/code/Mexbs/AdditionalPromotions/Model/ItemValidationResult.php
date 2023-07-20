<?php
namespace Mexbs\AdditionalPromotions\Model;

class ItemValidationResult extends \Magento\Framework\DataObject{
    public function getResult(){
        if(!is_bool($this->getData('result'))){
            return true;
        }
        return $this->getData('result');
    }
}