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



namespace Mirasvit\Rewards\Plugin;

use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Mirasvit\Rewards\Helper\Balance\EarnBehaviorOrderPoints;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class OrderCustomerManagement
{
    private $orderRepository;

    private $earnBehaviorOrderPoints;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        EarnBehaviorOrderPoints $earnBehaviorOrderPoints
    ) {
        $this->orderRepository         = $orderRepository;
        $this->earnBehaviorOrderPoints = $earnBehaviorOrderPoints;
    }

    /**
     * @param OrderCustomerManagementInterface $config
     * @param \callable                        $proceed
     * @param int                              $orderId
     *
     * @return bool
     */
    public function aroundCreate(OrderCustomerManagementInterface $config, $proceed, $orderId)
    {
        $result = $proceed($orderId);

        if ($result->getId()) {
            $order = $this->orderRepository->get($orderId);

            if ($order->getId()) {
                $this->earnBehaviorOrderPoints->earnBehaviorOrderPoints($order);
            }
        }

        return $result;
    }
}
