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


namespace Mirasvit\RewardsApi\Plugin\Mirasvit\Credit\Api\CreditManagement;

use Magento\Quote\Api\CartRepositoryInterface;
use Mirasvit\Credit\Api\CreditManagementInterface;
use Mirasvit\Rewards\Helper\Purchase as PurchaseHelper;

/**
 * Update rewards points on store credit applies. We need this to update js totals in checkout
 *
 * @package Mirasvit\Rewards\Plugin
 */
class UpdateRewardsPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var PurchaseHelper
     */
    private $purchaseHelper;

    public function __construct(
        PurchaseHelper $purchaseHelper,
        CartRepositoryInterface $cartRepository
    ) {
        $this->purchaseHelper = $purchaseHelper;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param CreditManagementInterface $creditManagement
     * @param \callable                 $proceed
     * @param int                       $cartId
     * @param float                     $creditAmount
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundApply(CreditManagementInterface $creditManagement, $proceed, $cartId, $creditAmount)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $returnValue = $proceed($cartId, $creditAmount);

        $quote = $this->cartRepository->get($cartId);
        $purchase = $this->purchaseHelper->getByQuote($quote);
        if ($purchase) {
//            $purchase->refreshPointsNumber(true);
        }

        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);

        return $returnValue;
    }
}