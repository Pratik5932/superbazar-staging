<?php

namespace Cminds\AdvancedPermissions\Model;

use Cminds\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
// use MEQP2\Tests\NamingConventions\true\mixed;

/**
 * Class AdvancedPermission
 *
 * @package Cminds\AdvancedPermissions\Model
 */
class AdvancedPermission extends AbstractModel implements IdentityInterface, AdvancedPermissionInterface
{
    /**
     * Cache tag.
     *
     * @const string
     */
    const CACHE_TAG = 'cminds_advancedpermissions_role';

    /**
     * AdvancedPermission Model initialization.
     */
    protected function _construct()
    {
        $this->_cacheTag = 'cminds_advancedpermissions_role';
        $this->_eventPrefix = 'cminds_advancedpermissions_role';

        $this->_init(\Cminds\AdvancedPermissions\Model\ResourceModel\AdvancedPermission::class);
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
     * Get is scope limit.
     *
     * @return boolean
     */
    public function getIsScopeLimit()
    {
        return $this->getData(self::SCOPE_LIMIT);
    }

    /**
     * Set is scope limit.
     *
     * @param $isScopeLimit
     */
    public function setIsScopeLimit($isScopeLimit)
    {
        return $this->setData(self::SCOPE_LIMIT,$isScopeLimit);
    }

    /**
     * Get is category limit.
     *
     * @return boolean
     */
    public function getIsCategoryLimit()
    {
        return $this->getData(self::CATEGORY_LIMIT);
    }

    /**
     * Set is category limit.
     *
     * @param $isCategoryLimit
     */
    public function setIsCategoryLimit($isCategoryLimit)
    {
        return $this->setData(self::CATEGORY_LIMIT, $isCategoryLimit);
    }

    /**
     * Get is product limit.
     *
     * @return boolean
     */
    public function getIsProductLimit()
    {
        return $this->getData(self::PRODUCT_LIMIT);
    }

    /**
     * Set is product limit.
     *
     * @param $isProductLimit
     *
     * @return mixed
     */
    public function setIsProductLimit($isProductLimit)
    {
        return $this->setData(self::PRODUCT_LIMIT, $isProductLimit);
    }
}
