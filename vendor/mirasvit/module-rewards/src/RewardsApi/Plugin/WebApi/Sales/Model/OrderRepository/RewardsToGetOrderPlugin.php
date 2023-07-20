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


namespace Mirasvit\RewardsApi\Plugin\WebApi\Sales\Model\OrderRepository;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Mirasvit\Rewards\Helper\Purchase;

/**
 * @package Mirasvit\Rewards\Plugin\WebApi
 */
class RewardsToGetOrderPlugin
{
    /**
     * @var Purchase
     */
    private $purchaseHelper;

    public function __construct(Purchase $purchaseHelper)
    {
        $this->purchaseHelper = $purchaseHelper;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface           $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        /** @var \Magento\Sales\Api\Data\OrderExtension $attributes */
        $attributes = $order->getExtensionAttributes();
        $purchase = $this->purchaseHelper->getByOrder($order);
        if ($purchase) {
            $attributes->setRewardsDiscount($purchase->getSpendAmount());
            $attributes->setRewardsSpend($purchase->getSpendPoints());
            $attributes->setRewardsEarn($purchase->getEarnPoints());
        }

        return $order;
    }
}