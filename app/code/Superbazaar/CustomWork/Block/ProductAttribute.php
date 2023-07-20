<?php
namespace Superbazaar\CustomWork\Block;

use Magento\Catalog\Model\Product;
use Webkul\Marketplace\Helper\Data as HelperData;

class ProductAttribute extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var HelperData
     */
    protected $_helperData;
	
	protected $_scopeConfig;
	
	protected $_entityAttribute;
	
	protected $_attributeOptionCollection;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Product $product,
        HelperData $helperData,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Eav\Model\Entity\Attribute $entityAttribute,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attributeOptionCollection,
        array $data = []
    ) {
        $this->_product = $product;
		$this->_scopeConfig = $scopeConfig;
        $this->_helperData = $helperData;
		$this->_entityAttribute = $entityAttribute;
		$this->_attributeOptionCollection = $attributeOptionCollection;
        parent::__construct($context, $data);
    }

    public function getProduct($id)
    {
        return $this->_product->load($id);
    }
	
	public function getEnabledAttributes()
	{
		$attributeList = [];
		$attributes = explode(",", $this->_scopeConfig->getValue("general/settings/attribute_list", \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
		foreach ($attributes as $attributeCode) {
			$attribute = $this->getAttributeInfo($attributeCode);
			$attributeList[] = $attribute->getData();
		}
		return $attributeList;
	}
	public function getAttributeInfo($attributeCode)
	{
		return $this->_entityAttribute
					->loadByCode("catalog_product", $attributeCode);
	}
	
	public function getAttributeOptionAll($attributeId)
	{
		return $this->_attributeOptionCollection
					->create()
					->setPositionOrder('asc')
					->setAttributeFilter($attributeId)
					->setStoreFilter()
					->load();
	}
}
