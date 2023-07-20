<?php
namespace Mexbs\AdditionalPromotions\Model\Plugin;

class Quote
{
    public function afterGetAllVisibleItems(
        \Magento\Quote\Model\Quote $subject,
        $items
    ){
        $itemsWithoutChildren = [];
        foreach ($items as $item) {
            if (!$item->isDeleted() && !$item->getParentItemId()
                && !$item->getParentItem()) {
                $itemsWithoutChildren[] = $item;
            }
        }
        return $itemsWithoutChildren;
    }
}