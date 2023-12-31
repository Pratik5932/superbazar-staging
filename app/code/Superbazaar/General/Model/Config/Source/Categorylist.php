<?php

namespace Superbazaar\General\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Categorylist implements ArrayInterface
{
    protected $_categoryFactory;
    protected $_categoryCollectionFactory;
    protected $_storeManager;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_categoryFactory = $categoryFactory;
         $this->_storeManager = $storeManager;
    }

    /**
    * Get category collection
    *
    * @param bool $isActive
    * @param bool|int $level
    * @param bool|string $sortBy
    * @param bool|int $pageSize
    * @return \Magento\Catalog\Model\ResourceModel\Category\Collection or array
    */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->setStore($this->_storeManager->getStore());


        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

    public function toOptionArray()
    {
        $arr = $this->_toArray();
        $ret = [];
        foreach ($arr as $key => $value){
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }    
        return $ret;
    }

    private function _toArray()
    {
        $categories = $this->getCategoryCollection(true, false, false, false);
        $catagoryList = array();
        foreach ($categories as $category){
           $catagoryList[$category->getEntityId()] = __($this->_getParentName($category->getPath()) . $category->getName());
        }
        return $catagoryList;
    }

    private function _getParentName($path = '')
    {
        $parentName = '';
        $rootCats = array(1,2);  
        $catTree = explode("/", $path);
        array_pop($catTree);     
        if($catTree && (count($catTree) > count($rootCats))){
            foreach ($catTree as $catId){
                if(!in_array($catId, $rootCats)){
                    $category = $this->_categoryFactory->create()->load($catId);
                    $categoryName = $category->getName();
                    $parentName .= $categoryName . ' -> ';
                }
            }
        }
        return $parentName;
    }
}
