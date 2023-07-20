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



namespace Mirasvit\RewardsBehavior\Block\Buttons;

/**
 * Class AbstractButtons
 *
 * Parent class for all cosial buttons
 *
 * @package Mirasvit\RewardsBehavior\Block\Buttons
 */
class AbstractButtons extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    protected $config;

    public function __construct(
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->config          = $config;
        $this->registry        = $registry;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->productFactory  = $productFactory;
        $this->context         = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Rewards\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get locale for social buttons
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
     * Get product/category url for share buttons
     * @return string
     */
    public function getCurrentUrl()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Catalog\Model\Layer\Resolver $resolver */
        $resolver = $objectManager->get('Magento\Catalog\Model\Layer\Resolver');
        $category = $resolver->get()->getCurrentCategory();
        if ($product = $this->registry->registry('current_product')) {
            $url = $this->getProductUrl($product);
        } elseif ($category || $this->registry->registry('current_category')) {
            $url = $this->_urlBuilder->getCurrentUrl();
        } elseif ($id = $this->getRequest()->getParam('id')) {
            $product = $this->productFactory->create()->load($id);
            $url = $this->getProductUrl($product);
        }

        $pos = strpos($url, '?__SID');
        if ($pos !== false) {
            $url = substr($url, 0, $pos);
        }

        return $url;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        $name = '';
        if ($product = $this->registry->registry('current_product')) {
            $name = $product->getName();
        } elseif ($id = $this->getRequest()->getParam('id')) {
            $product = $this->productFactory->create()->load($id);
            $name = $product->getName();
        }

        return $name;
    }

    /**
     * @return string
     */
    public function getFbApiVersion()
    {
        $version = $this->config->getFacebookApiVersion();

        return $version ?: 'v3.1';
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    private function getProductUrl($product)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->context->getStoreManager()->getStore();
        if ($store->isUseStoreInUrl()) {
            $url = $product->getUrlInStore(['_use_rewrite' => (bool)$product->getUrlKey()]);
        } else {
            $url = $product->getProductUrl();
        }

        return $url;
    }

    /**
     * Encode url
     *
     * @param string $url
     * @return string
     */
    public function getCurrentEncodedUrl($url)
    {
        return urlencode($url);
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function _getCustomer()
    {
        return $this->customerFactory->create()->load($this->customerSession->getCustomerId());
    }

    /**
     * @return bool
     */
    public function isAuthorized()
    {
        $customer = $this->_getCustomer();
        if ($customer && $customer->getId() > 0) {
            return true;
        }
    }
}
