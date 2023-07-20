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


namespace Mirasvit\Rewards\Model;

use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Config
{
    /**
     * @var \Magento\Framework\App\Config\MutableScopeConfigInterface
     */
    protected $mutableConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @param \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface        $scopeConfig
     * @param \Magento\Framework\Model\Context                          $context
     */
    public function __construct(
        \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\Context $context
    ) {
        $this->mutableConfig = $mutableConfig;
        $this->scopeConfig = $scopeConfig;
        $this->context = $context;
    }

    const BEHAVIOR_TRIGGER_SIGNUP            = 'signup';
    const BEHAVIOR_TRIGGER_CUSTOMER_ORDER    = 'customer_order';
    const BEHAVIOR_TRIGGER_SEND_LINK         = 'send_link';
    const BEHAVIOR_TRIGGER_NEWSLETTER_SIGNUP = 'newsletter_signup';
    const BEHAVIOR_TRIGGER_REVIEW            = 'review';
    const BEHAVIOR_TRIGGER_BIRTHDAY          = 'birthday';
    const BEHAVIOR_TRIGGER_INACTIVITY        = 'inactivity';
    const BEHAVIOR_TRIGGER_CREATED_RMA       = 'create_rma';
    const BEHAVIOR_TRIGGER_FACEBOOK_SHARE = 'facebook_share';
    const BEHAVIOR_TRIGGER_FACEBOOK_LIKE  = 'facebook_like';
    const BEHAVIOR_TRIGGER_TWITTER_TWEET  = 'twitter_tweet';
    const BEHAVIOR_TRIGGER_PINTEREST_PIN  = 'pinterest_pin';
    const BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_SIGNUP = 'referred_customer_signup';
    const BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_ORDER  = 'referred_customer_order';
    const BEHAVIOR_TRIGGER_PUSHNOTIFICATION_SIGNUP = 'pushnotification_signup';
    const BEHAVIOR_TRIGGER_AFFILIATE_CREATE = 'affiliate_create';
    const BEHAVIOR_TRIGGER_CUSTOMER_TIER_UP = 'customer_tier_up';
    const BEHAVIOR_TRIGGER_CUSTOMER_TIER_DOWN = 'customer_tier_down';
    const TYPE_PRODUCT  = 'product';
    const TYPE_CART     = 'cart';
    const TYPE_BEHAVIOR = 'behavior';
    const NOTIFICATION_POSITION_ACCOUNT_REWARDS   = 'account_rewards';
    const NOTIFICATION_POSITION_ACCOUNT_REFERRALS = 'account_referrals';
    const NOTIFICATION_POSITION_CART              = 'cart';
    const NOTIFICATION_POSITION_CHECKOUT          = 'checkout';
    const REFERRAL_STATUS_SENT       = 'sent';
    const REFERRAL_STATUS_VISITED    = 'visited';
    const REFERRAL_STATUS_SIGNUP     = 'signup';
    const REFERRAL_STATUS_MADE_ORDER = 'referred_customer_order';
    const TOTAL_TYPE_SUBTOTAL_TAX          = 'subtotal_tax';
    const TOTAL_TYPE_SUBTOTAL_TAX_SHIPPING = 'subtotal_tax_shipping';

    const EARNING_STYLE_GIVE         = 'earning_style_give';
    const EARNING_STYLE_AMOUNT_SPENT = 'earning_style_amount_spent';
    const EARNING_STYLE_QTY_SPENT    = 'earning_style_qty_spent';
    const EARNING_STYLE_AMOUNT_PRICE = 'earning_style_amount_price';

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     */
    public function getGeneralPointUnitName($store = null)
    {
        return (string)$this->scopeConfig->getValue(
            'rewards/general/point_unit_name',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return int
     */
    public function getGeneralExpiresAfterDays($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/expires_after_days',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return int
     */
    public function getGeneralActivatesAfterDays($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/activates_after_days',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsEarnAfterInvoice($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/is_earn_after_invoice',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsEarnAfterShipment($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/is_earn_after_shipment',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return array
     */
    public function getGeneralEarnInStatuses($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'rewards/general/earn_in_statuses',
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return explode(',', (string) $value);
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsRedirectAfterLogin($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'rewards/general/redirect_after_login',
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return $value;
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsCancelAfterRefund($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/is_cancel_after_refund',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsRestoreAfterRefund($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/is_restore_after_refund',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsEarnShipping($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/is_earn_shipping',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsSpendShipping($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/is_spend_shipping',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsIncludeDiscountEarning($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/is_include_discount_earning',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsIncludeTaxEarning($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/is_include_tax_earning',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsIncludeTaxSpending($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/is_include_tax_spending',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralApplyTaxAfterSpendingDiscount($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/apply_spending_discount_after_tax',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsAllowZeroOrders($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/is_allow_zero_orders',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsDisplayProductPointsAsMoney($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/is_display_product_points_as_money',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralIsDisplayMaxForConfigurableProduct($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/general/is_display_max_for_configurable_product',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getNotificationSenderEmail($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/notification/sender_email',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getNotificationBalanceUpdateEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/notification/balance_update_email_template',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getNotificationTransactionInactiveEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/notification/transaction_inactive_email_template',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getNotificationPointsExpireEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/notification/points_expire_email_template',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return int
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getNotificationSendBeforeExpiringDays($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/notification/send_before_expiring_days',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getTierCalcType($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/tier/calc_type',
            ScopeInterface::SCOPE_WEBSITE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getTierCalcForOrderInStatuses($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'rewards/tier/calc_for_order_in_statuses',
            ScopeInterface::SCOPE_WEBSITE,
            $store
        );

        return explode(',', (string) $value);
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     */
    public function getTierCalcForOrderInclShipping($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/tier/calc_for_order_incl_shipping',
            ScopeInterface::SCOPE_WEBSITE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     */
    public function getTierCalcForOrderInclDiscount($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/tier/calc_for_order_incl_discount',
            ScopeInterface::SCOPE_WEBSITE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     */
    public function getTierCalcForOrderInclTax($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/tier/calc_for_order_incl_tax',
            ScopeInterface::SCOPE_WEBSITE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return int
     */
    public function getTierCalculationPeriod($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/tier/period',
            ScopeInterface::SCOPE_WEBSITE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     */
    public function getTierAutoMoveDown($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/tier/auto_move_down',
            ScopeInterface::SCOPE_WEBSITE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     */
    public function getReferralIsActive($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/referral/is_active',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     */
    public function getReferralInvitationEmailTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/referral/invitation_email_template',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     */
    public function getAddThisWidgetCode($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/mst_rewards_widget/addthis_code',
            ScopeInterface::SCOPE_WEBSITE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAdvancedSettingsCustomRuleList($store = null)
    {
        $list = $this->scopeConfig->getValue(
            'rewards/advanced_settings/custom_rules_list',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );

        if (empty($list)) {
            return [];
        }

        $rules = [];
        foreach (explode(PHP_EOL, $list) as $rule) {
            if (empty($rule)) {
                continue;
            }
            list($code, $name) = explode(',', (string) $rule, 2);
            $rules[] = [
                'code' => $code,
                'name' => trim((string) $name),
            ];
        }

        return $rules;
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAdvancedSpendingCalculationMethod($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/advanced_settings/spending_calculation_method',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAdvancedEarningRoundingStype($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/advanced_settings/earning_points_rounding',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAdvancedApplyStyles($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/advanced_settings/apply_styles',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDisplayOptionsIsShowPointsMenu($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/display_options/is_show_points_menu',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     *
     * @return string
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCustomerGroups($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/display_options/show_points_menu_customer_groups',
            ScopeInterface::SCOPE_WEBSITE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDisplayOptionsIsShowPointsOnProductPage($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/display_options/is_show_points_on_product_page',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDisplayOptionsIsShowPointsOnFrontend($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/display_options/is_show_points_on_frontend',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     */
    public function getDisplayOptionsCheckoutNotification($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/display_options/checkout_notification',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     */
    public function getDisplayOptionsLogo($store = null)
    {
        $enabled = $this->scopeConfig->getValue(
            'rewards/display_options/enable_logo',
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $url = $this->scopeConfig->getValue(
            'rewards/display_options/rewards_logo',
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return $enabled ?  $url : '';
    }

    /**
     * By default we must allow including discounts in total
     * otherwise we don't apply discount on the last step of paypal express checkout (in the result order).
     *
     * @var bool
     */
    protected $_calculateTotalFlag = true;

    /**
     * @var bool
     */
    private $disableRewardsCalculation = true;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCalculateTotalFlag()
    {
        return $this->_calculateTotalFlag;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setCalculateTotalFlag($value)
    {
        $this->_calculateTotalFlag = $value;

        return $this;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDisableRewardsCalculation()
    {
        return $this->disableRewardsCalculation;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setDisableRewardsCalculation($value)
    {
        $this->disableRewardsCalculation = $value;

        return $this;
    }

    /**
     * @var bool
     */
    protected $_spendTotalAppliedFlag = false;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getSpendTotalAppliedFlag()
    {
        return $this->_spendTotalAppliedFlag;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setSpendTotalAppliedFlag($value)
    {
        $this->_spendTotalAppliedFlag = $value;

        return $this;
    }

    /**
     * @var bool
     */
    protected $_quoteSaveFlag = false;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getQuoteSaveFlag()
    {
        return $this->_quoteSaveFlag;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setQuoteSaveFlag($value)
    {
        $this->_quoteSaveFlag = $value;

        return $this;
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getFacebookApiVersion($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewardssocial/facebook/api_version',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getFacebookIsActive($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewardssocial/facebook/is_active',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getFacebookShowShare($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewardssocial/facebook/show_fb_share',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     */
    public function getFacebookAppId($store = null)
    {
        $appId = $this->scopeConfig->getValue(
            'rewardssocial/facebook/app_id',
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if (!$appId) {
            $appId = $this->scopeConfig->getValue(
                'rewardssocial/facebook/application_id',
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }

        return $appId;
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getTwitterIsActive($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewardssocial/twitter/is_active',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     */
    public function getTwitterToken($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewardssocial/twitter/token',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getTwitterConsumerKey($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewardssocial/twitter/consumer_key',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getTwitterConsumerSecret($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewardssocial/twitter/consumer_secret',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getTwitterIsTokenActive($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewardssocial/twitter/token_status',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string      $value
     * @param null|\Magento\Store\Model\Website $store
     *
     * @return void
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function setTwitterIsTokenActive($value, $store = null)
    {
        $this->mutableConfig->setValue(
            'rewardssocial/twitter/token_status',
            $value,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string      $value
     * @param null|\Magento\Store\Model\Website $store
     *
     * @return void
     */
    public function setTwitterToken($value, $store = null)
    {
        $this->mutableConfig->setValue(
            'rewardssocial/twitter/token',
            $value,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getPinterestIsActive($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewardssocial/pinterest/is_active',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAddthisIsActive($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewardssocial/addthis/is_active',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|\Magento\Store\Model\Website $store
     * @return string
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAddthisCode($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewardssocial/addthis/code',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return bool
     */
    public function isTaxjarEnabled()
    {
        /** @noinspection PhpUndefinedNamespaceInspection */
        if (!class_exists(\Taxjar\SalesTax\Model\Configuration::class)) {
            return false;
        }

        /** @noinspection PhpUndefinedNamespaceInspection */
        return $this->scopeConfig->getValue(\Taxjar\SalesTax\Model\Configuration::TAXJAR_ENABLED);
    }

    /**
     * @param null|\Magento\Store\Model\Store $store
     *
     * @return string
     */
    public function getReferralInvitationMessage($store = null)
    {
        return $this->scopeConfig->getValue(
            'rewards/display_options/referral_invitation_message',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
