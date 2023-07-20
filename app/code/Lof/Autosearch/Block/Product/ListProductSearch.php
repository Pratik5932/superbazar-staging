<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Lof\Autosearch\Block\Product;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Product list
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class ListProductSearch extends ListProduct
{
    var $dataHelper, $searchCollection, $request, $_queryFactory;
    protected $_categoryModel;
    protected $_searchModel;
    protected $_storeManager;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Lof\Autosearch\Helper\Data $dataHelper,
        StoreManagerInterface $storeManager,
        \Lof\Autosearch\Model\ResourceModel\Search\CollectionFactory $searchCollection,
        \Lof\Autosearch\Model\Search $searchModel,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Magento\Catalog\Model\Category $categoryModel,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->searchCollection = $searchCollection;
        $this->request = $request;
        $this->_storeManager     = $storeManager;
        $this->_categoryModel  = $categoryModel;
        $this->_searchModel = $searchModel;
        $this->_queryFactory = $queryFactory;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }
    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedProductCollection()
    {
        $data = $this->request->getPostValue();
        $searchstring  = isset($data['q']) ? $data['q'] : '';
        $searchFulltext = $this->dataHelper->searchFulltext();

        $rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();
        $query = $this->_queryFactory->get();
        $queryText = $query->getQueryText();
        $queryText = $queryText ? $queryText : $searchstring;
        $searchstring = $searchstring ? $searchstring : $queryText;
        $storeId       = $this->_storeManager->getStore()->getId();
        $categoryId    = isset($data['cat']) ? $data['cat'] : $rootCategoryId;
        if ($searchFulltext && $searchstring) {
            $searchstring = trim($searchstring);
            $search_arr = explode(" ", $searchstring);
            if (count($search_arr) <= 1) {
                $search_arr = $searchstring;
            }

            // $collection = $this->searchCollection->create();
            // $collection->addAttributeToSelect('*');
            // $product_collection = $this->_getProductCollection();
            //$product_collection = $collection->addSearchFilter($search_arr, $product_collection);

            // above code is always giving null show used below code for collection
            $category = $this->_categoryModel->load($categoryId);
            $collection = $this->_searchModel->getResultSearchCollection($searchstring, $category, $storeId);
            return $collection;
        } else {
            return parent::getLoadedProductCollection();
        }
    }
}
