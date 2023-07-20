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



namespace Mirasvit\RewardsCatalog\Block\Product;

/**
 * Class Points
 * @package Mirasvit\Rewards\Block\Product
 * @deprecated
 */
class Points extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Mirasvit_RewardsCatalog::product/points.phtml';

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;
    /**
     * @var \Mirasvit\Rewards\Model\Config
     */
    private $config;
    /**
     * @var \Mirasvit\RewardsCatalog\Helper\Earn
     */
    private $earnHelper;
    /**
     * @var \Mirasvit\RewardsCatalog\Helper\Spend
     */
    private $spendHelper;
    /**
     * @var \Mirasvit\Rewards\Helper\Data
     */
    private $rewardsDataHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Mirasvit\Rewards\Helper\ProductPrice
     */
    private $productPriceHelper;

    public function __construct(
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Helper\ProductPrice $productPriceHelper,
        \Mirasvit\RewardsCatalog\Helper\Earn $earnHelper,
        \Mirasvit\RewardsCatalog\Helper\Spend $spendHelper,
        \Mirasvit\Rewards\Helper\Data $rewardsDataHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config             = $config;
        $this->earnHelper         = $earnHelper;
        $this->spendHelper        = $spendHelper;
        $this->rewardsDataHelper  = $rewardsDataHelper;
        $this->registry           = $registry;
        $this->customerSession    = $customerSession;
        $this->storeManager       = $context->getStoreManager();
        $this->productPriceHelper = $productPriceHelper;
    }

    /**
     * @return bool
     */
    public function isShowPoints()
    {
        return $this->config->getDisplayOptionsIsShowPointsOnProductPage() && $this->getProduct();
    }

    /**
     * @return string
     */
    public function getPointsFormatted()
    {
        $customer = $this->customerSession->getCustomer();
        $websiteId = $this->storeManager->getWebsite()->getId();

        $points = $this->earnHelper->getProductPoints($this->getProduct(), $customer, $websiteId);
        if (!$points) {
            return 0;
        }
        $price = $this->productPriceHelper->getProductPrice($this->getProduct());
        $money  = $this->spendHelper->getProductPointsAsMoney($points, $price, $customer, $websiteId);
        if ($points != $money) {
            return __('Possible discount %1 %2', $this->getLabel(), $money);
        }

        return __('Earn %1 %2', $this->getLabel(), $this->rewardsDataHelper->formatPoints($points));
    }

    /**
     * @return bool|true
     */
    public function getIsProductPointsPossible()
    {
        return $this->earnHelper->getIsProductPointsPossible(
            $this->getProduct(),
            $this->customerSession->getCustomer(),
            $this->_storeManager->getStore()->getWebsiteId()
        );
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        $label = '';
        switch ($this->getProduct()->getTypeId()) {
            case \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE:
                $label = 'starting at';
                break;
            case \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE:
                $label = 'Up to';
                break;
        }

        return $label;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->product) {
            return $this->registry->registry('product');
        } else {
            return $this->product;
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }
}
