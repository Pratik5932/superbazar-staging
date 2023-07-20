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



namespace Mirasvit\Rewards\Observer\Quote;

use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Rewards\Helper\Purchase;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

/**
 * Adds rewards discount to quote object
 */
class QuoteLoadAfter implements ObserverInterface
{
    private $config;

    private $purchase;

    public function __construct(
        Config $config,
        Purchase $purchase
    ) {
        $this->config   = $config;
        $this->purchase = $purchase;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        /** @var \Magento\Quote\Model\Quote\Item $quote */
        $quote = $observer->getDataObject();
        if ($this->config->getAdvancedSpendingCalculationMethod() != Method::METHOD_ITEMS &&
            $purchase = $this->purchase->getByQuote($quote->getId())
        ) {
            $quote->setItemsRewardsDiscount($purchase->getSpendAmount());
            $quote->setBaseItemsRewardsDiscount($purchase->getBaseSpendAmount());
        }
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
