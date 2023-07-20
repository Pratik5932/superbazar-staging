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



namespace Mirasvit\RewardsCatalog\Plugin\Product\Type;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ProductConfigurable;
use Magento\Catalog\Model\ProductFactory;
use Mirasvit\Rewards\Model\Config;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class Configurable
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Mirasvit\RewardsCatalog\Helper\Earn
     */
    private $earnHelper;

    /**
     * @var \Mirasvit\Rewards\Helper\ProductPrice
     */
    private $productPriceHelper;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Config $config,
        \Mirasvit\RewardsCatalog\Helper\Earn $earnHelper,
        \Mirasvit\Rewards\Helper\ProductPrice $productPriceHelper,
        ProductFactory $productFactory
    ) {
        $this->registry           = $registry;
        $this->jsonDecoder        = $jsonDecoder;
        $this->jsonEncoder        = $jsonEncoder;
        $this->customerSession    = $customerSession;
        $this->storeManager       = $storeManager;
        $this->config             = $config;
        $this->earnHelper         = $earnHelper;
        $this->productPriceHelper = $productPriceHelper;
        $this->productFactory     = $productFactory;
    }

    /**
     * @param ProductConfigurable $configurable
     * @param \callable           $proceed
     *
     * @return string
     */
    public function aroundGetJsonConfig(ProductConfigurable $configurable, $proceed)
    {
        $returnValue = $proceed();
        \Magento\Framework\Profiler::start(__CLASS__ . '_default:' . __METHOD__);
        // category page
        if (!$this->registry->registry('current_product') &&
            !$this->config->getDisplayOptionsIsShowPointsOnFrontend()
        ) {
            \Magento\Framework\Profiler::stop(__CLASS__ . '_default:' . __METHOD__);

            return $returnValue;
        }
        // product page
        if ($this->registry->registry('current_product') &&
            !$this->config->getDisplayOptionsIsShowPointsOnProductPage()
        ) {
            \Magento\Framework\Profiler::stop(__CLASS__ . '_default:' . __METHOD__);

            return $returnValue;
        }
        // amasty checkout
        if ($this->registry->registry('current_product') &&
            strpos(strtolower($configurable->getNameInLayout()), 'amcheckout') !== false
        ) {
            \Magento\Framework\Profiler::stop(__CLASS__ . '_default:' . __METHOD__);

            return $returnValue;
        }
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer  = $this->customerSession->getCustomer();
        $earnRules = $this->earnHelper->getProductPageRules($customer, $websiteId);

        if (!$earnRules->count()) {
            return $returnValue;
        }

        \Magento\Framework\Profiler::start(__CLASS__ . ':' . __METHOD__);
        $data = $this->jsonDecoder->decode($returnValue);

        $products = $configurable->getAllowProducts();
        $children = [];
        $points   = 0;
        foreach ($products as $product) {
            $points    = $this->earnHelper->getProductFloatPoints($product, $customer, $websiteId, false);
            $productId = $product->getId();

            $data['optionPrices'][$productId]['rewardProductId']['amount']  = $productId;
            $data['optionPrices'][$productId]['rewardRules']['amount']      = $points;
            $data['optionPrices'][$productId]['rewardsBasePrice']['amount'] = $points ? $this->productPriceHelper
                ->getProductPrice($product) : 0;
            $product->setProduct($product);
            $children[] = $product;
        }

        if (!empty($data['productId'])) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productFactory->create()->loadByAttribute('entity_id', $data['productId']);

            if ($product) {
                $product->setChildren($children);
                $points = $this->earnHelper->getProductFloatPoints($product, $customer, $websiteId, false);
            }

        }

        $data['prices']['rewardRules']      = [
            'amount' => $points,
        ];
        $data['prices']['rewardsBasePrice'] = [
            'amount' => $product ?
                $this->productPriceHelper->getProductPrice($product) :
                0,
        ];
        $data['prices']['rewardProductId']  = [
            'amount' => 0, // should be 0 because it is hack to send product ID to price js
        ];
        \Magento\Framework\Profiler::stop(__CLASS__ . ':' . __METHOD__);
        \Magento\Framework\Profiler::stop(__CLASS__ . '_default:' . __METHOD__);

        return $this->jsonEncoder->encode($data);
    }
}
