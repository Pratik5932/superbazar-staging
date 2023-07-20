<?php

namespace Cminds\AdvancedPermissions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class RoleCategory
 *
 * @package Cminds\AdvancedPermissions\Model\ResourceModel
 */
class RoleCategory extends AbstractDb
{
    protected $_isPkAutoIncrement = false;
    /**
     * RoleCategory ResourceModel initialization.
     */
    protected function _construct()
    {
        $this->_init('cminds_advancedpermissions_role_category', 'role_id');
    }
}
