<?php

namespace Cminds\AdvancedPermissions\Plugin\Store;

use Cminds\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Cminds\AdvancedPermissions\Model\ResourceModel\AdvancedPermission\Collection as AdvancedPermissionCollection;
use Cminds\AdvancedPermissions\Model\RoleScopeFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Class StoreAdminGridPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Store
 */
class StoreAdminGridPlugin extends AbstractPlugin
{
    /**
     * @var AdvancedPermissionCollection
     */
    private $advancedPermissionCollection;

    /**
     * @var RoleScopeFactory
     */
    private $roleScopeFactory;

    /**
     * StoreAdminGridPlugin constructor.
     *
     * @param UserConfig                   $userConfig
     * @param ModuleConfig                 $moduleConfig
     * @param ManagerInterface             $messageManager
     * @param ResultFactory                $resultFactory
     * @param LayoutFactory                $layoutFactory
     * @param JsonFactory                  $resultJsonFactory
     * @param AdvancedPermissionCollection $advancedPermissionCollection
     * @param RoleScopeFactory             $roleScopeFactory
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        AdvancedPermissionCollection $advancedPermissionCollection,
        RoleScopeFactory $roleScopeFactory
    ) {
        $this->advancedPermissionCollection = $advancedPermissionCollection;
        $this->roleScopeFactory = $roleScopeFactory;

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
     * Filter admin store view grid by allowed websites or store views.
     *
     * @param \Magento\Store\Model\ResourceModel\Website\Grid\Collection $subject
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundJoinGroupAndStore(
        \Magento\Store\Model\ResourceModel\Website\Grid\Collection $subject,
        callable $proceed
    ) {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        if ($this->userConfig->currentUser === null) {
            return $proceed();
        }

        $role = $this->userConfig->currentUser->getRole();
        $roleId = $role->getId();
        $collection = $proceed();

        $advancedPermissionCollection = $this->advancedPermissionCollection
            ->addFieldToFilter('role_id', $roleId);

        if ($advancedPermissionCollection->count() > 0) {
            $advancedPermission = $advancedPermissionCollection->getFirstItem();

            if ($advancedPermission->getRoleId() != $roleId) {
                return $collection;
            }

            if ($advancedPermission->getIsScopeLimit() == true) {
                $roleScope = $this->roleScopeFactory
                    ->create()
                    ->load($advancedPermission->getRoleId());

                if ($roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {
                    if ($roleScope->getReferenceValue() !== null && $roleScope->getReferenceValue() !== '') {
                        $allowedStoresViews = explode(',', $roleScope->getReferenceValue());
                    } else {
                        $allowedStoresViews = [];
                    }

                    $collection->addFieldToFilter('store_id', ['in' => $allowedStoresViews]);

                    return $collection;
                } elseif ($roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES) {
                    if ($roleScope->getReferenceValue() !== null && $roleScope->getReferenceValue() !== '') {
                        $allowedWebisiteViews = explode(',', $roleScope->getReferenceValue());
                    } else {
                        $allowedWebisiteViews = [];
                    }

                    $collection->addFieldToFilter('website_id', ['in' => $allowedWebisiteViews]);

                    return $collection;
                }
            }
        }

        return $collection;
    }
}
