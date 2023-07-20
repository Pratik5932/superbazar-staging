<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Discount;

class GetYForEachXSpentAbstract extends ApDiscountAbstract
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

        $discountTotalFixedAmount = 0;
        $discountQty = 0;

        $items = $item->getAddress()->getAllVisibleItems();

        $itemApRuleMatches = $this->apHelper->getApRuleMatchesForItem($item);
        $simpleAction = $rule->getSimpleAction();

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

                if(isset($itemApRuleMatches[$rule->getId()]['apply']['get_y_amount'])
                    && isset($itemApRuleMatches[$rule->getId()]['apply']['qty'])
                ){
                    $discountTotalFixedAmount = $itemApRuleMatches[$rule->getId()]['apply']['get_y_amount'];
                    $discountQty = $itemApRuleMatches[$rule->getId()]['apply']['qty'];
                }
            }
        }

        $discountPercentDecimalFixedOutOfPrice =
            ($discountTotalFixedAmount >= ($itemPrices['price'] * $discountQty)
                ? 1
                : $discountTotalFixedAmount/($itemPrices['price'] * $discountQty)
            );

        $discountData->setAmount($discountPercentDecimalFixedOutOfPrice * $discountQty * $itemPrices['price']);
        $discountData->setBaseAmount($discountPercentDecimalFixedOutOfPrice * $discountQty * $itemPrices['base_price']);
        $discountData->setOriginalAmount($discountPercentDecimalFixedOutOfPrice * $discountQty * $itemPrices['original_price']);
        $discountData->setBaseOriginalAmount($discountPercentDecimalFixedOutOfPrice * $discountQty * $itemPrices['base_original_price']);

        return $discountData;
    }
}