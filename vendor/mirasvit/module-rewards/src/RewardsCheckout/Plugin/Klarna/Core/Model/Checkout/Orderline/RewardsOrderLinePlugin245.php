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



namespace Mirasvit\RewardsCheckout\Plugin\Klarna\Core\Model\Checkout\Orderline;

use Mirasvit\Rewards\Helper\Purchase;

class RewardsOrderLinePlugin245
{
    const ITEM_TYPE_REWARDS = 'discount';

    static  $discountCalculated = false;

    static  $discountApplied    = false;

    private $purchaseHrlper;

    public function __construct(
        Purchase $purchaseHrlper
    ) {
        $this->purchaseHrlper = $purchaseHrlper;
    }

    /**
     * @param \Klarna\Kp\Model\Api\Request\Orderline $subject
     * @param \callable                              $proceed
     * @param \Klarna\Base\Model\Api\Parameter       $checkout
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCollect(
        \Klarna\Kp\Model\Api\Request\Orderline $subject, $proceed, \Klarna\Base\Model\Api\Parameter $checkout
    ) {
        $result = $proceed($checkout);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $checkout->getObject();

        $purchase = $this->purchaseHrlper->getByQuote($quote);

        if (!self::$discountCalculated && $purchase && $purchase->getSpendAmount() > 0) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Klarna\Base\Helper\DataConverter $klarnaHelper */
            $klarnaHelper = $objectManager->create('Klarna\Base\Helper\DataConverter');

            $value = $klarnaHelper->toApiFloat($purchase->getSpendAmount());

            $checkout->addData([
                'rewards_unit_price'   => $value,
                'rewards_tax_rate'     => 0,
                'rewards_total_amount' => $value,
                'rewards_tax_amount'   => 0,
                'rewards_title'        => (string)__('Rewards Discount'),
                'rewards_reference'    => 'rewards',

            ]);

            self::$discountCalculated = true;
        }

        return $result;
    }

    /**
     * @param \Klarna\Kp\Model\Api\Request\Orderline $subject
     * @param \callable                              $proceed
     * @param \Klarna\Base\Model\Api\Parameter       $checkout
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundFetch(
        \Klarna\Kp\Model\Api\Request\Orderline $subject, $proceed, \Klarna\Base\Model\Api\Parameter $parameter
    ) {
        $result = $proceed($parameter);

        if (!self::$discountCalculated && $parameter->getRewardsTotalAmount()) {
            $title = __('Rewards Discount')->getText();

            $parameter->addOrderLine([
                'type'             => self::ITEM_TYPE_REWARDS,
                'reference'        => $parameter->getRewardsReference(),
                'name'             => $title,
                'quantity'         => 1,
                'unit_price'       => $parameter->getRewardsUnitPrice(),
                'tax_rate'         => $parameter->getRewardsTaxRate(),
                'total_amount'     => $parameter->getRewardsTotalAmount(),
                'total_tax_amount' => $parameter->getRewardsTaxAmount(),
            ]);

            self::$discountCalculated = true;
        }

        return $result;
    }
}
