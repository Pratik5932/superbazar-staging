<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Discount;

abstract class ProductsSetTotalDiscountAbstract extends ApDiscountAbstract
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


        $discountData->setAmount(0);
        $discountData->setBaseAmount(0);
        $discountData->setOriginalAmount(0);
        $discountData->setBaseOriginalAmount(0);


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
                if(isset($itemApRuleMatches[$rule->getId()]['apply']['groups'])
                    && is_array($itemApRuleMatches[$rule->getId()]['apply']['groups'])){
                    foreach($itemApRuleMatches[$rule->getId()]['apply']['groups'] as $groupDiscountData){
                        if(!isset($groupDiscountData['expected_prices'])
                            || (
                                isset($groupDiscountData['expected_prices'])
                                && !$this->_isPricesMatchExpected($itemPrices, $groupDiscountData['expected_prices'])
                            )){
                            $itemSkus = $this->_getItemSkus($items);
                            $this->_logErrorPricesDontMatchExpected($itemPrices, $groupDiscountData['expected_prices'], $itemSkus);
                            return $discountData;
                        }

                        if(isset($groupDiscountData['qty'])
                            && isset($groupDiscountData['discount_percent_in_decimal'])){

                            $discountQty = $groupDiscountData['qty'];
                            $discountPercentInDecimal = $groupDiscountData['discount_percent_in_decimal'];

                            $discountData->setAmount($discountData->getAmount() + $discountPercentInDecimal * $discountQty * $itemPrices['price']);
                            $discountData->setBaseAmount($discountData->getBaseAmount() + $discountPercentInDecimal * $discountQty * $itemPrices['base_price']);
                            $discountData->setOriginalAmount($discountData->getOriginalAmount() + $discountPercentInDecimal * $discountQty * $itemPrices['original_price']);
                            $discountData->setBaseOriginalAmount($discountData->getBaseOriginalAmount() + $discountPercentInDecimal * $discountQty * $itemPrices['base_original_price']);
                        }
                    }
                }
            }
        }

        return $discountData;
    }
}