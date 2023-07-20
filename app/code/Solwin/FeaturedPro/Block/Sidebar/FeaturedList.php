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
namespace Solwin\FeaturedPro\Block\Sidebar;

use Magento\Framework\View\Element\Template;

class FeaturedList extends Template
{

    /**
     * Call list.phtml file in sidebar
     */
    protected $_template = 'Solwin_FeaturedPro::sidebar/list.phtml';

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
     * @var \Magento\Framework\Url\Helper\Data
     */
    
    protected $_urlHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    
    protected $_priceCurrency;
    
    /**
     * Initialize
     *
     * @param Magento\Catalog\Block\Product\Context $context
     * @param Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param Magento\Framework\Url\Helper\Data $urlHelper
     * @param Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        array $data = []
    ) {
        $this->_urlHelper = $urlHelper;
        $this->_collection = $collection;
        $this->_priceCurrency = $priceCurrency;
         parent::__construct($context, $data);
    }

    /**
     * Get product collection
     */
    public function getProducts() {
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
                ->order('rand()');

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
     * Get final price
     */
    
    public function getFinalPrice($value) {
        return $this->_priceCurrency->convertAndFormat($value);
    }
    
}