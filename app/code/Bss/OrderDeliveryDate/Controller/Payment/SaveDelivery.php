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
namespace Bss\OrderDeliveryDate\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;

class SaveDelivery extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * SaveDelivery constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Helper\Cart $cartHelper
    ) {
        parent::__construct($context);
        $this->cartHelper = $cartHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $result = [];
        $params = $this->getRequest()->getParams();
        $quote = $this->cartHelper->getQuote();
        if (isset($params['shipping_arrival_date'])) {
            $quote->setShippingArrivalDate($params['shipping_arrival_date']);
        }
        if (isset($params['delivery_time_slot'])) {
            $quote->setShippingArrivalTimeslot($params['delivery_time_slot']);
        }
        if (isset($params['shipping_arrival_comments'])) {
            $quote->setShippingArrivalComments($params['shipping_arrival_comments']);
        }
        $quote->save();
        $result = [
            'status' => true
        ];
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
