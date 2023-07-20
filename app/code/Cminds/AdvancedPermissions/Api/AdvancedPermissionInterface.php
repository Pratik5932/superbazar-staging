<?php

namespace Cminds\AdvancedPermissions\Api;

/**
 * Interface AdvancedPermissionInterface
 */
interface AdvancedPermissionInterface
{

    const ROLE_ID = 'role_id';
    const SCOPE_LIMIT = 'scope_limit';
    const CATEGORY_LIMIT = 'category_limit';
    const PRODUCT_LIMIT = 'product_limit';

    /**
     * Scope access levels.
     */
    const ACCESS_TO_ALL_STORES = 1;
    const ACCESS_TO_SPECIFIED_WEBSITES = 2;
    const ACCESS_TO_SPECIFIED_STORE_VIEWS = 3;

    /**
     * Category access levels.
     */
    const ACCESS_TO_ALL_CATEGORIES = 0;
    const ACCESS_TO_SPECIFIED_CATEGORIES = 1;

    /**
     * Product access levels.
     */
    const ACCESS_TO_ALL_PRODUCTS = 1;
    const ACCESS_TO_SPECIFIED_PRODUCTS = 2;
    const ACCESS_TO_OWN_CREATED_PRODUCTS = 3;

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
     *
     * @return mixed
     */
    public function setRoleId($roleId);

    /**
     * Get is scope limited.
     *
     * @return boolean
     */
    public function getIsScopeLimit();

    /**
     * Set is scope limit.
     *
     * @param $isScopeLimit
     *
     * @return mixed
     */
    public function setIsScopeLimit($isScopeLimit);

    /**
     * Get is category limit.
     *
     * @return boolean
     */
    public function getIsCategoryLimit();

    /**
     * Set is category limit.
     *
     * @param $isCategoryLimit
     *
     * @return mixed
     */
    public function setIsCategoryLimit($isCategoryLimit);

    /**
     * Get is product limit.
     *
     * @return boolean
     */
    public function getIsProductLimit();

    /**
     * Set is product limit.
     *
     * @param $isProductLimit
     *
     * @return mixed
     */
    public function setIsProductLimit($isProductLimit);
}
