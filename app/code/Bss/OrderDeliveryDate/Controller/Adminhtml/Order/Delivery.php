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
namespace Bss\OrderDeliveryDate\Controller\Adminhtml\Order;

class Delivery extends \Magento\Sales\Controller\Adminhtml\Order
{
    const ADMIN_RESOURCE = 'Magento_Sales::actions_edit';

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $this->_coreRegistry->register('order_id', $orderId);
            $resultPage = $this->resultPageFactory->create();
            $deliveryFormContainer = $resultPage->getLayout()->getBlock('sales_order_delivery.form.container');
            if ($deliveryFormContainer) {
                $deliveryFormContainer->getChildBlock('form')->setDisplayVatValidationButton(false);
            }

            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath('sales/*/');
        }
    }
}
