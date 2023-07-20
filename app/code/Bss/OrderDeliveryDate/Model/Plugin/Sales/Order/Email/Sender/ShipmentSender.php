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
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;

class ShipmentSender extends \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
{
    protected $helper;

    /**
     * ShipmentSender constructor.
     * @param Template $templateContainer
     * @param ShipmentIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param ShipmentResource $shipmentResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param \Bss\OrderDeliveryDate\Helper\Data $helper
     * @param ManagerInterface $eventManager
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Template $templateContainer,
        ShipmentIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        ShipmentResource $shipmentResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        \Bss\OrderDeliveryDate\Helper\Data $helper,
        ManagerInterface $eventManager
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer,
            $paymentHelper,
            $shipmentResource,
            $globalConfig,
            $eventManager
        );
        $this->helper = $helper;
    }

    /**
     * @param Shipment $shipment
     * @param bool $forceSyncMode
     * @return bool
     * @throws \Exception
     */
    public function send(Shipment $shipment, $forceSyncMode = false)
    {
        $shipment->setSendEmail(true);

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            $order = $shipment->getOrder();

            $transport = [
                'order' => $order,
                'order_id' => $order->getId(),  // Compatible with >= m2.3.5
                'shipment' => $shipment,
                'shipment_id' => $shipment->getId(),  // Compatible with >= m2.3.5
                'comment' => $shipment->getCustomerNoteNotify() ? $shipment->getCustomerNote() : '',
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                'delivery_time_slot' => $order->getShippingArrivalTimeslot(),
                'shipping_arrival_comments' => $order->getShippingArrivalComments(),
                // Compatible with >= m2.3.5
                'order_data' => [
                    'customer_name' => $order->getCustomerName(),
                    'is_not_virtual' => $order->getIsNotVirtual(),
                    'email_customer_note' => $order->getEmailCustomerNote(),
                    'frontend_status_label' => $order->getFrontendStatusLabel()
                ]
            ];

            if ($order->getShippingArrivalDate()) {
                $transport['shipping_arrival_date'] = $this->helper->formatDate($order->getShippingArrivalDate());
            }

            $transportObject = new DataObject($transport);

            $this->eventManager->dispatch(
                'email_shipment_set_template_vars_before',
                ['sender' => $this, 'transport' => $transportObject->getData(), 'transportObject' => $transportObject]
            );

            $this->templateContainer->setTemplateVars($transport);

            if ($this->checkAndSend($order)) {
                $shipment->setEmailSent(true);
                $this->shipmentResource->saveAttribute($shipment, ['send_email', 'email_sent']);
                return true;
            }
        }

        $this->shipmentResource->saveAttribute($shipment, 'send_email');

        return false;
    }
}
