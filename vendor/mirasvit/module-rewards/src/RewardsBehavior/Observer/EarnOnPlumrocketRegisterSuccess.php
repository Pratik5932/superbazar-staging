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



namespace Mirasvit\RewardsBehavior\Observer;

use Magento\Framework\Event\ObserverInterface;

class EarnOnPlumrocketRegisterSuccess extends EarnOnCustomerRegisterSuccess implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__ . ':' . __METHOD__);
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            \Magento\Framework\Profiler::stop(__CLASS__ . ':' . __METHOD__);

            return;
        }

        $controller = $observer->getEvent()->getAccountController();
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer       = $observer->getEvent()->getCustomer();

        $amastyObserver = $observer->getEvent()->getAmastyCheckoutRegister();

        if ($amastyObserver || strpos(get_class($controller), 'SocialLoginPro') === false) {
            \Magento\Framework\Profiler::stop(__CLASS__ . ':' . __METHOD__);

            return;
        }

        $this->applyRules($customer);
        \Magento\Framework\Profiler::stop(__CLASS__ . ':' . __METHOD__);
    }
}
