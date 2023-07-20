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



namespace Mirasvit\RewardsCheckout\Controller\Checkout;

use Magento\Framework\Controller\ResultFactory;

class UpdatePaymentMethodPost extends \Mirasvit\RewardsCheckout\Controller\Checkout
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $paymentMethod = $this->getRequest()->getParam('payment', '');
        $method        = !empty($paymentMethod['method']) ? $paymentMethod['method'] : '';
        $response      = $this->rewardsCheckout->updatePaymentMethod($method);

        if ($this->getRequest()->isXmlHttpRequest()) {
            echo json_encode($response);
            exit;
        }

        return $this->_goBack();
    }
}
