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


namespace Mirasvit\RewardsCatalog\Plugin\Product;

use Magento\Catalog\Block\Product\View as ProductView;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Mirasvit\Rewards\Model\Config;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class View
{
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var \Mirasvit\RewardsCatalog\Helper\Earn
     */
    private $earnHelper;
    /**
     * @var ProductFactory
     */
    private $productFactory;
    /**
     * @var \Mirasvit\Rewards\Helper\ProductPrice
     */
    private $productPriceHelper;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        Config $config,
        \Mirasvit\RewardsCatalog\Helper\Earn $earnHelper,
        ProductFactory $productFactory,
        \Mirasvit\Rewards\Helper\ProductPrice $productPriceHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->jsonDecoder        = $jsonDecoder;
        $this->jsonEncoder        = $jsonEncoder;
        $this->config             = $config;
        $this->earnHelper         = $earnHelper;
        $this->productFactory     = $productFactory;
        $this->productPriceHelper = $productPriceHelper;
        $this->customerSession    = $customerSession;
        $this->storeManager       = $storeManager;
    }

    /**
     * @param ProductView $view
     * @param \callable   $proceed
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetJsonConfig(ProductView $view, $proceed)
    {
        $returnValue = $proceed();

        $data = $this->jsonDecoder->decode($returnValue);

        if (empty($data['prices']) || !$this->config->getDisplayOptionsIsShowPointsOnProductPage()) {
            return $returnValue;
        }
        $product   = $view->getProduct();
        $price     = $this->productPriceHelper->getProductPrice($product);
        $customer  = $this->customerSession->getCustomer();
        $websiteId = $this->storeManager->getWebsite()->getId();

        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $children = [];
            $products = $product->getTypeInstance()->getUsedProducts($product, null);
            foreach ($products as $childProduct) {
                $childProduct->setProduct($childProduct);
                $children[] = $childProduct;
            }
            $product->setChildren($children);
        }

        $data['prices']['rewardRules'] = [
            'adjustments' => [],
            'amount'      => $this->earnHelper->getProductFloatPoints($product, $customer, $websiteId, false)
        ];
        $data['prices']['rewardsBasePrice'] = [
            'adjustments' => [],
            'amount'      => $price,
        ];
        $data['prices']['rewardProductId'] = [
            'adjustments' => [],
            'amount'      => 0, // should be 0 because it is hack to send product ID to price js
        ];
        if (!empty($data['optionTemplate'])) {
            $unit = $this->config->getGeneralPointUnitName();
            $unit = str_replace(['(', ')'], '', $unit);
            $data['optionTemplate'] .= ' <% if (typeof data.Rewards != "undefined" && data.Rewards.value) { %>, ' .
                '<%= data.Rewards.value %>' .
                $unit .
                '<% } %> '
            ;
        }

        return $this->jsonEncoder->encode($data);
    }
}