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



namespace Mirasvit\Rewards\Plugin;

use Mirasvit\Rewards\Helper\Data;
use Mirasvit\Rewards\Helper\Purchase;
use Magento\Backend\Model\Session\Quote as BackendQuoteSession;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class StripeBillingInvoice
{
    public static $used = false;

    private       $pointData;

    private       $purchase;

    private       $backendQuoteSession;

    public function __construct(
        Purchase $purchase,
        Data $pointData,
        BackendQuoteSession $backendQuoteSession
    ) {
        $this->pointData           = $pointData;
        $this->purchase            = $purchase;
        $this->backendQuoteSession = $backendQuoteSession;
    }

    /**
     * @param \StripeIntegration\Payments\Model\Stripe\Coupon $subject
     * @param array                                           $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCouponParams(\StripeIntegration\Payments\Model\Stripe\Coupon $subject, $result)
    {
        if (!self::$used) {
            self::$used = true;
            $quote      = $this->backendQuoteSession->getQuote();
            $purchase   = $this->purchase->getByQuote($quote->getId());

            if ($purchase && $purchase->getSpendAmount() > 0) {
                $currency  = $quote->getQuoteCurrencyCode();
                $pointName = $this->pointData->getPointsName();
                $rewardsId = sha1(time() . $quote->getId());

                if ($result && !empty($result["amount_off"])) {
                    $result["amount_off"] = $result["amount_off"] + ($purchase->getSpendAmount() * 100);
                    $result["name"]       = $result["name"] . ", " . $pointName;
                }

                if (!$result) {
                    $result["id"]         = "mst_rewards_" . $rewardsId;
                    $result["amount_off"] = $purchase->getSpendAmount() * 100;
                    $result["currency"]   = $currency;
                    $result["name"]       = $pointName;
                    $result["duration"]   = "once";
                }
            }

        }

        return $result;
    }
}
