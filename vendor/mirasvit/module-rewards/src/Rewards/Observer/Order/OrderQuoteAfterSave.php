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



namespace Mirasvit\Rewards\Observer\Order;

class OrderQuoteAfterSave
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        /** @var \Magento\Quote\Model\Quote $quote */
        /** @todo remove later */
//        $quote = $observer->getQuote();
//        if (!$quote->getIsVirtual() && empty(trim($quote->getShippingAddress()->getShippingMethod(), '_'))) {
//            return;
//        }
//        $this->refreshPoints($quote);
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
