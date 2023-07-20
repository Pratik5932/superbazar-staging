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

use Mirasvit\Rewards\Helper\Purchase;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class AfterpayTransactionBuilder
{
    const AFTERPAY = 'afterpay';

    public static $used = false;

    private       $purchase;

    private       $session;

    public function __construct(
        Purchase $purchase,
        CheckoutSession $session
    ) {
        $this->purchase = $purchase;
        $this->session  = $session;
    }

    /**
     * @param \Magento\Payment\Gateway\Request\BuilderInterface $subject
     * @param array                                             $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuild(\Magento\Payment\Gateway\Request\BuilderInterface $subject, $result)
    {
        if (!self::$used && isset($result['payment']) && isset($result['orderlines']) &&
            strpos(($result['payment']->getMethod()), self::AFTERPAY) !== false
        ) {
            self::$used = true;
            $quoteId    = $this->session->getQuote()->getId();
            $purchase   = $this->purchase->getByQuote($quoteId);

            if ($purchase && $purchase->getSpendAmount() > 0) {
                $rewardsDiscount = $purchase->getSpendAmount() * 100 * -1;

                $result['orderlines'][] = [
                    'DISCOUNT',
                    'REWARDS_DISCOUNT',
                    1,
                    $rewardsDiscount,
                    1,
                    0,
                ];
            }
        }

        return $result;
    }
}
