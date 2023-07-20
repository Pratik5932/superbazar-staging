<?php
namespace Mexbs\AdditionalPromotions\Model\ResourceModel\RuleAvailableProductsIndex;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Mexbs\AdditionalPromotions\Model\RuleAvailableProductsIndex', 'Mexbs\AdditionalPromotions\Model\ResourceModel\RuleAvailableProductsIndex');
    }
}