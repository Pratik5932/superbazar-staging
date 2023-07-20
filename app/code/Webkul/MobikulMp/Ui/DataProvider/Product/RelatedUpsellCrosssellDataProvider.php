<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMp
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulMp\Ui\DataProvider\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;

/**
 * Class AbstractDataProvider
 */
class RelatedUpsellCrosssellDataProvider
{
    /**
     * Instance of Product Repository Interface
     *
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Instance of store Repository Interface
     *
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * Instance of Product link repository interface
     *
     * @var ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;

    /**
     * Instance of Webkul\Marketplace\Model\ProductFactory
     *
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProduct;

    /**
     * Construct function for class RelatedUpsellCrosssellDataProvider
     *
     * @param CollectionFactory              $collectionFactory     collectionFactory
     * @param ProductRepositoryInterface     $productRepository     productRepository
     * @param StoreRepositoryInterface       $storeRepository       storeRepository
     * @param ProductLinkRepositoryInterface $productLinkRepository productLinkRepository
     *
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        StoreRepositoryInterface $storeRepository,
        ProductRepositoryInterface $productRepository,
        \Webkul\Marketplace\Model\ProductFactory $mpProduct,
        ProductLinkRepositoryInterface $productLinkRepository
    ) {
        $this->mpProductFactory = $mpProduct;
        $this->collection = $collectionFactory;
        $this->storeRepository = $storeRepository;
        $this->productRepository = $productRepository;
        $this->productLinkRepository = $productLinkRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getSellerCollection($sellerId, $storeId, $linkType, $id = null)
    {
        $marketplaceProduct = $this->mpProductFactory->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', $sellerId);
        $allIds = $marketplaceProduct->getAllIds();
        /** @var Collection $collection */
        $collection = $this->collection->create();
        $collection->addAttributeToSelect('status');
        $collection->addFieldToFilter('entity_id', ['in' => $allIds]);
        $collection->setStore($storeId);
        if ($id) {
            $products = [];

            /** @var ProductLinkInterface $linkItem */
            foreach ($this->productLinkRepository->getList($this->getProduct($id)) as $linkItem) {
                if ($linkItem->getLinkType() !== $linkType) {
                    continue;
                }

                $products[] = $this->productRepository->get($linkItem->getLinkedProductSku())->getId();
            }

            $collection->addAttributeToFilter(
                $collection->getIdFieldName(),
                ['nin' => [$products]]
            );
        }

        if ($id && !$this->getProduct($id)) {
            return $collection;
        }

        $collection->addAttributeToFilter(
            $collection->getIdFieldName(),
            ['nin' => [$id]]
        );

        return $collection;
    }

    /**
     * Retrieve product
     *
     * @param integer $id product id
     *
     * @return ProductInterface|null
     */
    public function getProduct($id)
    {
        return $this->productRepository->getById($id);
    }
}
