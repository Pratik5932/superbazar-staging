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
namespace Solwin\FeaturedPro\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\ActionInterface;

class FeaturedList extends \Magento\Catalog\Block\Product\ListProduct
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
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $_helper;
    
    /**
     * Initialize
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Solwin\FeaturedPro\Helper\Data $helper
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param Collection $collection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Solwin\FeaturedPro\Helper\Data $helper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        Collection $collection,
        array $data = []
    ) {
        $this->imageBuilder = $context->getImageBuilder();
        $this->_collection = $collection;
        $this->_helper = $helper;
        parent::__construct(
                $context,
                $postDataHelper,
                $layerResolver,
                $categoryRepository,
                $urlHelper,
                $data
                );
        $this->pageConfig->getTitle()->set(__($this
                ->_helper
                ->getConfigValue(
                        'featuredpro_settings/featured_products/title'
                        )));
    }

    /**
     * Get product collection
     */
    protected function getProducts() {
        $limit = $this->_helper
                ->getConfigValue(
                        'featuredpro_settings/featured_products/limit'
                        );
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
                ->order('rand()');

        $pager = $this->getLayout()
                ->createBlock(
                        'Magento\Theme\Block\Html\Pager',
                        'featuredpro.grid.record.pager'
                        )
                ->setLimit($limit)
                ->setCollection($collection);
        $this->setChild('pager', $pager);

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
     * Get product toolbar
     */

    public function getToolbarHtml() {
        return $this->getChildHtml('pager');
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