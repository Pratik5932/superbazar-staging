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



namespace Mirasvit\Rewards\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Rewards\Api\Service\RefundServiceInterface;
use Mirasvit\Rewards\Helper\Calculation;

class OrderLoadAfter implements ObserverInterface
{
    /**
     * @var RefundServiceInterface
     */
    private $refundService;

    public function __construct(
        RefundServiceInterface $refundService
    ) {
        $this->refundService = $refundService;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->canUnhold() || $order->isCanceled()) {
            return;
        }
        $refundInfo = $this->refundService->getByOrderId($order->getId());
        if ($refundInfo->getBaseRefundedSum() > 0) {
            $leftToRefund = $order->getBaseTotalInvoiced() - $order->getBaseTotalOfflineRefunded() -
                $order->getBaseTotalOnlineRefunded() - $refundInfo->getBaseRefundedSum();
            // due to math errors in php we use 0.00099
            if ($leftToRefund > Calculation::ZERO_VALUE) {
                $order->setForcedCanCreditmemo(true);
            }
        }
    }
}
