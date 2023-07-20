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



namespace Mirasvit\RewardsApi\Service;

use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Rewards\Helper\ReferralFactory as ReferralHelper;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\CustomerFactory;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Model\ReferralFactory;
use Mirasvit\Rewards\Model\ResourceModel\ReferralLink\CollectionFactory;

class Referral
{
    private $storeManager;

    private $checkoutSession;

    private $customerFactory;

    private $referralHelper;

    private $referralFactory;

    private $referralLinkCollectionFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        Session $checkoutSession,
        CustomerFactory $customerFactory,
        ReferralHelper $referralHelper,
        ReferralFactory $referralFactory,
        CollectionFactory $referralLinkCollectionFactory
    ) {
        $this->storeManager                  = $storeManager;
        $this->checkoutSession               = $checkoutSession;
        $this->customerFactory               = $customerFactory;
        $this->referralHelper                = $referralHelper;
        $this->referralFactory               = $referralFactory;
        $this->referralLinkCollectionFactory = $referralLinkCollectionFactory;
    }

    /**
     * @param int $customerId
     *
     * @return string
     */
    public function getReferralCode($customerId)
    {
        return $this->referralHelper->create()->getReferralLinkId($customerId);
    }

    /**
     * @param int    $customerId
     * @param string $code
     * @param int    $storeId
     *
     * @return int
     */
    public function addReferral($customerId, $code, $storeId)
    {
        $result             = 0;
        $referrerCustomerId = $this->getCustomerIdByCode($code);

        if ($referrerCustomerId) {
            $referral = $this->referralFactory->create()
                ->setCustomerId($referrerCustomerId)
                ->setNewCustomerId($customerId)
                ->setStatus(Config::REFERRAL_STATUS_SIGNUP)
                ->setStoreId($storeId)
                ->save();
            $result   = $referral->getId();
        }

        return $result;
    }

    /**
     * @param int      $customerId
     * @param int      $storeId
     * @param string   $message
     * @param string[] $friendMail
     *
     * @return void
     */
    public function sendReferralMessage($customerId, $storeId, $message, $friendMail)
    {
        $customer       = $this->customerFactory->create()->load($customerId);
        $websiteId      = $this->storeManager->getStore($storeId)->getWebsiteId();
        $referralHelper = $this->referralHelper->create();
        $referralHelper->frontendPost($customer, $friendMail, $message, $websiteId);
    }

    /**
     * @param string $code
     * @param int    $quoteId
     * @param int    $storeId
     *
     * @return int
     */
    public function addGuestReferral($code, $quoteId, $storeId)
    {
        $result     = 0;
        $customerId = $this->getCustomerIdByCode($code);

        if ($customerId) {
            $referral = $this->referralFactory->create()
                ->setCustomerId($customerId)
                ->setStatus(Config::REFERRAL_STATUS_VISITED)
                ->setStoreId($storeId)
                ->setQuoteId($quoteId)
                ->save();

            $result = $referral->getId();

            $this->checkoutSession->setReferral($referral->getId());
        }

        return $result;
    }

    /**
     * @param string $code
     *
     * @return int
     */
    private function getCustomerIdByCode($code)
    {
        $link = $this->referralLinkCollectionFactory->create()
            ->addFieldToFilter('referral_link', $code)
            ->getFirstItem();

        return $link->getCustomerId() ? : 0;
    }
}
