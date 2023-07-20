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



namespace Mirasvit\RewardsCatalog\Block\Product\View;

/**
 * Class Points
 * @package Mirasvit\Rewards\Block\Product\View
 * @deprecated
 */
class Points extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;
    /**
     * @var \Mirasvit\RewardsCatalog\Helper\Earn
     */
    private $earnHelper;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Mirasvit\RewardsCatalog\Helper\Earn $earnHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->earnHelper      = $earnHelper;
        $this->customerSession = $customerSession;
        $this->storeManager    = $context->getStoreManager();
        $this->registry        = $registry;
        $this->context         = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return int
     */
    public function getProductPoints()
    {
        $customer = $this->customerSession->getCustomer();
        $websiteId = $this->storeManager->getWebsite()->getId();

        return $this->earnHelper->getProductPoints($this->getProduct(), $customer, $websiteId);
    }
}
