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


namespace Mirasvit\RewardsCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Mirasvit\Rewards\Helper\Purchase;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

/**
 * Used for PayPal checkout.
 */
class AddRewardsDiscountOnCartCollectItems implements ObserverInterface
{
    private $config;

    private $purchaseHelper;

    static $totalCalculated = false;

    protected $moduleManager;

    public function __construct(
        Config $config,
        Purchase $purchaseHelper,
        ModuleManager $moduleManager
    ) {
        $this->config         = $config;
        $this->purchaseHelper = $purchaseHelper;
        $this->moduleManager  = $moduleManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (self::$totalCalculated && $this->moduleManager->isEnabled('MSP_DevTools')) {
          return;
        }

        /** @var \Magento\Paypal\Model\Cart $cart */
        $cart = $observer->getCart();

        $quoteId = $cart->getSalesModel()->getDataUsingMethod('entity_id');
        if (!$quoteId) {
            $quoteId = $cart->getSalesModel()->getDataUsingMethod('quote_id');
        }
        $purchase = $this->purchaseHelper->getByQuote($quoteId);

        if ($purchase && $purchase->getSpendAmount() > 0 &&
            $this->config->getAdvancedSpendingCalculationMethod() == Method::METHOD_TOTALS
        ) {
            $cart->addCustomItem(
                'Rewards Discount',
                1,
                -$purchase->getBaseSpendAmount()
            );
        }
        self::$totalCalculated = true;
    }
}
