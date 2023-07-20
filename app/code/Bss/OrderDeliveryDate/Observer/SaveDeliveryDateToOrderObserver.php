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

namespace Bss\OrderDeliveryDate\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Save delivery date to order
 */
class SaveDeliveryDateToOrderObserver implements ObserverInterface
{
    /**
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * SaveDeliveryDateToOrderObserver constructor.
     * @param \Bss\OrderDeliveryDate\Helper\Data $helper
     */
    public function __construct(
        \Bss\OrderDeliveryDate\Helper\Data $helper
    ) {

        $this->helper = $helper;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->helper->isEnabled()) {
            $order = $observer->getOrder();
            $quote = $observer->getQuote();
            $order->setShippingArrivalComments($quote->getShippingArrivalComments());
            $order->setShippingArrivalDate($quote->getShippingArrivalDate());
            $order->setShippingArrivalTimeslot($quote->getShippingArrivalTimeslot());
        }
        return $this;
    }
}
