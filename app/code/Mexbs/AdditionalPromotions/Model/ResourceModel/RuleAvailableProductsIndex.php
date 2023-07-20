<?php
namespace Mexbs\AdditionalPromotions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RuleAvailableProductsIndex extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('apactionrule_product', 'apactionrule_product_id');
    }

    public function getNumberOfProductsPerRuleAndGroup($ruleId, $groupNumber){
        $select = $this->getConnection()->select()->from(
            $this->getTable('apactionrule_product'),
            [new \Zend_Db_Expr("count(*)")]
        )->where(
            sprintf(
                'rule_id = "%s" AND group_number = "%s"',
                $ruleId,
                $groupNumber
            )
        );
        $result = $this->getConnection()->fetchCol($select);
        if(isset($result[0])){
            return $result[0];
        }
        return 0;
    }
}