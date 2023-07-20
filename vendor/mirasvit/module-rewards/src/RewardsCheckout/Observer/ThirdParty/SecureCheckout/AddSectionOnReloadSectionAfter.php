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


namespace Mirasvit\RewardsCheckout\Observer\ThirdParty\SecureCheckout;

use Magento\Framework\Event\ObserverInterface;

class AddSectionOnReloadSectionAfter implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $container = $observer->getEvent()->getContainer();
        $sections = $container->getSections();
        $sections['ApplyPointsSecureCheckoutPost'] = ['payment_method', 'review_cart', 'review_coupon'];
        $container->setSections($sections);
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
