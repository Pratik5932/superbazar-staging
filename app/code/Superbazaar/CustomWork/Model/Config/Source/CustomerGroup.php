<?php
namespace Superbazaar\CustomWork\Model\Config\Source;

class CustomerGroup
{
	protected $_customerGroup;
	
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
    ) {
        $this->_customerGroup = $customerGroup;
    }
	
    public function toOptionArray()
    {
		return $this->_customerGroup->toOptionArray();
    }
}
