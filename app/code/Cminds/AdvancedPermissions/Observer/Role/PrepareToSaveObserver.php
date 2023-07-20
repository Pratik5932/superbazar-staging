<?php

namespace Cminds\AdvancedPermissions\Observer\Role;

use Cminds\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Cminds\AdvancedPermissions\Model\AdvancedPermission;
use Cminds\AdvancedPermissions\Model\ResourceModel\AdvancedPermission\Collection;
use Cminds\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Cminds\AdvancedPermissions\Model\ResourceModel\RoleScope\Collection as RoleScopeCollection;
use Cminds\AdvancedPermissions\Model\ResourceModel\RoleProduct\Collection as RoleProductCollection;
use Cminds\AdvancedPermissions\Model\RoleCategory;
use Cminds\AdvancedPermissions\Model\RoleProduct;
use Cminds\AdvancedPermissions\Model\RoleProductFactory;
use Cminds\AdvancedPermissions\Model\RoleScope;
use Cminds\AdvancedPermissions\Model\RoleScopeFactory;
use Cminds\AdvancedPermissions\Model\ResourceModel\RoleCategory\Collection as RoleCategoryCollection;
use Cminds\AdvancedPermissions\Model\RoleCategoryFactory;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class PrepareToSaveObserver
 *
 * @package Cminds\AdvancedPermissions\Observer\Role
 */
class PrepareToSaveObserver implements ObserverInterface
{
    /**
     * @var Collection
     */
    private $advancedPermissionCollection;

    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * @var RoleScopeFactory
     */
    private $roleScopeFactory;

    /**
     * @var RoleScopeCollection
     */
    private $roleScopeCollection;

    /**
     * @var RoleCategoryFactory
     */
    private $roleCategoryFactory;

    /**
     * @var RoleCategoryCollection
     */
    private $roleCategoryCollection;

    /**
     * @var RoleProductFactory
     */
    private $roleProductFactory;

