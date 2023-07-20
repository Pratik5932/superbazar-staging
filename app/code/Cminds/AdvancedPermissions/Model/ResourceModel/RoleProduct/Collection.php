<?php

namespace Cminds\AdvancedPermissions\Model\ResourceModel\RoleProduct;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Cminds\AdvancedPermissions\Model\ResourceModel\RoleProduct
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'role_id';

    /**
     * Id field name
     */
    protected function _construct()
    {
        $this->_init(
            \Cminds\AdvancedPermissions\Model\RoleProduct::class,
            \Cminds\AdvancedPermissions\Model\ResourceModel\RoleProduct::class
        );
    }

    /**
     * Collection initialization.
     */
    protected function _initSelect()
    {
        parent::_initSelect();
    }
}
