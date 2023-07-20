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



namespace Mirasvit\Rewards\Observer\Refund;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Pricing\PriceCurrencyInterface as CurrencyHelper;
use Magento\Sales\Model\Order\Creditmemo;
use Mirasvit\Rewards\Helper\Calculation;
use Symfony\Component\HttpFoundation\File\File;

class CreditmemoRegisterBefore implements ObserverInterface
{
    /**
     * @var Calculation
     */
    protected $calculationHelper;

    /**
     * @var CurrencyHelper
     */
    protected $currencyHelper;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @param CurrencyHelper $currencyHelper
     * @param Calculation    $calculationHelper
     * @param Http           $request
     */
    public function __construct(
        CurrencyHelper $currencyHelper,
        Calculation $calculationHelper,
        Http $request
    ) {
        $this->currencyHelper    = $currencyHelper;
        $this->calculationHelper = $calculationHelper;
        $this->request           = $request;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        /** @var Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();

        $input = $this->request->getParam('creditmemo');

        // we can refund points without money
        if (!empty($input['rewards_refunded_points']) && isset($input['rewards_base_refunded'])) {
            $pointsAmount = (int)$input['rewards_refunded_points'];

            $baseAmount = floatval($input['rewards_base_refunded']);
            $baseAmount = $creditmemo->roundPrice($baseAmount);

            if ($baseAmount > $creditmemo->getBaseGrandTotal()) {
                $baseAmount = $creditmemo->getBaseGrandTotal();
            }
            $amount = $this->calculationHelper->convertToCurrency(
                $baseAmount,
                $creditmemo->getBaseCurrencyCode(),
                $creditmemo->getOrderCurrencyCode(),
                $creditmemo->getStore()
            );
            $amount = round($amount, 2, PHP_ROUND_HALF_DOWN);

            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseAmount);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $amount);

            $creditmemo->setRewardsRefundedPoints($pointsAmount);
            $creditmemo->setRewardsBaseRefunded($baseAmount);
            $creditmemo->setRewardsRefunded($amount);

            if ($creditmemo->getBaseGrandTotal() <= 0) {
                $creditmemo->setBaseGrandTotal(0);
                $creditmemo->setGrandTotal(0);
                $creditmemo->setAllowZeroGrandTotal(true);
            }
        }
    }
}
