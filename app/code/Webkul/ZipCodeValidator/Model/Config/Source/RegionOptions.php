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
namespace Webkul\ZipCodeValidator\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class RegionOptions extends AbstractSource
{
    /**
     * Region Collection
     *
     * @var \Webkul\ZipCodeValidator\Model\ResourceModel\Region\CollectionFactory
     */
    protected $_regionCollection;

    /**
     * Construct
     *
     * @param \Webkul\ZipCodeValidator\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     */
    public function __construct(
        \Webkul\ZipCodeValidator\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
    ) {
        $this->_regionCollection = $regionCollectionFactory;
    }
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $collections = $this->_regionCollection->create()
            ->addFieldToFilter('status', 1);
        if ($this->_options === null) {
            foreach ($collections as $region) {
                $this->_options[] = [
                    'label' => __($region->getRegionName()),
                    'value' => $region->getId(),
                ];
            }
        }
        return $this->_options;
    }
}
