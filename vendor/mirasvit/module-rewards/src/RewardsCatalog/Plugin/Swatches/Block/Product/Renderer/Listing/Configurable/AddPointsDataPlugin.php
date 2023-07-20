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


namespace Mirasvit\RewardsCatalog\Plugin\Swatches\Block\Product\Renderer\Listing\Configurable;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Block\Product\Renderer\Listing\Configurable as ListingBlock;
use Mirasvit\Rewards\Helper\Json;

use Mirasvit\Rewards\Model\Config;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class AddPointsDataPlugin
{
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Mirasvit\RewardsCatalog\Helper\Earn
     */
    private $earnHelper;
    /**
     * @var Json
     */
    private $jsonHelper;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var \Mirasvit\Rewards\Helper\ProductPrice
     */
    private $productPriceHelper;

    public function __construct(
        Session $customerSession,
        StoreManagerInterface $storeManager,
        \Mirasvit\RewardsCatalog\Helper\Earn $earnHelper,
        \Mirasvit\Rewards\Helper\ProductPrice $productPriceHelper,
        Json $jsonHelper,
        Config $config
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager    = $storeManager;
        $this->earnHelper      = $earnHelper;
        $this->jsonHelper      = $jsonHelper;
        $this->config          = $config;
        $this->productPriceHelper          = $productPriceHelper;
    }

    /**
     * @param ListingBlock $listingBlock
     * @param \callable    $proceed
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function aroundGetPricesJson(ListingBlock $listingBlock, $proceed)
    {
        \Magento\Framework\Profiler::start(__CLASS__.'_default:'.__METHOD__);
        $returnValue = $proceed();

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerSession->getCustomer();
        $productRules = $this->earnHelper->getProductPageRules($customer, $websiteId);
        if (!$productRules->count()) {
            return $returnValue;
        }

        $product = $listingBlock->getProduct();
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $children = [];
            $products = $listingBlock->getAllowProducts();
            foreach ($products as $childProduct) {
                $childProduct->setProduct($childProduct);
                $children[] = $childProduct;
            }
            $product->setChildren($children);
        }
        if (!$product || !$product->getId() || !$this->config->getDisplayOptionsIsShowPointsOnFrontend()) {
            \Magento\Framework\Profiler::stop(__CLASS__.'_default:'.__METHOD__);
            return $returnValue;
        }

        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $data = $this->jsonHelper->unserialize($returnValue);

        $points = $this->earnHelper->getProductFloatPoints($product, $customer, $websiteId, false);
        $data['rewardRules'] = [
            'amount'    => $points,
        ];
        $data['rewardsBasePrice'] = [
            'amount'    => $this->productPriceHelper->getProductPrice($product),
        ];
        $data['rewardProductId'] = [
            'amount'    => 0, // should be 0 because it is hack to send product ID to price js
        ];
        $data['rewardProductId'] = [
            'amount'    => 0, // should be 0 because it is hack to send product ID to price js
        ];

        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
        \Magento\Framework\Profiler::stop(__CLASS__.'_default:'.__METHOD__);

        return $this->jsonHelper->serialize($data);
    }
}