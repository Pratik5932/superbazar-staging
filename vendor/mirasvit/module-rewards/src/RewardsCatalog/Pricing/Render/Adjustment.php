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


namespace Mirasvit\RewardsCatalog\Pricing\Render;

use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\AbstractAdjustment;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

use Mirasvit\Rewards\Model\Config;

/**
 * Display points on product and category pages
 *
 * @method string getIdSuffix()
 * @method string getDisplayLabel()
 */
class Adjustment extends AbstractAdjustment
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var \Mirasvit\RewardsCatalog\Helper\Earn
     */
    private $earnHelper;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Mirasvit\Rewards\Helper\ProductPrice
     */
    private $productPriceHelper;

    public function __construct(
        Config                 $config,
        \Mirasvit\RewardsCatalog\Helper\Earn $earnHelper,
        \Mirasvit\Rewards\Helper\ProductPrice $productPriceHelper,
        Registry               $registry,
        Session                $customerSession,
        PriceCurrencyInterface $priceCurrency,
        Template\Context       $context,
        array                  $data = []
    ) {
        $this->earnHelper      = $earnHelper;
        $this->registry        = $registry;
        $this->customerSession = $customerSession;
        $this->productPriceHelper = $productPriceHelper;
        $this->config          = $config;
        $this->storeManager    = $context->getStoreManager();
        $this->appState        = $context->getAppState();

        parent::__construct($context, $priceCurrency, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function apply()
    {
        $websiteId    = $this->storeManager->getStore()->getWebsiteId();
        $customer     = $this->customerSession->getCustomer();
        $productRules = $this->earnHelper->getProductPageRules($customer, $websiteId);
        if (!$productRules->count()) {
            return '';
        }

        if ($this->isProductPage() && !$this->config->getDisplayOptionsIsShowPointsOnProductPage()) {
            return '';
        }
        if (!$this->isProductPage() && !$this->config->getDisplayOptionsIsShowPointsOnFrontend()) {
            return '';
        }

        if ($this->getData('price_type_code') == 'tier_price') {
            return '';
        }

        return $this->toHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function getAdjustmentCode()
    {
        return \Mirasvit\RewardsCatalog\Pricing\Adjustment::ADJUSTMENT_CODE;
    }

    /**
     * @return bool
     */
    public function isProductPage()
    {
//        difference of view. Do not remove commented code!!!
//        if (
//            $this->getCurrentProduct() && $this->getCurrentProduct()->getTypeId() &&
//            (
//                $this->getData('zone') == \Magento\Framework\Pricing\Render::ZONE_ITEM_VIEW ||
//                (// for grouped products zone is inverted
//                    $this->getCurrentProduct()->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE &&
//                    $this->getData('zone') == \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
//                ) ||
//                (// bundle options
//                    $this->getCurrentProduct()->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE &&
//                    $this->getProduct()->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE &&
//                    !$this->getData('zone')
//                )
//            )
//        ) {
//            return true;
//        }

        return $this->getCurrentProduct() && $this->getCurrentProduct()->getTypeId();
    }

    /**
     * Define if both prices should be displayed
     *
     * @return bool
     */
    public function isShowPoints()
    {
        \Magento\Framework\Profiler::start(__METHOD__);
        if ($this->isProductPage()) {
            $isAllowToShow = $this->config->getDisplayOptionsIsShowPointsOnProductPage();
        } else {
            $isAllowToShow = $this->config->getDisplayOptionsIsShowPointsOnFrontend();
        }

        $f = $isAllowToShow && !$this->isOptionPrice();
        \Magento\Framework\Profiler::stop(__METHOD__);
        return $f;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isFront()
    {
        return $this->appState->getAreaCode() == 'frontend';
    }

    /**
     * @return float
     */
    public function getBaseProductPrice()
    {
        return $this->productPriceHelper->getProductPrice($this->getProduct());
    }

    /**
     * @return float
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * @return float
     */
    public function getPointsRequestUrl()
    {
        return $this->getUrl('rewards_catalog/product/points', ['product_id' => $this->getProduct()]);
    }

    /**
     * @return bool
     */
    public function isOptionPrice()
    {
        return $this->getAmountRender()->getPrice()->getPriceCode() == CustomOptionPrice::PRICE_CODE;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProduct()
    {
        return $this->getSaleableItem();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return float
     */
    public function getMinProductPrice()
    {
        $price = 0;
        $priceType = $this->getAmountRender()->getPriceType();
        if ($this->isBundle() && $priceType == 'minPrice') {
            $price = $this->productPriceHelper->getProductPrice($this->getProduct(), $priceType);
        }

        return $price;
    }

    /**
     * @return float
     */
    public function getMaxProductPrice()
    {
        $price = 0;
        $priceType = $this->getAmountRender()->getPriceType();
        if ($this->isBundle() && $priceType == 'maxPrice') {
            $price = $this->productPriceHelper->getProductPrice($this->getProduct(), $priceType);
        }

        return $price;
    }

    /**
     * @return float
     */
    public function getDefaultSelectedProductPriceAmount()
    {
        $price = 0;
        $priceType = $this->getAmountRender()->getPriceType();

        if ($this->isBundle() && $priceType !== 'maxPrice' && $priceType !== 'minPrice') {
            $price = $this->getAmountRender()->getAmount();
        }

        return $price;
    }

    /**
     * @return bool
     */
    private function isBundle()
    {
        return $this->getSaleableItem()->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE;
    }
}
