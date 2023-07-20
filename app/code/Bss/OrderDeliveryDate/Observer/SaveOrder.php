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

use Magento\Framework\Event\ObserverInterface;

class SaveOrder implements ObserverInterface
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
     * SaveOrder constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bss\OrderDeliveryDate\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Bss\OrderDeliveryDate\Helper\Data $helper
    ) {
    
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnabled()) {
            $order = $observer->getOrder();
            $params = $this->request->getParams();

            if (isset($params['shipping_arrival_date'])) {
                $order->setShippingArrivalDate($params['shipping_arrival_date']);
            }
            if (isset($params['delivery_time_slot'])) {
                $order->setShippingArrivalTimeslot($params['delivery_time_slot']);
            }
            if (isset($params['shipping_arrival_comments'])) {
                $order->setShippingArrivalComments($params['shipping_arrival_comments']);
            }
            $order->save();
        }
    }
}
