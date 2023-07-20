<?php

namespace Cminds\AdvancedPermissions\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 *
 * @package Cminds\AdvancedPermissions\Model
 */
class Config
{
    const PRODUCT_CREATE_EDIT_ALLOWED_RESOURCES = 0;
    const PRODUCT_DELETE_ALLOWED_RESOURCES = 1;
    const CATEGORY_CREATE_EDIT_ALLOWED_RESOURCES = 2;
    const CATEGORY_DELETE_ALLOWED_RESOURCES = 3;
    const CUSTOMER_CREATE_EDIT_ALLOWED_RESOURCES = 4;
    const CUSTOMER_DELETE_ALLOWED_RESOURCES = 5;
    const PRODUCT_POST_CODE_ATTRIBUTE = 'store_location';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var array
     */
    private $config = [];

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface  $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;

        $this->storeId = $this->getStoreId();
    }

    /**
     * Get store id.
     *
     * @return int
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get module config value.
     *
     * @param $fieldKey
     *
     * @return mixed
     */
    private function getConfigValue($fieldKey)
    {
        if (isset($this->config[$fieldKey]) === false) {
            $this->config[$fieldKey] = $this->scopeConfig->getValue(
                'cminds_advancedpermissions/' . $fieldKey,
                ScopeInterface::SCOPE_STORE,
                $this->storeId
            );
        }

        return $this->config[$fieldKey];
    }

    /**
     * Return bool value depends of that if module is active or not.
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getConfigValue('general/module_enabled');
    }

    /**
     * Get resources for check by resource code
     *
     * @param int $resource
     * @return array
     */
    public function getResourcesToCheck($resource)
    {
        switch ($resource) {
            case self::PRODUCT_CREATE_EDIT_ALLOWED_RESOURCES:
                return [
                    'Magento_Backend::all',
                    'Cminds_AdvancedPermission::product_allow_to_create_edit'
                ];
            case self::PRODUCT_DELETE_ALLOWED_RESOURCES:
                return [
                    'Magento_Backend::all',
                    'Cminds_AdvancedPermission::product_allow_to_delete'
                ];
            case self::CATEGORY_CREATE_EDIT_ALLOWED_RESOURCES:
                return [
                    'Magento_Backend::all',
                    'Cminds_AdvancedPermission::category_allow_to_create_edit'
                ];
            case self::CATEGORY_DELETE_ALLOWED_RESOURCES:
                return [
                    'Magento_Backend::all',
                    'Cminds_AdvancedPermission::category_allow_to_delete'
                ];
            case self::CUSTOMER_CREATE_EDIT_ALLOWED_RESOURCES:
                return [
                    'Magento_Backend::all',
                    'Cminds_AdvancedPermission::customer_allow_to_create_edit'
                ];
            case self::CUSTOMER_DELETE_ALLOWED_RESOURCES:
                return [
                    'Magento_Backend::all',
                    'Cminds_AdvancedPermission::customer_allow_to_delete'
                ];
        }

    }
}
