<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\RewardsCheckout\Plugin\Klarna\Base\Model\Checkout\Orderline;

use Klarna\Base\Model\Api\Parameter;
use Klarna\Base\Model\Api\DataHolder;
use Klarna\Orderlines\Model\Items\Items;
use Magento\Quote\Api\Data\CartInterface;
use Mirasvit\Rewards\Helper\Purchase as PurchaseHelper;

/**
 * @see Items::collectPrePurchase()
 */
class ApplyDiscountToItemPlugin245
{
    private $purchaseHelper;

    public function __construct(
        PurchaseHelper $purchaseHelper
    ) {
        $this->purchaseHelper = $purchaseHelper;
    }

    /**
     * @param Items         $subject
     * @param \callable     $proceed
     * @param Parameter     $parameter
     * @param DataHolder    $dataHolder
     * @param CartInterface $quote
     *
     * @return Items
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCollectPrePurchase(Items $subject, $proceed, Parameter $parameter, DataHolder $dataHolder, CartInterface $quote)
    {
        $result = $proceed($parameter, $dataHolder, $quote);

        if (!$quote) {
            return $result;
        }

        $purchase = $this->purchaseHelper->getByQuote($quote->getId());
        if ($purchase->getSpendAmount() <= 0) {
            return $result;
        }

        $items = $parameter->getItems();

        $discounts = $this->calculateDiscountPercent($items);

        $rewardsDiscount = $purchase->getSpendAmount() * 100; // klarna format

        foreach ($items as $k => $item) {
            $itemDiscount = $purchase->getSpendAmount() * 100 * $discounts[$item['reference']];

            if ($itemDiscount != round($itemDiscount, 0)) {
                $itemDiscount = round($itemDiscount, 0);
            }

            $items[$k]['total_amount']          -= $itemDiscount;
            $items[$k]['total_discount_amount'] += $itemDiscount;

            $rewardsDiscount -= $itemDiscount;

            $lastItem = $k;
        }

        if ($rewardsDiscount > 0) {
            $items[$lastItem]['total_amount']          -= $rewardsDiscount;
            $items[$lastItem]['total_discount_amount'] += $rewardsDiscount;
        } elseif ($rewardsDiscount < 0) {
            $items[$lastItem]['total_amount']          += $rewardsDiscount;
            $items[$lastItem]['total_discount_amount'] -= $rewardsDiscount;
        }

        $parameter->setItems($items);

        return $result;
    }

    private function calculateDiscountPercent($items)
    {
        $discounts = [];

        $sum = 0;
        foreach ($items as $item) {
            $sum += $item['total_amount'];
        }

        $lastItem    = null;
        $percentLeft = 1;

        if ($sum <= 0) {
            return $discounts;
        }

        foreach ($items as $item) {
            $itemPercent = $item['total_amount'] / $sum;

            if ($itemPercent != round($itemPercent, 6)) {
                $itemPercent = round($itemPercent, 6);
            }

            $percentLeft -= $itemPercent;

            $discounts[$item['reference']] = $itemPercent;

            $lastItem = $item['reference'];
        }

        $percentLeft = round($percentLeft, 6);

        $discounts[$lastItem] += $percentLeft;

        return $discounts;
    }
}
