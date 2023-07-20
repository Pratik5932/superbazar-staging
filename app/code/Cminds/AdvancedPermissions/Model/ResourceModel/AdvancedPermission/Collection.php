<?php

namespace Cminds\AdvancedPermissions\Model\ResourceModel\AdvancedPermission;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Cminds\AdvancedPermissions\Model\ResourceModel\AdvancedPermission
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'role_id';

    /**
     * Collection initialization.
     */
    protected function _construct()
    {
        $this->_init(
            \Cminds\AdvancedPermissions\Model\AdvancedPermission::class,
            \Cminds\AdvancedPermissions\Model\ResourceModel\AdvancedPermission::class
        );
    }

    /**
     * Init select.
     */
    protected function _initSelect()
    {
        parent::_initSelect();
    }
}
