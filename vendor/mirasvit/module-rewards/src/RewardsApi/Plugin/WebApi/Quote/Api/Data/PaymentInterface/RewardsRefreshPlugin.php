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


namespace Mirasvit\RewardsApi\Plugin\WebApi\Quote\Api\Data\PaymentInterface;

use Magento\Quote\Api\Data\PaymentInterface;
use Mirasvit\Rewards\Helper\Purchase;

/**
 * @package Mirasvit\Rewards\Plugin\WebApi
 */
class RewardsRefreshPlugin
{
    /**
     * @var Purchase
     */
    private $purchaseHelper;

    public function __construct(Purchase $purchaseHelper)
    {
        $this->purchaseHelper = $purchaseHelper;
    }

    /**
     * @param PaymentInterface $payment
     * @param \callable        $proceed
     * @param array            $data
     * @return PaymentInterface
     * @throws \Exception
     */
    public function aroundImportData(PaymentInterface $payment, $proceed, $data)
    {
        $result = $proceed($data);
        return $result;

        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $payment->getQuote();
        if ($quote->getTotalsCollectedFlag() !== false || !$purchase = $this->purchaseHelper->getByQuote($quote)) {
            return $result;
        }

        $purchase->setQuote($quote);
        $purchase->setFreezeSpendPoints(false);
//        $purchase->refreshPointsNumber(true);
        $purchase->save();
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);

        return $result;
    }
}