    /**
     * @var RoleProductCollection
     */
    private $roleProductCollection;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * PrepareToSaveObserver constructor.
     *
     * @param Collection                $advancedPermissionCollection
     * @param AdvancedPermissionFactory $advancedPermissionFactory
     * @param RoleScopeFactory          $roleScopeFactory
     * @param RoleScopeCollection       $roleScopeCollection
     * @param RoleCategoryCollection    $roleCategoryCollection
     * @param RoleCategoryFactory       $roleCategoryFactory
     * @param RoleProductCollection     $roleProductCollection
     * @param RoleProductFactory        $roleProductFactory
     * @param ModuleConfig              $moduleConfig
     * @param Session                   $customerSession
     */
    public function __construct(
        Collection $advancedPermissionCollection,
        AdvancedPermissionFactory $advancedPermissionFactory,
        RoleScopeFactory $roleScopeFactory,
        RoleScopeCollection $roleScopeCollection,
        RoleCategoryCollection $roleCategoryCollection,
        RoleCategoryFactory $roleCategoryFactory,
        RoleProductCollection $roleProductCollection,
        RoleProductFactory $roleProductFactory,
        ModuleConfig $moduleConfig,
        Session $customerSession
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->advancedPermissionCollection = $advancedPermissionCollection;
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleScopeFactory = $roleScopeFactory;
        $this->roleScopeCollection = $roleScopeCollection;
        $this->roleCategoryFactory = $roleCategoryFactory;
        $this->roleCategoryCollection = $roleCategoryCollection;
        $this->roleProductCollection = $roleProductCollection;
        $this->roleProductFactory = $roleProductFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getRequest();
        $role = $observer->getObject();
        $roleId = $role->getId();

        if ($this->moduleConfig->isActive() === false || $roleId === null) {
            return;
        }

        $scopeAccessLevel = (int) $request->getParam('scope_select');
        $categoryAccessLevel = (int) $request->getParam('category_select');
        $productAccessLevel = (int) $request->getParam('product_select');

        // scope reference value
        if ($request->getParam('websites') !== null
            && $request->getParam('websites') !== ''
        ) {
            $scopeReferenceValue = $request->getParam('websites');
        } elseif ($request->getParam('stores') !== null
            && $request->getParam('stores') !== ''
        ) {
            $scopeReferenceValue = $request->getParam('stores');
        } else {
            $scopeReferenceValue = '';
        }

        // category reference value
        if ($request->getParam('categories') !== null
            && $request->getParam('categories') !== ''
        ) {
            $categoryReferenceValue = $request->getParam('categories');
        } else {
            $categoryReferenceValue = '';
        }

        // product reference value
        if ($request->getParam('in_role_product') !== null
            && $request->getParam('in_role_product') !== ''
        ) {
            $productReferenceValue = $request->getParam('in_role_product');
        } else {
            $productReferenceValue = [];
        }

        $tempProductReferenceValue = $this->customerSession->getSelectedProductsTempValues();

        if ($tempProductReferenceValue !== null) {
            foreach ($tempProductReferenceValue as $index => $value) {
                if ($value == 1) {
                    $isAlreadyInArray = array_search($index, $productReferenceValue);

                    if ($isAlreadyInArray === false) {
                        $productReferenceValue[] = $index;
                    }

                }
            }
        }

        $scopeLimit = false;
        $categoryLimit = false;
        $productLimit = false;

        // check is there a scope limit
        if ($scopeAccessLevel !==  AdvancedPermissionInterface::ACCESS_TO_ALL_STORES) {
            $scopeLimit = true;
        }

        // check is there a category limit
        if ($categoryAccessLevel !==  AdvancedPermissionInterface::ACCESS_TO_ALL_CATEGORIES) {
            $categoryLimit = true;
        }

        // check is there a product limit
        if ($productAccessLevel !==  AdvancedPermissionInterface::ACCESS_TO_ALL_PRODUCTS) {
            $productLimit = true;
        }

        //if the AdvancedPermission model for this role does not exist
        if ($this->isAdvancedPermissionRoleExists($role->getRoleId()) === false) {
            $advancedPermission = $this->saveToNewAdvancedPermissionRole(
                $role->getRoleId(),
                $scopeLimit,
                $categoryLimit,
                $productLimit
            );
        } elseif ($this->isAdvancedPermissionRoleExists($role->getRoleId())) {
            $advancedPermission = $this->isAdvancedPermissionRoleExists($role->getRoleId());
            $this->saveToExistingAdvancedPermissionRole(
                $advancedPermission,
                $scopeLimit,
                $categoryLimit,
                $productLimit
            );
        }

        // save RoleScope details
        if ($scopeLimit === true && $this->isRoleScopeExists($role->getRoleId())) {
            // save to existing one
            $roleScope = $this->isRoleScopeExists($role->getRoleId());
            $this->saveToExistingScopeRole($role->getRoleId(), $scopeAccessLevel, $scopeReferenceValue);

        } elseif ($scopeLimit === true && $this->isRoleScopeExists($role->getRoleId()) === false) {
            // save to new one
            $roleScope = $this->saveToNewScopeRole($role->getRoleId(), $scopeAccessLevel, $scopeReferenceValue);
        }

        // save RoleCategory details
        if ($categoryLimit === true && $this->isRoleCategoryExists($role->getRoleId())) {
            // save to existing one
            $roleCategory = $this->isRoleCategoryExists($role->getRoleId());
            $this->saveToExistingCategoryRole($role->getRoleId(), $categoryReferenceValue);

        } elseif ($categoryLimit === true && $this->isRoleCategoryExists($role->getRoleId()) === false) {
            // save to new one
            $roleCategory = $this->saveToNewCategoryRole($role->getRoleId(), $categoryReferenceValue);
        }

        // save RoleProduct details
        if ($productLimit === true && $this->isRoleProductExists($role->getRoleId())) {
            // save to existing one
            $roleProduct = $this->isRoleProductExists($role->getRoleId());
            $this->saveToExistingProductRole($role->getRoleId(), $productAccessLevel, $productReferenceValue);

        } elseif ($productLimit === true && $this->isRoleProductExists($role->getRoleId()) === false) {
            // save to new one
            $roleProduct = $this->saveToNewProductRole($role->getRoleId(), $productAccessLevel, $productReferenceValue);
        }

        $this->customerSession->setSelectedProductsTempValues(null);
    }

    /**
     * Check is AdvancedPermission model exists.
     *
     * @param $roleId
     * @return bool|\Magento\Framework\DataObject
     */
    private function isAdvancedPermissionRoleExists($roleId)
    {
        $rolesCollection = $this->advancedPermissionCollection
            ->addFieldToFilter('role_id', $roleId);

        if ($rolesCollection->count() > 0) {
            return $rolesCollection->getFirstItem();
        }

        return false;
    }

    /**
     * Check is RoleScope model exists for role id.
     * @param $roleId
     *
     * @return bool|\Magento\Framework\DataObject
     */
    private function isRoleScopeExists($roleId)
    {
        $roleScopeCollection = $this->roleScopeCollection
            ->addFieldToFilter('role_id', $roleId);

        if ($roleScopeCollection->count() > 0) {
            return $roleScopeCollection->getFirstItem();
        }

        return false;
    }

    /**
     * Check is RoleCategory model exists for role id.
     * @param $roleId
     *
     * @return bool|\Magento\Framework\DataObject
     */
    private function isRoleCategoryExists($roleId)
    {
        $roleCategoryCollection = $this->roleCategoryCollection
            ->addFieldToFilter('role_id', $roleId);

        if ($roleCategoryCollection->count() > 0) {
            return $roleCategoryCollection->getFirstItem();
        }

        return false;
    }

    /**
     * Check is RoleProduct model exists for role id.
     * @param $roleId
     *
     * @return bool|\Magento\Framework\DataObject
     */
    private function isRoleProductExists($roleId)
    {
        $roleProductCollection = $this->roleProductCollection
            ->addFieldToFilter('role_id', $roleId);

        if ($roleProductCollection->count() > 0) {
            return $roleProductCollection->getFirstItem();
        }

        return false;
    }

