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
 * Class IndexPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Category
 */
class IndexPlugin extends AbstractPlugin
{
    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * @var RoleCategoryFactory
     */
    private  $roleCategoryFactory;

    /**
     * IndexPlugin constructor.
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
     * Redirect to the dashboard if user does not have access to any categories.
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Category\Index $subject
     * @param callable $proceed
     * @param $request
     *
     * @return mixed
     */
    public function aroundDispatch(
        \Magento\Catalog\Controller\Adminhtml\Category\Index $subject,
        callable $proceed,
        $request
    ) {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed($request);
        }

        $role = $this->userConfig->currentUser->getRole();
        $advancedPermission = $this->advancedPermissionFactory->create()->load($role->getId());

        if ($advancedPermission === null) {
            return $proceed($request);
        }

        if ($advancedPermission->getIsCategoryLimit() == false) {
            return $proceed($request);
        }

        $roleCategory = $this->roleCategoryFactory->create()->load($role->getId());
        $referenceValue =  $roleCategory->getReferenceValue();
        if ($referenceValue === '1' || $referenceValue === '' || $referenceValue === null) {
            return $this->redirectBack(
                'admin/dashboard/index',
                __('You do not have access to any category.'));
        }

        return $proceed($request);
    }
}
