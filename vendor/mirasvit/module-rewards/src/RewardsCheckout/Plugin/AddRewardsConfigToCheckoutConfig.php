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

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Add to the js checkout object rewards information
 */
class AddRewardsConfigToCheckoutConfig
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilber;

    /**
     * @var \Mirasvit\Rewards\Helper\Message
     */
    private $messageHelper;

    /**
     * @var \Mirasvit\Rewards\Helper\Rule\Notification
     */
    private $rewardsNotification;

    /**
     * @var \Mirasvit\Rewards\Helper\Purchase
     */
    private $rewardsPurchase;

    /**
     * @var \Mirasvit\Rewards\Helper\Data
     */
    private $rewardsData;

    /**
     * @var \Mirasvit\Rewards\Model\Config
     */
    private $config;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    private $productMetadata;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilber,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Mirasvit\Rewards\Helper\Message $messageHelper,
        \Mirasvit\Rewards\Helper\Rule\Notification $rewardsNotification,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Mirasvit\Rewards\Model\Config $config,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->urlBuilber          = $urlBuilber;
        $this->productMetadata     = $productMetadata;
        $this->messageHelper       = $messageHelper;
        $this->rewardsNotification = $rewardsNotification;
        $this->rewardsPurchase     = $rewardsPurchase;
        $this->rewardsData         = $rewardsData;
        $this->config              = $config;
        $this->checkoutSession     = $checkoutSession;
        $this->customerSession     = $customerSession;
        $this->scopeConfig         = $scopeConfig;
        $this->moduleManager       = $moduleManager;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array                                         $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        $storeId = null;

        $result['chechoutRewardsIsShow']         = 0;
        $result['chechoutRewardsPoints']         = 0;
        $result['chechoutRewardsPointsMax']      = 0;
        $result['chechoutRewardsPointsSpend']    = 0;
        $result['chechoutRewardsPointsAvailble'] = 0;
        $result['chechoutRewardsPointsName']     = $this->rewardsData->getPointsName();
        $result['chechoutRewardsIsGuest']        = !$this->customerSession->isLoggedIn();
        $result['rewardsLogo']                   = $this->rewardsData->getLogoHtml();

        if (($purchase = $this->rewardsPurchase->getPurchase()) && $purchase->getQuote()->getCustomerId()) {

            $quote = $purchase->getQuote();

            $result['chechoutRewardsNotificationMessages'] = [];

            if ($purchase->getEarnPoints()) {
                $result['chechoutRewardsPoints'] = $this->rewardsData->formatPoints($purchase->getEarnPoints());
            }

            if ($point = $purchase->getSpendPoints()) {
                $result['chechoutRewardsPointsSpend'] = $this->rewardsData->formatPoints($point);
                $result['chechoutRewardsPointsUsed']  = $point;
            }

            $result['chechoutRewardsPointsAvailble'] = $this->rewardsData->formatPoints(
                $purchase->getCustomerBalancePoints($quote->getCustomerId())
            );
            $result['chechoutRewardsPointsMax']      = $purchase->getMaxPointsNumberToSpent();
            $result['chechoutRewardsIsShow']         = $result['chechoutRewardsPointsMax'] > 0;
        } else {
            $quote = $this->checkoutSession->getQuote();
        }

        $result['chechoutRewardsApplayPointsUrl'] = $this->urlBuilber->getUrl(
            'rewards_checkout/checkout/applyPointsPost', ['_secure' => true]
        );

        if (!$quote->getIsMultiShipping() && !$quote->isMultipleShippingAddresses()) {
            $result['chechoutRewardsPaymentMethodPointsUrl'] = $this->urlBuilber->getUrl(
                'rewards_checkout/checkout/updatePaymentMethodPost', ['_secure' => true]
            );
        }

        if ($quote) {
            $storeId = $quote->getStoreId();
        }

        $message = $this->config->getDisplayOptionsCheckoutNotification($storeId);
        if ($message) {
            $result['rewardsCheckoutNotification'] = $this->messageHelper->processCheckoutNotificationVariables(
                $message
            );
        } else {
            $result['rewardsCheckoutNotification'] = '';
        }

        $result['isMageplazaOsc']               = (int)$this->moduleManager->isEnabled('Mageplaza_Osc');
        $result['isRokanthemesOpCheckout']      = (int)$this->isRokanthemesOpCheckout();
        $result['isOnestepcheckoutIosc']        = (int)$this->isOnestepcheckoutIosc();
        $result['isBssOnestepCheckout']         = (int)$this->moduleManager->isEnabled('Bss_OneStepCheckout');
        $result['isAheadOnestepCheckout']       = (int)$this->moduleManager->isEnabled('Aheadworks_OneStepCheckout');
        $result['isAmastyShippingTableRates']   = (int)$this->moduleManager->isEnabled('Amasty_ShippingTableRates');
        $result['isMageplazaCurrencyFormatter'] = (int)$this->moduleManager->isEnabled('Mageplaza_CurrencyFormatter');
        $result['isMagestyAppsPaymentFee']      = (int)$this->moduleManager->isEnabled('MagestyApps_PaymentFee');


        // in m2.1.0 on page load need to call collect totals in js to display rewards discount
        $result['updateTotalsM21']       = version_compare($this->productMetadata->getVersion(), '2.2.0', '<');
        $result['isShippingMinOrderSet'] = $this->isShippingMinOrderSet($quote);

        return $result;
    }

    /**
     *
     * @return bool
     */
    protected function isRokanthemesOpCheckout()
    {
        return ($this->moduleManager->isEnabled('Rokanthemes_OpCheckout')
            || $this->moduleManager->isEnabled('Rokanthemes_OnePageCheckout'));
    }

    /**
     *
     * @return bool
     */
    protected function isOnestepcheckoutIosc()
    {
        return ($this->moduleManager->isEnabled('Onestepcheckout_Iosc'));
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return bool
     */
    protected function isShippingMinOrderSet($quote)
    {
        $enabled = $this->scopeConfig->getValue(
            'carriers/freeshipping/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $quote->getStore()->getWebsiteId()
        );
        $amount  = $this->scopeConfig->getValue(
            'carriers/freeshipping/free_shipping_subtotal',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $quote->getStore()->getWebsiteId()
        );

        return $enabled && $amount > 0;
    }
}
