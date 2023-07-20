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

namespace Webkul\Mppaypalexpresscheckout\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul Mppaypalexpresscheckout PaymentMethodAvailable Observer Model.
 */
class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * @var \Webkul\Mppaypalexpresscheckout\Helper\Data
     */
    private $helper;

    /**
     * checkout_onepage_controller_success_action event handler.
     *
     * @param \Webkul\Mppaypalexpresscheckout\Helper\Data $helper
     */
    public function __construct(
        \Webkul\Mppaypalexpresscheckout\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($observer->getEvent()->getMethodInstance()->getCode()=="mppaypalexpresscheckout") {
                $checkResult = $observer->getEvent()->getResult();
                if (!$this->helper->checkIsAdminValidUser()) {
                    $checkResult->setData('is_available', false);
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Observer_PaymentMethodAvailable execute : ".$e->getMessage());
        }
    }
}
