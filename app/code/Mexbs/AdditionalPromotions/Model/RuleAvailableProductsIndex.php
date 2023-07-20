<?php
namespace Mexbs\AdditionalPromotions\Model;

use Magento\Framework\Model\AbstractModel;

class RuleAvailableProductsIndex extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Mexbs\AdditionalPromotions\Model\ResourceModel\RuleAvailableProductsIndex');
    }
}