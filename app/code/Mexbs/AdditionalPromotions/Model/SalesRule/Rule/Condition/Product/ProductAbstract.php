<?php
namespace Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Product;

abstract class ProductAbstract extends \Magento\SalesRule\Model\Rule\Condition\Product{
    abstract public function getDirectAttributeKeys();

    public function loadArray($arr)
    {
        $this->setType($arr['type']);
        foreach($this->getDirectAttributeKeys() as $directAttributeKey){
            $this->setData($directAttributeKey, (isset($arr[$directAttributeKey]) ? $arr[$directAttributeKey] : ''));
        }
    }

    public function asArray(array $arrAttributes = [])
    {
        $out = [
            'type' => $this->getType()
        ];
        foreach($this->getDirectAttributeKeys() as $directAttributeKey){
            $out[$directAttributeKey] = $this->getData($directAttributeKey);
        }
        return $out;
    }
}