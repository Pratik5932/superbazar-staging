<?php

namespace Cminds\AdvancedPermissions\Plugin\Store;

use Cminds\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Cminds\AdvancedPermissions\Api\RoleScopeInterface;
use Cminds\AdvancedPermissions\Model\ResourceModel\AdvancedPermission\Collection as AdvancedPermissionCollection;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Cminds\AdvancedPermissions\Model\RoleScopeFactory;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Class StoreViewPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Store
 */
class StoreViewPlugin extends AbstractPlugin
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
     * StoreViewPlugin constructor.
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
     * Catch loading collection with store views and filter them to allowed ones.
     *
     * @param \Magento\Store\Model\ResourceModel\Store\Collection $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetSelect(\Magento\Store\Model\ResourceModel\Store\Collection $subject, $result)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $result;
        }

        if ($this->userConfig->currentUser === null) {
            return $result;
        }

        $role = $this->userConfig->currentUser->getRole();
        $roleId = $role->getId();

        $advancedPermissionCollection = $this->advancedPermissionCollection
            ->addFieldToFilter('role_id', $roleId);

        if ($advancedPermissionCollection->count() > 0) {
            $advancedPermission = $advancedPermissionCollection->getFirstItem();

            if ($advancedPermission->getRoleId() !== $roleId) {
                return $result;
            }

            if ($advancedPermission->getIsScopeLimit() == true) {
                $roleScope = $this->roleScopeFactory
                    ->create()
                    ->load($advancedPermission->getRoleId());

                if ($roleScope->getAccessLevel() != AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {
                    return $result;
                }

                $allowedStoresViews = explode(',', $roleScope->getReferenceValue());
                $subject->addFieldToFilter('store_id', $allowedStoresViews);
            }
        }

        return $result;
    }
}
