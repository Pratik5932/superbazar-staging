<?php

namespace Cminds\AdvancedPermissions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AdvancedPermission
 *
 * @package Cminds\AdvancedPermissions\Model\ResourceModel
 */
class AdvancedPermission extends AbstractDb
{
    protected $_isPkAutoIncrement = false;

    /**
     * AdvancedPermission ResourceModel initialization.
     */
    protected function _construct()
    {
        $this->_init('cminds_advancedpermissions_role', 'role_id');
    }
}
