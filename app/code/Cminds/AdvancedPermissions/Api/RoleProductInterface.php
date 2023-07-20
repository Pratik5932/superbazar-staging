<?php

namespace Cminds\AdvancedPermissions\Api;

/**
 * Interface RoleProductInterface
 *
 * @package Cminds\AdvancedPermissions\Api
 */
interface RoleProductInterface
{
    /**
     * Database fields.
     */
    const ROLE_ID = 'role_id';
    const ACCESS_LEVEL = 'access_level';
    const REFERENCE_VALUE = 'reference_value';

    /**
     * Get role id.
     *
     * @return int
     */
    public function getRoleId();

    /**
     * Set role id.
     *
     * @param $roleId
     */
    public function setRoleId($roleId);

    /**
     * Get access level.
     *
     * @return int
     */
    public function getAccessLevel();

    /**
     * Set access level.
     *
     * @param $accessLevel
     *
     * @return mixed
     */
    public function setAccessLevel($accessLevel);

    /**
     * Get reference value.
     *
     * @return int
     */
    public function getReferenceValue();

    /**
     * Set reference value.
     *
     * @param $referenceValue
     *
     * @return mixed
     */
    public function setReferenceValue($referenceValue);
}
