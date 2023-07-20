<?php
namespace Mexbs\AdditionalPromotions\Model\Plugin;

use \Mexbs\AdditionalPromotions\Model\Source\Config\BreakdownType as BreakdownTypeFromConfig;
use \Mexbs\AdditionalPromotions\Model\Source\SalesRule\BreakdownType as SalesRuleBreakdownType;

class Validator{
    protected $apHelper;
    protected $rules;
    protected $collectionFactory;
    protected $serializer;
    protected $counter = 0;

    public function __construct(
        \Mexbs\AdditionalPromotions\Helper\Data $apHelper,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer
    ){
        $this->apHelper = $apHelper;
        $this->collectionFactory = $collectionFactory;
        $this->serializer = $serializer;
    }

    public function beforeInitTotals(
        \Magento\SalesRule\Model\Validator $subject,
        $items, \Magento\Quote\Model\Quote\Address $address
    ){
        $address->unsApDiscountDetails();
        $address->unsDiscountDetails();

        $address->getQuote()->unsHintMessages();

        foreach($items as $item){
            $item->setApRuleMatches(null);
            $item->setApPriceTypeFlags(null);
            $item->setDiscountAmount(0);
            $item->setBaseDiscountAmount(0);
            $item->setDiscountPercent(0);
        }

        return [$items, $address];
    }


    public function aroundInitTotals(
        \Magento\SalesRule\Model\Validator $subject,
        \Closure $proceed,
        $items,
        \Magento\Quote\Model\Quote\Address $address
    ){
        $returnValue = $proceed($items, $address);

        $this->apHelper->processRulesOnItems(
            $items,
            $address,
            $subject->getWebsiteId(),
            $subject->getCustomerGroupId(),
            $subject->getCouponCode()
        );

        return $returnValue;
    }

    protected function _getRuleLabel($rule, $address){
        $ruleLabel = $rule->getStoreLabel($address->getQuote()->getStore());
        if(!$ruleLabel){
            $ruleLabel = $rule->getPrimaryCoupon()->getCode();
            if(!$ruleLabel){
                $ruleLabel = $rule->getName();
            }
        }
        return $ruleLabel;
    }

    protected function _getItemNamesOutOfItems($items = []){
        $itemNames = [];
        foreach($items as $item){
            if(!in_array($item->getName(), $itemNames)){
                $itemNames[] = $item->getName();
            }
        }
        return $itemNames;
    }

    public function aroundPrepareDescription(
        \Magento\SalesRule\Model\Validator $subject,
        \Closure $proceed,
        $address, $separator = ', '
    ){
        $returnValue = $proceed($address, $separator);

        $addressDiscountDetails = [];
        if($address->getDiscountDetails()){
            $addressDiscountDetails = $this->serializer->unserialize($address->getDiscountDetails());
        }

        if(!is_array($addressDiscountDetails)){
            $discountDetails = [];
        }else{
            $discountDetails = $addressDiscountDetails;
        }

        $items = $address->getAllVisibleItems();

        $appliedRuleIds = [];
        $appliedRuleIdToItems = [];

        foreach($items as $item){
            $itemAppliedRuleIdsStr = $item->getAppliedRuleIds();
            if($itemAppliedRuleIdsStr){
                $itemAppliedRuleIds = explode(",",$itemAppliedRuleIdsStr);
                if(is_array($itemAppliedRuleIds)){
                    $appliedRuleIds = array_unique(array_merge($appliedRuleIds, $itemAppliedRuleIds));

                    foreach($itemAppliedRuleIds as $itemAppliedRuleId){
                        if(!isset($appliedRuleIdToItems[$itemAppliedRuleId])){
                            $appliedRuleIdToItems[$itemAppliedRuleId] = [];
                        }
                        $itemId = $this->apHelper->getQuoteItemId($item);
                        $appliedRuleIdToItems[$itemAppliedRuleId][$itemId] = $item;
                    }
                }
            }
        }

        $appliedRulesCollection = $this->apHelper->getRulesCollectionById($appliedRuleIds);
        foreach($appliedRulesCollection as $rule){
            if(isset($discountDetails[$rule->getId()])){
                continue;
            }

            $ruleBreakdownType = $rule->getDiscountBreakdownType();
            if($ruleBreakdownType == SalesRuleBreakdownType::TYPE_CONFIG){
                $ruleBreakdownType = $this->apHelper->getConfigBreakdownType();
            }

            $simpleAction = $rule->getSimpleAction();

            if($this->apHelper->isSimpleActionAp($simpleAction)
                && ($ruleBreakdownType == BreakdownTypeFromConfig::TYPE_COMPREHENSIVE)){
                $apDiscountDetails = $address->getApDiscountDetails();
                if(is_array($apDiscountDetails)
                    && isset($apDiscountDetails[$rule->getId()]['comprehensive_description'])){
                    $discountDetails[$rule->getId()] = $apDiscountDetails[$rule->getId()]['comprehensive_description'];
                }
            }elseif($ruleBreakdownType == BreakdownTypeFromConfig::TYPE_LABELS_AND_PRODUCT_NAMES){
                $ruleLabel = $this->_getRuleLabel($rule, $address);

                $appliedItemsOfTheRule = (
                isset($appliedRuleIdToItems[$rule->getId()])
                    ? $appliedRuleIdToItems[$rule->getId()]
                    : []
                );
                $appliedItemNamesOfTheRule = $this->_getItemNamesOutOfItems($appliedItemsOfTheRule);
                $discountDetails[$rule->getId()]
                    = [$ruleLabel.": ".implode(", ", $appliedItemNamesOfTheRule)];
            }else{
                $ruleLabel = $this->_getRuleLabel($rule, $address);

                $discountDetails[$rule->getId()] = [$ruleLabel];
            }
        }


        $address->setDiscountDetails($this->serializer->serialize($discountDetails));

        return $returnValue;
    }
}