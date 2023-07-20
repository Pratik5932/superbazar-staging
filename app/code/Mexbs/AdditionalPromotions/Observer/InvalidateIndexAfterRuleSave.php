<?php
namespace Mexbs\AdditionalPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class InvalidateIndexAfterRuleSave implements ObserverInterface{
    protected $ruleProductProcessor;
    public function __construct(
        \Mexbs\AdditionalPromotions\Model\Indexer\Rule\RuleProductProcessor $ruleProductProcessor
    ){
        $this->ruleProductProcessor = $ruleProductProcessor;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $observer->getEvent()->getEntity();
        if($rule && $rule->getId()){
            $this->ruleProductProcessor->reindexRow($rule->getId());
        }
    }
}