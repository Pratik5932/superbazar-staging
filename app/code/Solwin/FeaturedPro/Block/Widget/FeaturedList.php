<?php
/**
 * Solwin Infotech
 * Solwin Featured Product Extension
 * 
 * @category   Solwin
 * @package    Solwin_FeaturedPro
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/
 */
namespace Solwin\FeaturedPro\Block\Widget;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\ActionInterface;

class FeaturedList extends \Magento\Catalog\Block\Product\ListProduct
implements \Magento\Widget\Block\BlockInterface
{

    /**
     * Product collection model
     *
     * @var Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_collection;

    /**
     * Product collection model
     *
     * @var Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_productCollection;

    /**
     * Initialize
     *
     * @param Magento\Catalog\Block\Product\Context $context
     * @param Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Magento\Framework\Url\Helper\Data $urlHelper
     * @param Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        array $data = []
    ) {
        $this->_collection = $collection;
        parent::__construct(
                $context,
                $postDataHelper,
                $layerResolver,
                $categoryRepository,
                $urlHelper,
                $data
                );
        $this->setTemplate('widget/list.phtml');
    }

    /**
     * Get product collection
     */
    public function getProducts() {
        $limit = $this->getData('widget_limit');

        $this->_collection->clear()->getSelect()->reset('where');
        $collection = $this->_collection
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('news_from_date')
                ->addAttributeToSelect('news_to_date')
                ->addAttributeToSelect('special_price')
                ->addAttributeToSelect('special_from_date')
                ->addAttributeToSelect('special_to_date')
                ->addAttributeToFilter('is_saleable', 1, 'left')
                ->addAttributeToFilter('is_featured', 1, 'left');

        $collection->getSelect()
                ->order('rand()')
                ->limit($limit);

        $this->_productCollection = $collection;
        return $this->_productCollection;
    }

    /**
     * Load and return product collection
     */
    public function getLoadedProductCollection() {
        return $this->getProducts();
    }

    /**
     * Get grid mode
     */
    public function getMode() {
        return 'grid';
    }

    /**
     * Get image helper
     */
    public function getImageHelper() {
        return $this->_imageHelper;
    }

    public function getAddToCartPostParams(
        \Magento\Catalog\Model\Product $product
    ) {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED =>
                $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
}