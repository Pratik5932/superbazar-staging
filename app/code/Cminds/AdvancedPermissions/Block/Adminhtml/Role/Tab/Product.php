<?php

namespace Cminds\AdvancedPermissions\Block\Adminhtml\Role\Tab;

use Cminds\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Cminds\AdvancedPermissions\Model\RoleProductFactory;
use Cminds\AdvancedPermissions\Block\Adminhtml\Role\Grid\Product as GridProduct;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;

/**
 * Class Product
 *
 * @package Cminds\AdvancedPermissions\Block\Adminhtml\Role\Tab
 */
class Product extends Template implements TabInterface
{
    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * @var RoleProductFactory
     */
    private $roleProductFactory;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var GridProduct
     */
    private $gridProduct;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * Product constructor.
     *
     * @param Template\Context                $context
     * @param AdvancedPermissionFactory       $advancedPermissionFactory
     * @param RoleProductFactory              $roleProductFactory
     * @param Registry                        $coreRegistry
     * @param GridProduct                     $gridProduct
     * @param Session                         $customerSession
     * @param array                           $data
     */
    public function __construct(
        Template\Context $context,
        AdvancedPermissionFactory $advancedPermissionFactory,
        RoleProductFactory $roleProductFactory,
        Registry $coreRegistry,
        GridProduct $gridProduct,
        Session $customerSession,
        ModuleConfig $moduleConfig,
        array $data = []
    ) {
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleProductFactory = $roleProductFactory;
        $this->coreRegistry = $coreRegistry;
        $this->gridProduct = $gridProduct;
        $this->customerSession = $customerSession;
        $this->moduleConfig = $moduleConfig;

        parent::__construct($context, $data);
    }

    /**
     * Get tab label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Advanced Role Products');
    }

    /**
     * Return Tab title
     *
     * @return string
     *
     * @api
     */
    public function getTabTitle()
    {
        return __('Advanced Role Products');
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
        $currentRole = $this->coreRegistry->registry('cminds_advancedpermissions_edited_rule_id');
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
     * Internal constructor, that is called from real constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('role/product.phtml');

        parent::_construct();
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->customerSession->setSelectedProductsTempValues(null);

        $this->setChild(
            'productGrid',
            $this->getLayout()->createBlock(
                \Cminds\AdvancedPermissions\Block\Adminhtml\Role\Grid\Product::class,
                'roleProductsGrid'
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Get grid html.
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('productGrid');
    }

    /**
     * Get access level.
     *
     * @return int|string
     */
    public function getAccessLevel()
    {
        $roleId = $this->getRequest()->getParam(
            'rid'
        ) > 0 ? $this->getRequest()->getParam(
            'rid'
        ) : $this->coreRegistry->registry(
            'RID'
        );

        $advancedPermission = $this->advancedPermissionFactory->create()->load($roleId);
        if ($advancedPermission->getRoleId() === null
            || $advancedPermission->getRoleId() === ''
            || $advancedPermission->getIsProductLimit() == false
        ) {
            return '';
        }

        $roleProduct = $this->roleProductFactory->create()->load($roleId);
        $productAccessLevel = $roleProduct->getAccessLevel();

        return $productAccessLevel;
    }
}
