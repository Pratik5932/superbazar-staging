<?php

namespace Cminds\AdvancedPermissions\Plugin\Category;

use Cminds\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Cminds\AdvancedPermissions\Model\RoleCategoryFactory;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Class FilterCategoryPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Category
 */
class FilterCategoryPlugin extends AbstractPlugin
{
    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * @var RoleCategoryFactory
     */
    private $roleCategoryFactory;

    /**
     * @var UserConfig
     */
    protected $userConfig;

    /**
     * FilterCategoryPlugin constructor.
     *
     * @param UserConfig                $userConfig
     * @param ModuleConfig              $moduleConfig
     * @param ManagerInterface          $messageManager
     * @param ResultFactory             $resultFactory
     * @param LayoutFactory             $layoutFactory
     * @param JsonFactory               $resultJsonFactory
     * @param AdvancedPermissionFactory $advancedPermissionFactory
     * @param RoleCategoryFactory       $roleCategoryFactory
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        AdvancedPermissionFactory $advancedPermissionFactory,
        RoleCategoryFactory $roleCategoryFactory

    ) {
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleCategoryFactory = $roleCategoryFactory;

        parent::__construct(
            $userConfig,
            $moduleConfig,
            $messageManager,
            $resultFactory,
            $layoutFactory,
            $resultJsonFactory
        );
    }

    /**
     * Filter all category collections to allowed category ids.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $subject
     * @param callable $proceed
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundLoad(\Magento\Catalog\Model\ResourceModel\Category\Collection $subject, callable $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        if ($this->userConfig->currentUser === null) {
            return $proceed();
        }

        $role = $this->userConfig->currentUser->getRole();
        $advancedPermission = $this->advancedPermissionFactory->create()->load($role->getId());

        if ($advancedPermission === null) {
            return $proceed();
        }

        if ($advancedPermission->getIsCategoryLimit() == false) {
            return $proceed();
        }

        $roleCategory = $this->roleCategoryFactory->create()->load($role->getId());

        if ($roleCategory === null) {
            return $proceed();
        }

        if ($roleCategory->getReferenceValue() === '' || $roleCategory->getReferenceValue() === null) {
            $allowedCategoryIds = null;
        } else {
            $allowedCategoryIds = explode(',', $roleCategory->getReferenceValue());
        }

        $subject->addAttributeToFilter('entity_id', ['in' => $allowedCategoryIds]);

        return $proceed();
    }
}
