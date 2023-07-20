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


namespace Mirasvit\RewardsApi\Model\Product;

use Mirasvit\Rewards\Api\Data\TierInterface;

class ProductPoints implements \Mirasvit\Rewards\Api\ProductPointsInterface
{
    private $earnHelper;

    private $productRepository;

    private $customerRepository;

    private $customerFactory;

    private $productPointsResponseFactory;

    public function __construct(
        \Mirasvit\RewardsCatalog\Helper\Earn $earnHelper,
        \Mirasvit\RewardsApi\Model\Api\ProductPointsResponseFactory $productPointsResponseFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->earnHelper         = $earnHelper;
        $this->productRepository  = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->customerFactory    = $customerFactory;

        $this->productPointsResponseFactory = $productPointsResponseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sku, $price, $customerId, $websiteId, $tierId)
    {
        $product = $this->productRepository->get($sku);
        if ($customerId > 0) {
            $customer = $this->customerRepository->getById($customerId);
        } else {
            $customer = $this->customerFactory->create()->load($customerId);
        }
        if (!$customer->getWebsiteId() && $websiteId) {
            $customer->setWebsiteId($websiteId);
        }

        if (!$tierId) {
            $tier = $customer->getCustomAttribute(TierInterface::CUSTOMER_KEY_TIER_ID);
            if ($tier) {
                $tierId = $tier->getValue();
            }
        }

        $points = $this->earnHelper->getProductPointsByTier(
            $product,
            $price,
            $tierId,
            $customer,
            $customer->getWebsiteId()
        );
        $points = $this->earnHelper->roundPoints($points);

        return $points;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($productInfo)
    {
        $response = [];
        /** @var \Mirasvit\Rewards\Api\Data\ProductPointsInterface $productPoints */
        foreach ($productInfo as $productPoints) {
            $points = $this->get($productPoints->getSku(), $productPoints->getPrice(), $productPoints->getCustomerId(),
                $productPoints->getWebsiteId(), $productPoints->getTierId());
            /** @var \Mirasvit\Rewards\Api\Data\ProductPointsResponseInterface $productPointsResponseObject */
            $productPointsResponseObject = $this->productPointsResponseFactory->create();
            $productPointsResponseObject->setPoints($points)
                ->setSku($productPoints->getSku());
            $response[] = $productPointsResponseObject;
        }

        return $response;
    }
}