<?php

namespace Cminds\AdvancedPermissions\Model;

use Cminds\AdvancedPermissions\Api\RoleProductInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class RoleProduct
 *
 * @package Cminds\AdvancedPermissions\Model
 */
class RoleProduct extends AbstractModel implements IdentityInterface, RoleProductInterface
{
    /**
     * Cache tag.
     *
     * @const string
     */
    const CACHE_TAG = 'cminds_advancedpermissions_role_product';

    /**
     * RoleProduct Model initialization.
     */
    protected function _construct()
    {
        $this->_cacheTag = 'cminds_advancedpermissions_role_product';
        $this->_eventPrefix = 'cminds_advancedpermissions_role_product';

        $this->_init(\Cminds\AdvancedPermissions\Model\ResourceModel\RoleProduct::class);
    }

    /**
     * Return unique ID(s) for each object in system.
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get role id.
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->getData(self::ROLE_ID);
    }

    /**
     * Set role id.
     *
     * @param $roleId
     */
    public function setRoleId($roleId)
    {
        return $this->setData(self::ROLE_ID, $roleId);
    }

    /**
     * Get access level.
     *
     * @return int
     */
    public function getAccessLevel()
    {
        return $this->getData(self::ACCESS_LEVEL);
    }

    /**
     * Set access level.
     *
     * @param $accessLevel
     *
     * @return mixed
     */
    public function setAccessLevel($accessLevel)
    {
        return $this->setData(self::ACCESS_LEVEL, $accessLevel);
    }

    /**
     * Get reference value.
     *
     * @return int
     */
    public function getReferenceValue()
    {
        return $this->getData(self::REFERENCE_VALUE);
    }

    /**
     * Set reference value.
     *
     * @param $referenceValue
     *
     * @return mixed
     */
    public function setReferenceValue($referenceValue)
    {
        return $this->setData(self::REFERENCE_VALUE, $referenceValue);
    }
}
