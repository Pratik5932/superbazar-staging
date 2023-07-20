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
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;

class CreditmemoCommentSender extends \Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender
{
    /**
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * CreditmemoCommentSender constructor.
     * @param Template $templateContainer
     * @param CreditmemoCommentIdentity $identityContainer
     * @param \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param \Bss\OrderDeliveryDate\Helper\Data $helper
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        CreditmemoCommentIdentity $identityContainer,
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
     * @param Creditmemo $creditmemo
     * @param bool $notify
     * @param string $comment
     * @return bool
     */
    public function send(Creditmemo $creditmemo, $notify = true, $comment = '')
    {
        $order = $creditmemo->getOrder();
        $transport = [
            'order' => $order,
            'creditmemo' => $creditmemo,
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
            'email_creditmemo_comment_set_template_vars_before',
            ['sender' => $this, 'transport' => $transportObject->getData(), 'transportObject' => $transportObject]
        );

        $this->templateContainer->setTemplateVars($transport);

        return $this->checkAndSend($order, $notify);
    }
}
