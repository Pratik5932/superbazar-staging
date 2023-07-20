<?php

namespace Cminds\AdvancedPermissions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class RoleScope
 *
 * @package Cminds\AdvancedPermissions\Model\ResourceModel
 */
class RoleScope extends AbstractDb
{
    protected $_isPkAutoIncrement = false;
    /**
     * RoleScope ResourceModel initialization.
     */
    protected function _construct()
    {
        $this->_init('cminds_advancedpermissions_role_scope', 'role_id');
    }
}
