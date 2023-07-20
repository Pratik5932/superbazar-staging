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



namespace Mirasvit\RewardsCustomerAccount\Block\Account;

use Magento\Framework\Url;
use Mirasvit\RewardsCustomerAccount\Helper\Account\Rule as AccountHelperRule;
use Mirasvit\Rewards\Helper\Referral as ReferralHelper;
use Mirasvit\Rewards\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Helper\Balance;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;
/**
 * Class Listing
 * Customer account main block. Uses as parent on history, index and share pages
 * @package Mirasvit\RewardsCustomerAccount\Block\Account
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Listing extends \Magento\Framework\View\Element\Template
{
    private $frontendUrlBuilder;

    private $transactionCollectionFactory;

    private $config;

    private $rewardsBalance;

    private $customerFactory;

    private $customerSession;

    private $context;

    private $accountRuleHelper;

    private $referralHelper;

    private $_collection;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Url $frontendUrlBuilder,
        AccountHelperRule $accountRuleHelper,
        ReferralHelper $referralHelper,
        TransactionCollectionFactory $transactionCollectionFactory,
        Config $config,
        Balance $rewardsBalance,
        CustomerFactory $customerFactory,
        CustomerSession $customerSession,
        Context $context,
        array $data = []
    ) {
        $this->frontendUrlBuilder           = $frontendUrlBuilder;
        $this->accountRuleHelper            = $accountRuleHelper;
        $this->referralHelper               = $referralHelper;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->config                       = $config;
        $this->rewardsBalance               = $rewardsBalance;
        $this->customerFactory              = $customerFactory;
        $this->customerSession              = $customerSession;
        $this->context                      = $context;

        parent::__construct($context, $data);

        $title         = $this->getPageTitle();
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');

        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($title);
        }

        $this->pageConfig->getTitle()->set($title);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getTransactionCollection()) {
            $this->pageConfig->getTitle()->set(__('My Reward Points'));
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'rewards.account_list_toolbar_pager'
            )->setData(
                'show_amounts', false
            )->setShowPerPage(
                false
            )->setCollection(
                $this->getTransactionCollection()
            );
            $this->setChild('pager', $pager);
            $this->getTransactionCollection()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getShareUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('r/' . $this->referralHelper->getReferralLinkId());
    }

    /**
     * @return \Mirasvit\Rewards\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return \Mirasvit\Rewards\Model\ResourceModel\Transaction\Collection|\Mirasvit\Rewards\Model\Transaction[]
     */
    public function getTransactionCollection()
    {
        if (!$this->_collection) {
            $this->_collection = $this->transactionCollectionFactory->create()
                ->addFieldToFilter('customer_id', $this->getCustomer()->getId())
                ->setOrder('created_at', 'desc');
        }

        return $this->_collection;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer()
    {
        return $this->customerFactory->create()->load($this->customerSession->getCustomerId());
    }

    /**
     * @return int
     */
    public function getBalancePoints()
    {
        return $this->rewardsBalance->getBalancePoints($this->getCustomer());
    }

    /**
     * @return int
     */
    public function isCustomerSubscribed()
    {
        return (bool)$this->getCustomer()->getRewardsSubscription();
    }

    /**
     * @return string
     */
    public function getUnsubscribeUrl()
    {
        if (is_object($this->getCustomer())) {

            $referralCode = $this->referralHelper->getReferralLinkId();

            return $this->frontendUrlBuilder->getUrl('rewards/account/unsubscribe',
                ['code' => $referralCode, '_nosid' => true]
            );
        }

        return '';
    }

    /**
     * @return string
     */
    public function getSubscribeUrl()
    {
        return $this->getUrl('rewards/account/subscribe');
    }

    /**
     * @return \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\Collection
     */
    public function getDisplayEarnRules()
    {
        return $this->accountRuleHelper->getDisplayEarnRules();
    }

    /**
     * @return \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\Collection
     */
    public function getDisplaySocialRules()
    {
        return $this->accountRuleHelper->getDisplaySocialRules();
    }

    /**
     * @return \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\Collection
     */
    public function getDisplaySpendRules()
    {
        return $this->accountRuleHelper->getDisplaySpendRules();
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getFacebookIsActive()
    {
        return $this->isShareActive() || $this->isLikeActive();
    }

    /**
     * Get locale code for social buttons
     * @return string
     */
    public function getLocaleCode()
    {
        $locale = $this->context->getStoreManager()->getStore()->getLocaleCode();
        if (!$locale) {
            $locale = 'en';
        }

        return $locale;
    }

    /**
     * @return string
     */
    public function getExpirationEnabled()
    {
        return $this->config->getGeneralExpiresAfterDays();
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->config->getFacebookAppId();
    }

    /**
     * @return string
     */
    public function getFbApiVersion()
    {
        $version = $this->config->getFacebookApiVersion();

        return $version ? : 'v3.1';
    }

    /**
     * @return bool
     */
    public function isShareActive()
    {
        return $this->config->getFacebookShowShare();
    }

    /**
     * @return bool
     */
    public function isLikeActive()
    {
        return $this->config->getFacebookIsActive();
    }

    /**
     * @return bool
     */
    public function isTweetActive()
    {
        return $this->config->getTwitterIsActive();
    }

    /**
     * @return bool
     */
    public function isReferralActive()
    {
        return $this->config->getReferralIsActive();
    }

    /**
     * Check if at least one social button is active
     * @return bool
     */
    public function isSocialActive()
    {
        return $this->isTweetActive() || $this->isShareActive() || $this->isLikeActive();
    }

    /**
     * Encode url
     *
     * @param string $url
     *
     * @return string
     */
    public function getCurrentEncodedUrl($url)
    {
        return urlencode($url);
    }

    /**
     * Get product/category url for share buttons
     * @return string
     */
    public function getCurrentUrl()
    {
        $url = $this->getBaseUrl();

        return $url;
    }
}
