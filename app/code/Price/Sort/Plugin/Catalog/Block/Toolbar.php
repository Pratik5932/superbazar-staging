<?php
namespace Price\Sort\Plugin\Catalog\Block;
class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{

    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        
       if ($this->getCurrentOrder()) {
       
            if (($this->getCurrentOrder()) == 'name') {
                $this->_collection->addAttributeToSort(
                    $this->getCurrentOrder(),
                    $this->getCurrentDirection()
                );
                
            } else {
                if ($this->getCurrentOrder() == 'high_to_low') {
                    $this->_collection->setOrder('price', 'desc');
                } elseif ($this->getCurrentOrder() == 'low_to_high') {
                    $this->_collection->setOrder('price', 'asc');
                } elseif ($this->getCurrentOrder() == 'weight') {
                    // $this->_collection->setOrder('weight', 'desc');
                    $this->_collection->addAttributeToFilter('weight', array("gt"=>0));
                } 
                elseif (($this->getCurrentOrder()) == 'brand') {
                    $this->_collection->addAttributeToSort('brand','DESC');
                    //addAttributeToFilter('brand', array('eq' => 963));
                //    echo "<pre>";
                //     print_r($this->_collection->getData());
                //    exit;
                    }

            }
        }
        return $this;
    }

}