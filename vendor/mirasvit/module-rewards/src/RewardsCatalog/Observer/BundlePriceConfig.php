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



namespace Mirasvit\RewardsCatalog\Observer;

use Magento\Framework\Registry;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Bundle\Model\Product\Type;
use Mirasvit\Rewards\Helper\Balance\Earn;
use Mirasvit\Rewards\Model\Config;
use \Mirasvit\Rewards\Api\Data\TierInterface;

class BundlePriceConfig implements ObserverInterface
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var ProductFactory
     */
    private $productFactory;
    /**
     * @var \Mirasvit\RewardsCatalog\Helper\Earn
     */
    private $earnHelper;
    /**
     * @var \Mirasvit\Rewards\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Mirasvit\Rewards\Helper\ProductPrice
     */
    private $productPriceHelper;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var array
     */
    private $prodConfig = [];

    public function __construct(
        Registry $registry,
        Config $config,
        ProductFactory $productFactory,
        \Mirasvit\RewardsCatalog\Helper\Earn $earnHelper,
        \Mirasvit\Rewards\Helper\ProductPrice $productPriceHelper,
        \Mirasvit\Rewards\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->registry        = $registry;
        $this->productPriceHelper        = $productPriceHelper;
        $this->config          = $config;
        $this->productFactory  = $productFactory;
        $this->earnHelper      = $earnHelper;
        $this->helper          = $helper;
        $this->storeManager    = $storeManager;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        /** @var \Magento\Framework\DataObject $configObj */
        $configObj = $observer->getEvent()->getData('configObj');
        if ($this->prodConfig) { // some third-party extensions call this event twice
            return;
        }
        if (!$this->config->getDisplayOptionsIsShowPointsOnProductPage()) {
            return;
        }
        $this->prodConfig = $configObj->getConfig();
        $customer = $this->customerSession->getCustomer();
        $websiteId = $this->storeManager->getWebsite()->getId();

        if ($this->isBundle()) {
            if ($this->isRules($customer, $websiteId)) {
                $this->getBundleOptions($customer, $websiteId);
            }
        } else {
            $this->getCustomOptions($customer, $websiteId);
        }

        $configObj->setConfig($this->prodConfig);
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }

    /**
     * @return bool
     */
    private function isBundle()
    {
        if ($this->registry->registry('current_product')) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->registry->registry('current_product');

            return $product->getTypeId() == Type::TYPE_CODE;
        } else {
            return isset($this->prodConfig['options']);
        }
    }

    /**
     * @param Customer $customer
     * @param int $websiteId
     * @return bool
     */
    private function isRules(Customer $customer, $websiteId)
    {
        return (bool)$this->earnHelper->getProductPageRules($customer, $websiteId)->count();
    }

    /**
     * @param Customer $customer
     * @param int $websiteId
     */
    private function getBundleOptions(Customer $customer, $websiteId)
    {
        $config = $this->prodConfig;
        $config['rewardLabel'] = $this->helper->getPointsName();
        $config['rounding']    = $this->config->getAdvancedEarningRoundingStype();
        if (isset($config['options'])) {
            foreach ($config['options'] as $selectionId => $option) {
                foreach ($option['selections'] as $optionId => $data) {
                    $productId = $data['optionId'];
                    /** @var \Magento\Catalog\Model\Product $product */
                    $product   = $this->productFactory->create()->loadByAttribute('entity_id', $productId);
                    $product->setOptionId($data['optionId']);
                    $product->setSelectionId($selectionId);
                    $product->setSelectionQty($data['qty']);
                    $rules     = $this->getProductRulesCoefficient($product, $customer, $websiteId);
                    $points    = $this->earnHelper->getProductPoints($product, $customer, $websiteId);
                    $productPrice = $this->productPriceHelper->getProductPrice($product);
                    $config['options'][$selectionId]['selections'][$optionId]['rewardRules'] = $rules;
                    $config['options'][$selectionId]['selections'][$optionId]['rewardsBasePrice'] = [
                        'amount' => $productPrice
                    ];
                    $config['options'][$selectionId]['selections'][$optionId]['prices']['rewardsBasePrice']['amount'] =
                        $productPrice;
                    if ($points) {
                        $config['options'][$selectionId]['selections'][$optionId]['prices']['Rewards']['amount'] =
                            $points;
                    }
                }
            }
        }
        $bundleProduct = $this->registry->registry('current_product');
        //@fixme price:        0, //because it's base bundle. price is sum of its subproducts.
        $config["baseProductPoints"] = $this->earnHelper->roundPoints($this->earnHelper->getProductPoints(
            $bundleProduct,
            $customer,
            $websiteId
        ));

        $this->prodConfig = $config;
    }

    /**
     * @param Customer $customer
     * @param int $websiteId
     */
    private function getCustomOptions(Customer $customer, $websiteId)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->registry->registry('current_product');
        if ($product) {
            $config = $this->prodConfig;
            foreach ($config as $optionId => $data) {
                $rules = $this->getProductRulesCoefficient($product, $customer, $websiteId);
                $config[$optionId]['prices']['rewardRules'] = $rules;
            }

            $config['rewardLabel'] = $this->config->getGeneralPointUnitName();
            $config['rounding'] = $this->config->getAdvancedEarningRoundingStype();
            $this->prodConfig = $config;

            $this->getOptionsPoints();
        }
    }

    private function getOptionsPoints()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->registry->registry('current_product');
        $config = $this->prodConfig;

        $options = $product->getOptions();
        if (!$options) {
            return;
        }
        foreach ($options as $option) {
            if ($option->hasValues()) {
                $rules = [];
                if (isset($config[$option->getOptionId()]['prices']['rewardRules'][$product->getId()])) {
                    $rules = $config[$option->getOptionId()]['prices']['rewardRules'][$product->getId()];
                }
                if (isset($config[$option->getOptionId()]['prices']['rewardsBasePrice'][$product->getId()])) {
                    $rules = $config[$option->getOptionId()]['prices']['rewardsBasePrice'][$product->getId()];
                }
                if (!$rules) {
                    continue;
                }
                foreach (array_keys($option->getValues()) as $valueId) {
                    $valuePrice = $config[$option->getOptionId()][$valueId]['prices']['finalPrice']['amount'];
                    $config[$option->getOptionId()][$valueId]['prices']['rewardsBasePrice'] = ['amount' => $valuePrice];
                    foreach ($rules as $ruleOptions) {
                        // prevent division by 0
                        if (!$ruleOptions['coefficient']) {
                            $ruleOptions['coefficient'] = 1;
                        }
                        $points = $valuePrice / $ruleOptions['coefficient'];
                        if (isset($config[$option->getOptionId()][$valueId]['prices']['rewardRules']['amount'])) {
                            $config[$option->getOptionId()][$valueId]['prices']['rewardRules']['amount'] += $points;
                        } else {
                            $config[$option->getOptionId()][$valueId]['prices']['rewardRules'] = ['amount' => $points];
                        }
                    }
                }
            }
        }
        $this->prodConfig = $config;
    }

    /**
     * Prepare data for product options
     *
     * @param Product $product
     * @param Customer $customer
     * @param int $websiteId
     * @return array
     */
    private function getProductRulesCoefficient(Product $product, Customer $customer, $websiteId)
    {
        $product->setCustomer($this->customerSession->getCustomer());
        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        $minAllowed = max((float)$stockItem->getMinSaleQty(), 1);

        $data = [];
        $rules = $this->earnHelper->getProductPageRules($customer, $websiteId);
        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        foreach ($rules as $rule) {
            $tier = $rule->getTier($customer);
            $rule->afterLoad();
            $rule->setIsProductPage(true);
            if ($tier->getEarnPoints() && $rule->validate($product)) {
                switch ($tier->getEarningStyle()) {
                    case Config::EARNING_STYLE_GIVE:
                        $data[$product->getId()][$rule->getId()] = [
                            'points'      => $tier->getEarnPoints(),
                            'coefficient' => 0,
                        ];
                        break;

                    case Config::EARNING_STYLE_AMOUNT_PRICE:
                        $data[$product->getId()][$rule->getId()] = [
                            'points'       => 0,
                            'rewardsPrice' => $this->productPriceHelper->getProductPrice($product),
                            'coefficient'  => $tier->getMonetaryStep() / $tier->getEarnPoints(),
                            'options'      => [
                                'limit' => $tier->getPointsLimit(),
                            ],
                        ];
                        break;
                }

                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }
        if ($data) {
            $data['minAllowed'] = $minAllowed;
            $data['rounding']   = $this->config->getAdvancedEarningRoundingStype();
        }

        return $data;
    }

}
