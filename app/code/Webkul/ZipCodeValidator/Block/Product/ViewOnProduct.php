<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ZipCodeValidator\Block\Product;

class ViewOnProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Magento\Customer\Model\Address
     */
    protected $_address;

    /**
     * @var Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var Webkul\ZipCodeValidator\Helper
     */
    protected $_helper;

    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context                   $context
     * @param \Magento\Customer\Model\Session                           $customerSession
     * @param \Magento\Customer\Model\Address                           $address
     * @param \Magento\Catalog\Model\Product                            $product
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface      $stockRegistry
     * @param array                                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Address $address,
        \Magento\Catalog\Model\Product $product,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Webkul\ZipCodeValidator\Helper\Data $helper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_address = $address;
        $this->_product = $product;
        $this->stockRegistry = $stockRegistry;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get Customer Zipcode
     *
     * @return string
     */
    public function getCustomerZipcode()
    {
        if ($this->_customerSession->getCustomerId()) {
            $customerAddressId = $this->_customerSession->getCustomer()->getDefaultShipping();
            $postcode = $this->_address->load($customerAddressId)->getPostcode();
            return $postcode;
        }
        return '';
    }

    /**
     * get Product
     *
     * @return Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->_product->load($id);
    }
    /**
     * get Stock status
     *
     * @return boolean
     */
    public function getStockValue($productId)
    {
        try {
            $stockItem = $this->stockRegistry->getStockItem($productId);
            if ($stockItem) {
                return $stockItem->getIsInStock();
            }
        } catch (\Exception $e) {
            $this->_helper->logDataInLogger("Block_ViewOnProduct_getStockValue : ".$e->getMessage());
        }
    }

    public function isDisplayValidatorField($productId)
    {
        $regionIds = $this->_helper->validateZipCode($productId);
        
        if (!empty($regionIds)) {
            return true;
        } else {
            return false;
        }
    }
}
