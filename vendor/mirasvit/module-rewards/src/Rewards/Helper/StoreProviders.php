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


namespace Mirasvit\Rewards\Helper;


class StoreProviders
{
    private $storeManager;
    private $customerFactory;
    private $appState;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\State $appState
    ) {
        $this->storeManager    = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->appState        = $appState;
    }

    /**
     * @param bool|int|\Magento\Customer\Model\Customer $customerId
     * @return null|\Magento\Customer\Model\Customer
     */
    public function getCustomer($customerId)
    {
        if (is_object($customerId)) {
            $customerId = $customerId->getId();
        }
        if (!$customerId && $this->appState->getAreaCode() == 'frontend') {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->get('Magento\Customer\Model\Session');
            $customerId = $customerSession->getCustomerId();
            if (!$customerId) {
                return null;
            }
        }
        if ($customerId) {
            $customer = $this->customerFactory->create()->load($customerId);
            if ($customer->getId()) {
                return $customer;
            }
        }
        return null;
    }

    public function getWebsiteId()
    {
        return $this->storeManager->getWebsite()->getId();
    }
}