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



namespace Mirasvit\RewardsCheckout\Plugin;

use Mirasvit\Rewards\Model\Config;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;
use Magento\Quote\Model\Quote\Address;
use Mirasvit\Rewards\Helper\Purchase;
use Mirasvit\RewardsCheckout\Registry;

/**
 * @package Mirasvit\Rewards\Plugin
 * @see \Magento\Quote\Model\Quote\Address
 */
class BaseSubtotalForMinimumOrderAmountValidation
{
    private $config;

    private $rewardsPurchase;

    private $registry;

    public function __construct(
        Purchase $rewardsPurchase,
        Config $config,
        Registry $registry
    ) {
        $this->config          = $config;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->registry        = $registry;
    }

    /**
     * @param Address $quoteAddress
     * @param   float      $result
     *
     * @return float
     */
    public function afterGetBaseSubtotalWithDiscount(Address $quoteAddress, $result)
    {
        if ($this->config->getAdvancedSpendingCalculationMethod() == Method::METHOD_TOTALS
            && $this->registry->isSkipOrderAmountValidation() === false) {

            $purchase = $this->rewardsPurchase->getByQuote($quoteAddress->getQuote()->getId());

            if ($purchase && $purchase->getSpendAmount()) {
                $result -= $purchase->getSpendAmount();
            }
        }

        return $result;
    }
}
