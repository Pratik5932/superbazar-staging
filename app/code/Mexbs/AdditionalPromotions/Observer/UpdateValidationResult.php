<?php
namespace Mexbs\AdditionalPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateValidationResult implements ObserverInterface{

    protected $apHelper;

    public function __construct(
        \Mexbs\AdditionalPromotions\Helper\Data $apHelper
    ) {
        $this->apHelper = $apHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $itemValidationResult = $observer->getEvent()->getItemValidationResult();
        $item = $observer->getEvent()->getItem();
        $rule = $observer->getEvent()->getRule();
        $appliesRules = $observer->getEvent()->getAppliesRules();

        if(!$rule->getId()){
            return;
        }

        $simpleAction = $rule->getSimpleAction();
        if(!$this->apHelper->isSimpleActionAp($simpleAction)){
            return;
        }

        $address = $item->getAddress();
        $items = $address->getAllVisibleItems();
        if(!$items){
            return;
        }

        if($this->apHelper->getIsOneOfTheItemsMarkedByRule([$item], $rule->getId())){
            return;
        }

        $itemValidationResult->setResult(false);
    }
}