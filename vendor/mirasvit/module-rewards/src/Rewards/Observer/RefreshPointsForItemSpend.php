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



namespace Mirasvit\Rewards\Observer;

use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;
use Magento\Checkout\Model\CartFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Model\Context;
use Mirasvit\Rewards\Helper\Purchase;
use Mirasvit\Rewards\Helper\Referral;
use Mirasvit\Rewards\Model\Config;

class RefreshPointsForItemSpend implements \Magento\Framework\Event\ObserverInterface
{
    private $cartFactory;

    private $config;

    private $context;

    private $rewardsPurchase;

    private $rewardsReferral;

    private $sessionFactory;

    public function __construct(
        SessionFactory $sessionFactory,
        CartFactory $cartFactory,
        Context $context,
        Purchase $rewardsPurchase,
        Referral $rewardsReferral,
        Config $config
    ) {
        $this->cartFactory     = $cartFactory;
        $this->config          = $config;
        $this->context         = $context;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->rewardsReferral = $rewardsReferral;
        $this->sessionFactory  = $sessionFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->getAdvancedSpendingCalculationMethod() != Method::METHOD_ITEMS) {
            return;
        }

        if (!method_exists($observer->getControllerAction(), 'getRequest')) {
            return;
        }

        $uri = $observer->getControllerAction()->getRequest()->getRequestUri();

        if ($this->context->getAppState()->getAreaCode() == 'frontend' &&
            strpos($uri, 'paypal') !== false
        ) {
            return;
        }

        if ($this->context->getAppState()->getAreaCode() == 'frontend' &&
            strpos($uri, 'checkout') === false
        ) {
            $this->config->setDisableRewardsCalculation(false);

            return;
        }

        if ($this->context->getAppState()->getAreaCode() == 'adminhtml' &&
            strpos($uri, 'sales/order_create/save') !== false
        ) {
            return;
        }

        $quote = $this->cartFactory->create()->getQuote();
        if (!$quote || !$quote->getId()) {
            $this->config->setDisableRewardsCalculation(false);

            return;
        }

        //this does not calculate quote correctly
        if (strpos($uri, '/checkout/cart/add/') !== false ||
            strpos($uri, '/checkout/cart/addgroup/') !== false ||
            strpos($uri, '/checkout/cart/updatePost/') !== false
        ) {
            $this->config->setDisableRewardsCalculation(false);

            return;
        }

        if (
            $this->context->getAppState()->getAreaCode() == 'frontend' &&
            !($this->sessionFactory->create()->isLoggedIn() && $this->sessionFactory->create()->getId()) &&
            strpos($uri, '/checkout/cart/delete/') !== false
        ) {
            $this->config->setDisableRewardsCalculation(false);

            return;
        }

        if (strpos($uri, '/checkout/sidebar/removeItem/') !== false) {
            $this->config->setDisableRewardsCalculation(false);

            return;
        }

        if (strpos($uri, '/checkout/sidebar/updateItemQty') !== false) {
            $this->config->setDisableRewardsCalculation(false);

            return;
        }

        //this does not calculate quote correctly with firecheckout
        if (strpos($uri, '/firecheckout/') !== false) {
            $this->config->setDisableRewardsCalculation(false);

            return;
        }

        //this does not calculate quote correctly with gomage
        if (strpos($uri, '/gomage_checkout/onepage/save/') !== false) {
            $this->config->setDisableRewardsCalculation(false);

            return;
        }

        if (strpos($uri, '/checkout/onepage/success/') !== false) {
            $this->config->setDisableRewardsCalculation(false);

            return;
        }

        if (strpos($uri, '/rewards') === 0) {
            $this->config->setDisableRewardsCalculation(false);

            return;
        }

        $this->refreshPoints($quote);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param bool                       $force
     * @param bool                       $freezeSpendPoints
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function refreshPoints($quote, $force = false, $freezeSpendPoints = false)
    {
        if ($quote->getIsPurchaseSave() && !$force) {
            return;
        }

        if (!$purchase = $this->rewardsPurchase->getByQuote($quote)) {
            return;
        }

        if (
            ($this->context->getAppState()->getAreaCode() == 'frontend' &&
                !($this->sessionFactory->create()->isLoggedIn() && $this->sessionFactory->create()->getId())) ||
            !$quote->getAllItems()
        ) {
            $purchase->setSpendPoints(0);
        }

        $purchase->setQuote($quote);
        $purchase->setFreezeSpendPoints($freezeSpendPoints);
        $purchase->refreshPointsNumber($force);
        $purchase->save();

        $this->rewardsReferral->rememberReferal($quote);
    }
}
