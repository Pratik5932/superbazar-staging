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
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo as CreditmemoResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;

class CreditmemoSender extends \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender
{
    protected $helper;

    /**
     * CreditmemoSender constructor.
     * @param Template $templateContainer
     * @param CreditmemoIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param CreditmemoResource $creditmemoResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param \Bss\OrderDeliveryDate\Helper\Data $helper
     * @param ManagerInterface $eventManager
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Template $templateContainer,
        CreditmemoIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        CreditmemoResource $creditmemoResource,
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
            $creditmemoResource,
            $globalConfig,
            $eventManager
        );
        $this->helper = $helper;
    }

    /**
     * @param Creditmemo $creditmemo
     * @param bool $forceSyncMode
     * @return bool
     * @throws \Exception
     */
    public function send(Creditmemo $creditmemo, $forceSyncMode = false)
    {
        $creditmemo->setSendEmail(true);

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            $order = $creditmemo->getOrder();

            $transport = [
                'order' => $order,
                'order_id' => $order->getId(),  // Compatible with >= m2.3.5
                'creditmemo' => $creditmemo,
                'creditmemo_id' => $creditmemo->getId(),  // Compatible with >= m2.3.5
                'comment' => $creditmemo->getCustomerNoteNotify() ? $creditmemo->getCustomerNote() : '',
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
                'email_creditmemo_set_template_vars_before',
                ['sender' => $this, 'transport' => $transportObject->getData(), 'transportObject' => $transportObject]
            );

            $this->templateContainer->setTemplateVars($transport);

            if ($this->checkAndSend($order)) {
                $creditmemo->setEmailSent(true);
                $this->creditmemoResource->saveAttribute($creditmemo, ['send_email', 'email_sent']);
                return true;
            }
        }

        $this->creditmemoResource->saveAttribute($creditmemo, 'send_email');

        return false;
    }
}
