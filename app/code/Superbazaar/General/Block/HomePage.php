<?php
namespace Superbazaar\General\Block;

class HomePage extends \Magento\Framework\View\Element\Template
{
    /**
    * Tax configuration model
    *
    * @var \Magento\Tax\Model\Config
    */

    /**
    * @var Order
    */

    /**
    * @var \Magento\Framework\DataObject
    */
    protected $source;
    protected $autherCollection;
    protected $categoryCollection;
    protected $categoryRepository;
    protected $categoryFactory;


    /**
    * @param \Magento\Framework\View\Element\Template\Context $context
    * @param \Magento\Tax\Model\Config $taxConfig
    * @param array $data
    * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
    */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Sample\News\Model\ResourceModel\Author\Collection $autherCollection,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,

        array $data = []
    ) {
        $this->autherCollection = $autherCollection;
        $this->categoryCollection = $categoryCollection;
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;

        parent::__construct($context, $data);
    }

    public function getAutherCollection(){
        return $this->autherCollection->load();
    }
    public function getCategoryCollection(){
        return $this->categoryCollection->create()->addAttributeToSelect('*')->addFieldToFilter('is_active', 1)->addAttributeToSort('name', 'ASC');; 
    }
    public function getChildrenCategories($id){
        $cat = $this->getCategory($id);
        //$categoryObj = $this->categoryRepository->get($id);
//        print_r($categoryObj->getData());exit;
//        echo $categoryObj->getId();exit;
        $subcategories = $cat->getChildrenCategories();
        return $subcategories;

    }
    public function getCategory($id)
    {
       // $categoryId = $this->getCategoryId();
        $category = $this->categoryFactory->create()->load($id);
        return $category;
    }
    /**
    * Get data (totals) source model
    *
    * @return \Magento\Framework\DataObject
    */

}