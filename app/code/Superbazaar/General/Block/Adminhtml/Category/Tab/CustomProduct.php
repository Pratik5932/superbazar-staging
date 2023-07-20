<?php
namespace Superbazaar\General\Block\Adminhtml\Category\Tab;
use Magento\Framework\Data\Collection;

class CustomProduct extends \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
{

    /* protected $eavConfig;
    public function __construct(
    \Magento\Eav\Model\Config $eavConfig
    ) {
    $this->eavConfig = $eavConfig;
    }        */

    public function setCollection($collection)
    {
        $collection->addAttributeToSelect('store_location');
        $this->_collection = $collection;
    }


    /**
    * @return Extended
    */
    protected function _prepareColumns()
    {         
        parent::_prepareColumns();
        $this->addColumnAfter(
            'store_location',
            [
                'header' => __('Store Location'),
                'index' => 'store_location',
                'options' => $this->getStoreLocations(),
                'type' => 'options',
                'column_css_class' => 'data-grid-thumbnail-cell'
            ],
            'status'
        );
        $this->sortColumnsByOrder();

        return $this;
    }


    public function getStoreLocations()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $eavConfig = $objectManager->create('Magento\Eav\Model\Config');
        $attribute = $eavConfig->getAttribute('catalog_product', 'store_location');
        $aoptions = $attribute->getSource()->getAllOptions(); 

        $res = [];
        foreach ($aoptions as $key => $value){
            $res[$value['value']] = $value['label'];

        }
        return $res;



    }   

}