    /**
     * Save to the new AdvancedPermission model.
     *
     * @param $roleId
     * @param $scopeLimit
     * @param $categoryLimit
     *
     * @return AdvancedPermission
     */
    private function saveToNewAdvancedPermissionRole($roleId, $scopeLimit, $categoryLimit, $productLimit)
    {
        $advancedPermission = $this->advancedPermissionFactory
            ->create()
            ->setRoleId($roleId)
            ->setIsScopeLimit($scopeLimit)
            ->setIsCategoryLimit($categoryLimit)
            ->setIsProductLimit($productLimit)
            ->save();

        return $advancedPermission;
    }

    /**
     * Save to existing AdvancedPermission model.
     *
     * @param $advancedPermission
     * @param $scopeLimit
     * @param $categoryLimit
     *
     * @return AdvancedPermission
     */
    private function saveToExistingAdvancedPermissionRole(
        $advancedPermission,
        $scopeLimit,
        $categoryLimit,
        $productLimit
    ) {
        $advancedPermission
            ->setIsScopeLimit($scopeLimit)
            ->setIsCategoryLimit($categoryLimit)
            ->setIsProductLimit($productLimit)
            ->save();

        return $advancedPermission;
    }

    /**
     * Save to the new RoleScope model.
     *
     * @param $roleId
     * @param $scopeAccessLevel
     * @param $scopeReferenceValue
     * @return RoleScope
     */
    private function saveToNewScopeRole($roleId, $scopeAccessLevel, $scopeReferenceValue)
    {
        if ($scopeReferenceValue !== '') {
            $scopeReferenceValue = implode(',', $scopeReferenceValue);
        }

        $roleScope = $this->roleScopeFactory
            ->create()
            ->setRoleId((int)$roleId)
            ->setAccessLevel($scopeAccessLevel)
            ->setReferenceValue($scopeReferenceValue)
            ->save();

        return $roleScope;
    }

    /**
     * Save to existing RoleScope model.
     *
     * @param $roleId
     * @param $scopeAccessLevel
     * @param $scopeReferenceValue
     * @return RoleScope
     */
    private function saveToExistingScopeRole($roleId, $scopeAccessLevel, $scopeReferenceValue)
    {
        if ($scopeReferenceValue !== '') {
            $scopeReferenceValue = implode(',', $scopeReferenceValue);
        }

        $roleScope = $this->roleScopeFactory->create()->load($roleId);
        $roleScope
            ->setAccessLevel($scopeAccessLevel)
            ->setReferenceValue($scopeReferenceValue)
            ->save();

        return $roleScope;
    }

    /**
     * Save to the new RoleCategory model.
     *
     * @param $roleId
     * @param $categoryReferenceValue
     * @return RoleCategory
     */
    private function saveToNewCategoryRole($roleId, $categoryReferenceValue)
    {
        if ($categoryReferenceValue !== '') {
            $categoryReferenceValue = implode(',', $categoryReferenceValue);
        }

        $roleCategory = $this->roleCategoryFactory
            ->create()
            ->setRoleId((int)$roleId)
            ->setReferenceValue($categoryReferenceValue)
            ->save();

        return $roleCategory;
    }

    /**
     * Save to existing RoleCategory model.
     *
     * @param $roleId
     * @param $categoryReferenceValue
     * @return RoleCategory
     */
    private function saveToExistingCategoryRole($roleId, $categoryReferenceValue)
    {
        if ($categoryReferenceValue !== '') {
            $categoryReferenceValue = implode(',', $categoryReferenceValue);
        }

        $roleCategory = $this->roleCategoryFactory->create()->load($roleId);
        $roleCategory
            ->setReferenceValue($categoryReferenceValue)
            ->save();

        return $roleCategory;
    }

    /**
     * Save to the new RoleProduct model.
     *
     * @param $roleId
     * @param $productAccessLevel
     * @param $productReferenceValue
     *
     * @return RoleProduct
     */
    private function saveToNewProductRole($roleId, $productAccessLevel, $productReferenceValue)
    {
        if ($productReferenceValue !== '') {
            $productReferenceValue = implode(',', $productReferenceValue);
        }

        $roleProduct = $this->roleProductFactory
            ->create()
            ->setRoleId((int)$roleId)
            ->setAccessLevel($productAccessLevel)
            ->setReferenceValue($productReferenceValue)
            ->save();

        return $roleProduct;
    }

    /**
     * Save to existing RoleProduct model.
     *
     * @param $roleId
     * @param $productAccessLevel
     * @param $productReferenceValue
     *
     * @return RoleProduct
     */
    private function saveToExistingProductRole($roleId, $productAccessLevel, $productReferenceValue)
    {
        if ($productReferenceValue !== '') {
            $productReferenceValue = implode(',', $productReferenceValue);
        }

        $roleProduct = $this->roleProductFactory->create()->load($roleId);
        $roleProduct
            ->setAccessLevel($productAccessLevel)
            ->setReferenceValue($productReferenceValue)
            ->save();

        return $roleProduct;
    }
}
