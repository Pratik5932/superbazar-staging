<?php

namespace Cminds\AdvancedPermissions\Block\Adminhtml\Role\Tab;

use Cminds\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Cminds\AdvancedPermissions\Model\AdvancedPermission;
use Cminds\AdvancedPermissions\Model\RoleScope;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic as FormGeneric;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * Class Scope
 *
 * @package Cminds\AdvancedPermissions\Block\Adminhtml\Role\Tab
 */
class Scope extends FormGeneric implements TabInterface
{
    /**
     * @var UserConfig
     */
    private $userConfig;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var AdvancedPermission
     */
    private $advancedPermission;

    /**
     * @var RoleScope
     */
    private $roleScope;

    /**
     * Scope constructor.
     *
     * @param Context            $context
     * @param Registry           $registry
     * @param FormFactory        $formFactory
     * @param UserConfig         $userConfig
     * @param ModuleConfig       $moduleConfig
     * @param AdvancedPermission $advancedPermission
     * @param RoleScope          $roleScope
     * @param array              $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        AdvancedPermission $advancedPermission,
        RoleScope $roleScope,
        array $data = []
    ) {
        $this->userConfig = $userConfig;
        $this->moduleConfig = $moduleConfig;
        $this->registry = $registry;
        $this->advancedPermission = $advancedPermission;
        $this->roleScope = $roleScope;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @var string
     */
    protected $_template = 'role/scope.phtml';

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */

    public function getTabLabel()
    {
        return __('Advanced Role Scope');
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return __('Advanced Role Scope');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     *
     * @api
     */
    public function canShowTab()
    {
        $currentRole = $this->registry->registry('cminds_advancedpermissions_edited_rule_id');
        if ($this->moduleConfig->isActive() === false
            || $currentRole === null
        ) {
            return false;
        }

        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     *
     * @api
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get websites.
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getWebsites()
    {
        return $this->userConfig->getAllWebsites();
    }

    /**
     * Get store view tree.
     *
     * @return array
     */
    public function getStoreViewsTree()
    {
        return $this->userConfig->getStoreViewsTree();
    }

    /**
     * Get role values assigned to role. (Website ids, Store view ids)
     *
     * @return array
     */
    public function getSelected()
    {
        $roleId = $this->getCurrentRoleId();
        $currentRoleScope = $this->roleScope->load($roleId);
        if ($this->roleScope->getReferenceValue() === '' || $this->roleScope->getReferenceValue() === null) {
            $selected = [];
        } else {
            $selected = explode(',', $this->roleScope->getReferenceValue());
        }

        return $selected;
    }

    /**
     * Get access level for current role scope.
     *
     * @return int
     */
    public function getAccessLevel()
    {
        $roleId = $this->getCurrentRoleId();
        $advancedPermission = $this->advancedPermission->load($roleId);

        if ($advancedPermission->getIsScopeLimit() == 1) {
            $currentRoleScope = $this->roleScope->load($roleId);
            $scopeAccessLevel = $currentRoleScope->getAccessLevel();
        } else {
            $scopeAccessLevel = AdvancedPermissionInterface::ACCESS_TO_ALL_STORES;
        }

        return $scopeAccessLevel;
    }

    /**
     * Get current role id.
     *
     * @return mixed
     */
    private function getCurrentRoleId()
    {
        return $this->registry->registry('cminds_advancedpermissions_edited_rule_id');
    }
}
