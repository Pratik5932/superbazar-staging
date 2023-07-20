<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Discount;

class FirstNNextMAbstract extends ApDiscountAbstract
{
    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $itemPrices = [
            'price' => $this->apHelper->getItemPrice($item),
            'base_price' => $this->apHelper->getItemBasePrice($item),
            'original_price' => $this->apHelper->getItemOriginalPrice($item),
            'base_original_price' => $this->apHelper->getItemBaseOriginalPrice($item),
        ];

        $discountQty = 0;

        $actionDetailModel = $this->apHelper->getLoadedActionDetail($rule);
        $simpleAction = $rule->getSimpleAction();

        $items = $item->getAddress()->getAllVisibleItems();

        $itemApRuleMatches = $this->apHelper->getApRuleMatchesForItem($item);

        if(!isset($itemApRuleMatches[$rule->getId()])){
            if($this->apHelper->isSimpleActionAp($simpleAction)){
                $actionDetailModel = $this->apHelper->getLoadedActionDetail($rule);
                if($actionDetailModel){
                    $actionDetailModel->markMatchingItemsAndGetHint($items, $item->getAddress());
                }
            }
            $itemApRuleMatches = $this->apHelper->getApRuleMatchesForItem($item);
        }

        if(isset($itemApRuleMatches[$rule->getId()])){
            if(isset($itemApRuleMatches[$rule->getId()]['apply'])){
                if(!isset($itemApRuleMatches[$rule->getId()]['apply']['expected_prices'])
                    || (
                        isset($itemApRuleMatches[$rule->getId()]['apply']['expected_prices'])
                        && !$this->_isPricesMatchExpected($itemPrices, $itemApRuleMatches[$rule->getId()]['apply']['expected_prices'])
                    )){
                    $itemSkus = $this->_getItemSkus($items);
                    $this->_logErrorPricesDontMatchExpected($itemPrices, $itemApRuleMatches[$rule->getId()]['apply']['expected_prices'], $itemSkus);
                    return $discountData;
                }
            }
        }

        if(isset($itemApRuleMatches[$rule->getId()]['apply']['qtys_discs'])){
            foreach($itemApRuleMatches[$rule->getId()]['apply']['qtys_discs'] as $qtyDisc){
                $discountPercentInDecimal = $qtyDisc['discount_percent_in_decimal'];
                $discountQty = $qtyDisc['qty'];

                $discountData->setAmount($discountData->getAmount() + $discountPercentInDecimal * $discountQty * $itemPrices['price']);
                $discountData->setBaseAmount($discountData->getBaseAmount() + $discountPercentInDecimal * $discountQty * $itemPrices['base_price']);
                $discountData->setOriginalAmount($discountData->getOriginalAmount() + $discountPercentInDecimal * $discountQty * $itemPrices['original_price']);
                $discountData->setBaseOriginalAmount($discountData->getBaseOriginalAmount() + $discountPercentInDecimal * $discountQty * $itemPrices['base_original_price']);
            }
        }
        return $discountData;
    }
}