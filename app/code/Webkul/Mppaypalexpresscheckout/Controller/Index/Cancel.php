<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mppaypalexpresscheckout\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * Mppaypalexpresscheckout Index Cancel Controller.
 */
class Cancel extends \Magento\Customer\Controller\AbstractAccount
{
    public function execute()
    {
        /**
         * @var \Magento\Framework\Controller\Result\Redirect $resultRedirect
        */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $this->messageManager->addError(
            __("Payment has been cancelled")
        );
        return $resultRedirect->setPath('checkout/cart');
    }
}
