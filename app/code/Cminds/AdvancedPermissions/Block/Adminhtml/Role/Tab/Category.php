<?php

namespace Cminds\AdvancedPermissions\Block\Adminhtml\Role\Tab;

use Cminds\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Cminds\AdvancedPermissions\Helper\Data;
use Cminds\AdvancedPermissions\Model\AdvancedPermission;
use Cminds\AdvancedPermissions\Model\RoleCategory;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Cminds\AdvancedPermissions\Helper\Data as DataHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic as FormGeneric;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Block\Adminhtml\Category\Tree;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * Class Category
 *
 * @package Cminds\AdvancedPermissions\Block\Adminhtml\Role\Category
 */
class Category extends FormGeneric implements TabInterface
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
    private $roleCategory;

    /**
     * @var Tree
     */
    private $adminCategoryTree;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * Category constructor.
     *
     * @param Context            $context
     * @param Registry           $registry
     * @param FormFactory        $formFactory
     * @param UserConfig         $userConfig
     * @param ModuleConfig       $moduleConfig
     * @param AdvancedPermission $advancedPermission
     * @param RoleCategory       $roleCategory
     * @param Tree               $adminCategoryTree
     * @param DataHelper         $dataHelper
     * @param array              $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        AdvancedPermission $advancedPermission,
        RoleCategory $roleCategory,
        Tree $adminCategoryTree,
        DataHelper $dataHelper,
        array $data = []
    ) {
        $this->userConfig = $userConfig;
        $this->moduleConfig = $moduleConfig;
        $this->registry = $registry;
        $this->advancedPermission = $advancedPermission;
        $this->roleCategory = $roleCategory;
        $this->adminCategoryTree = $adminCategoryTree;
        $this->dataHelper = $dataHelper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @var string
     */
    protected $_template = 'role/category.phtml';

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */

    public function getTabLabel()
    {
        return __('Advanced Role Categories');
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return __('Advanced Role Categories');
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
     * Get category tree.
     *
     * @return array
     */
    public function getCategoryTree()
    {
        $categoryTree = $this->dataHelper->mapCategories($this->adminCategoryTree->getTree());

        return $categoryTree;
    }

    /**
     * Get selected values.
     *
     * @return array
     */
    public function getSelected()
    {
        $roleId = $this->getCurrentRoleId();
        $currentRoleCategory = $this->roleCategory->load($roleId);
        if ($this->roleCategory->getReferenceValue() === '' || $this->roleCategory->getReferenceValue() === null) {
            $selected = [];
        } else {
            $selected = explode(',', $this->roleCategory->getReferenceValue());
        }

        return $selected;
    }

    /**
     * Get current access level.
     *
     * @return int
     */
    public function getAccessLevel()
    {
        $roleId = $this->getCurrentRoleId();
        $advancedPermission = $this->advancedPermission->load($roleId);

        if ($advancedPermission->getIsCategoryLimit() == 1) {
            return AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_CATEGORIES;
        } else {
            return AdvancedPermissionInterface::ACCESS_TO_ALL_CATEGORIES;
        }
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
