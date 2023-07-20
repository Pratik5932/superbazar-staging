<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\OrderDeliveryDate\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;

class OrderLoadAfter implements ObserverInterface
{
    /**
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory
     */
    protected $orderExtensionFactory;

    /**
     * OrderLoadAfter constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bss\OrderDeliveryDate\Helper\Data $helper
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Bss\OrderDeliveryDate\Helper\Data $helper,
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $extensionAttributes = $order->getExtensionAttributes();

        /** @var \Magento\Sales\Api\Data\OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();

        $date = $order->getShippingArrivalDate();
        $timeSlot = $order->getShippingArrivalTimeslot();
        $comment = $order->getShippingArrivalComments();
        if (isset($date)) {
            $orderExtension->setShippingArrivalDate($date);
        }
        if (isset($timeSlot)) {
            $orderExtension->setShippingArrivalTimeslot($timeSlot);
        }
        if (isset($comment)) {
            $orderExtension->setShippingArrivalComments($comment);
        }
        $order->setExtensionAttributes($orderExtension);
    }
}
