<?php

namespace Cminds\AdvancedPermissions\Model\ResourceModel\RoleScope;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Cminds\AdvancedPermissions\Model\ResourceModel\RoleScope
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
            \Cminds\AdvancedPermissions\Model\RoleScope::class,
            \Cminds\AdvancedPermissions\Model\ResourceModel\RoleScope::class
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
