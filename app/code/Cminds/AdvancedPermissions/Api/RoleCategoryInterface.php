<?php

namespace Cminds\AdvancedPermissions\Api;

/**
 * Interface RoleCategoryInterface
 *
 * @package Cminds\AdvancedPermissions\Api
 */
interface RoleCategoryInterface
{
    /**
     * Database fields.
     */
    const ROLE_ID = 'role_id';
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
