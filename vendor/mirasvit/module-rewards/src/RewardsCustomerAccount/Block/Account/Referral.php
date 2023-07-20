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

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteRepository;
use Mirasvit\Rewards\Helper\Referral as ReferralHelper;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Model\ResourceModel\Referral\CollectionFactory;
use Mirasvit\Rewards\Helper\Message;

/**
 * Class Referral
 * Customer account Referral tab content
 * @package Mirasvit\RewardsCustomerAccount\Block\Account
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Referral extends \Magento\Framework\View\Element\Template
{
    private $config;

    private $context;

    private $customerFactory;

    private $customerSession;

    private $helper;

    private $localeResolver;

    private $quoteRepository;

    private $referralCollectionFactory;

    private $messageHelper;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Message $messageHelper,
        ReferralHelper $helper,
        CollectionFactory $referralCollectionFactory,
        Config $config,
        CustomerFactory $customerFactory,
        QuoteRepository $quoteRepository,
        Session $customerSession,
        ResolverInterface $localeResolver,
        Context $context,
        array $data = []
    ) {
        $this->messageHelper             = $messageHelper;
        $this->config                    = $config;
        $this->context                   = $context;
        $this->customerFactory           = $customerFactory;
        $this->customerSession           = $customerSession;
        $this->helper                    = $helper;
        $this->localeResolver            = $localeResolver;
        $this->quoteRepository           = $quoteRepository;
        $this->referralCollectionFactory = $referralCollectionFactory;

        parent::__construct($context, $data);

        $title         = $this->getPageTitle();
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');

        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($title);
        }

        $this->pageConfig->getTitle()->set($title);
    }

    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Referral\Collection
     */
    protected $_collection;

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getReferralCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'rewards.account_referral_list_toolbar_pager'
            )->setCollection(
                $this->getReferralCollection()
            );
            $this->setChild('pager', $pager);
            $this->getReferralCollection()->load();
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
     * @return \Mirasvit\Rewards\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return \Mirasvit\Rewards\Model\Referral[]|\Mirasvit\Rewards\Model\ResourceModel\Referral\Collection
     */
    public function getReferralCollection()
    {
        if (!$this->_collection) {
            $this->_collection = $this->referralCollectionFactory->create()
                ->addFieldToFilter('main_table.customer_id', $this->getCustomer()->getId())
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
     * @return string
     */
    public function getShareUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('r/' . $this->helper->getReferralLinkId());
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
    public function getAppId()
    {
        return $this->config->getFacebookAppId();
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getFacebookIsActive()
    {
        return $this->config->getFacebookIsActive();
    }

    /**
     * @return string
     */
    public function getReferralInvitationMessage()
    {
        return $this->messageHelper->processReferralInvitationVariables($this->config->getReferralInvitationMessage($this->getCustomer()->getStore()));
    }
}
