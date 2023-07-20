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



namespace Mirasvit\RewardsCatalog\Controller\Product;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Mirasvit\RewardsCatalog\Service\CacheService as RewardsCacheService;
use Mirasvit\Rewards\Helper\Data;
use Mirasvit\RewardsCatalog\Helper\EarnProductPage;
use Mirasvit\RewardsCatalog\Helper\Spend as SpendHelper;
use Mirasvit\Rewards\Model\Config as RewardsConfig;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Action\Context;
use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Repository\TierRepository;

class Points extends Action
{
    protected $rewardsCacheService;

    protected $config;

    protected $customerSession;

    protected $earnProductPageHelper;

    protected $productRepository;

    protected $resultJsonFactory;

    protected $rewardsDataHelper;

    protected $spendHelper;

    protected $storeManager;

    protected $tierRepository;

    public function __construct(
        RewardsCacheService $rewardsCacheService,
        Data $rewardsDataHelper,
        EarnProductPage $earnProductPageHelper,
        SpendHelper $spendHelper,
        RewardsConfig $config,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        TierRepository $tierRepository,
        Context $context
    ) {
        $this->rewardsCacheService   = $rewardsCacheService;
        $this->config                = $config;
        $this->rewardsDataHelper     = $rewardsDataHelper;
        $this->earnProductPageHelper = $earnProductPageHelper;
        $this->spendHelper           = $spendHelper;
        $this->productRepository     = $productRepository;
        $this->customerSession       = $customerSession;
        $this->storeManager          = $storeManager;
        $this->tierRepository        = $tierRepository;
        $this->resultJsonFactory     = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Currency_Exception
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $isMoney  = $this->config->getGeneralIsDisplayProductPointsAsMoney();
        $products = $this->getRequest()->getParams();
        $result = [];

        foreach ($products as $k => $data) {
            if (!is_array($data) || !isset($data['product_id'])) {
                continue;
            }

            $product      = $this->productRepository->getById((int)$data['product_id']);
            $productPrice = (float)$data['price'] * (int)$data['qty'];
            $customer     = $this->customerSession->getCustomer();
            $websiteId    = $this->storeManager->getWebsite()->getId();
            $tierId         = (int)$customer->getData(TierInterface::CUSTOMER_KEY_TIER_ID) ? :
                $this->tierRepository->getFirstTier()->getId();
            $existCacheData = $this->rewardsCacheService->getCache(
                RewardsCacheService::REWARDS_PRODUCT_CACHE,
                [
                    $websiteId,
                    $customer->getGroupId(),
                    $tierId,
                    $product->getSku(),
                    $productPrice
                ]);
            if (!$existCacheData) {
                if ($product->getTypeId() == Configurable::TYPE_CODE) {
                    $product->setProduct($product);
                    $children = [
                        $product,
                    ];
                    $product->setChildren($children);
                }

                $product->setRewardsQty((float)$data['qty']);
                $points = $this->earnProductPageHelper->getProductPagePoints($product, $productPrice, $customer, $websiteId);

                    $this->rewardsCacheService->setCache(RewardsCacheService::REWARDS_PRODUCT_CACHE, [
                        $websiteId,
                        $customer->getGroupId(),
                        $tierId,
                        $product->getSku(),
                        $productPrice
                    ], [
                        $points,
                    ]);
            } else {
                $points = $existCacheData[0];
            }
            if ($isMoney) {
                $money = $this->spendHelper->getProductPointsAsMoney($points, $productPrice, $customer, $websiteId);
                $label = __('Possible discount %1 %2', $this->rewardsDataHelper->getLogoHtml(), $money);
            } else {
                $label = __('Earn %1 %2', $this->rewardsDataHelper->getLogoHtml(), $this->rewardsDataHelper->formatPoints($points));
            }

            // $k - key of js object. Do not change.
            $result[$k] = [
                'points'     => $points,
                'label'      => $label,
                'product_id' => $data['product_id'],
            ];
        }

        return $response->setData($result);
    }
}
