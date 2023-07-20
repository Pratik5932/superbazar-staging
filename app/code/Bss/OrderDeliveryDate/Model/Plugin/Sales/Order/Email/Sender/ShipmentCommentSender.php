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

namespace Bss\OrderDeliveryDate\Model\Plugin\Sales\Order\Email\Sender;

use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Email\Container\ShipmentCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;

class ShipmentCommentSender extends \Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender
{
    /**
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * ShipmentCommentSender constructor.
     * @param Template $templateContainer
     * @param ShipmentCommentIdentity $identityContainer
     * @param \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param \Bss\OrderDeliveryDate\Helper\Data $helper
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        ShipmentCommentIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        \Bss\OrderDeliveryDate\Helper\Data $helper,
        ManagerInterface $eventManager
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer,
            $eventManager
        );
        $this->helper = $helper;
    }

    /**
     * @param Shipment $shipment
     * @param bool $notify
     * @param string $comment
     * @return bool
     */
    public function send(Shipment $shipment, $notify = true, $comment = '')
    {
        $order = $shipment->getOrder();
        $transport = [
            'order' => $order,
            'shipment' => $shipment,
            'comment' => $comment,
            'billing' => $order->getBillingAddress(),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'delivery_time_slot' => $order->getShippingArrivalTimeslot(),
            'shipping_arrival_comments' => $order->getShippingArrivalComments(),
            // Compatible with >= m2.3.5
            'order_data' => [
                'customer_name' => $order->getCustomerName(),
                'frontend_status_label' => $order->getFrontendStatusLabel()
            ]
        ];

        if ($order->getShippingArrivalDate()) {
            $transport['shipping_arrival_date'] = $this->helper->formatDate($order->getShippingArrivalDate());
        }

        $transportObject = new DataObject($transport);

        $this->eventManager->dispatch(
            'email_shipment_comment_set_template_vars_before',
            ['sender' => $this, 'transport' => $transportObject->getData(), 'transportObject' => $transportObject]
        );

        $this->templateContainer->setTemplateVars($transport);

        return $this->checkAndSend($order, $notify);
    }
}
