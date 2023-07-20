<?php

namespace Cminds\AdvancedPermissions\Plugin\Website;

use Cminds\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Cminds\AdvancedPermissions\Model\ResourceModel\AdvancedPermission\Collection as AdvancedPermissionCollection;
use Cminds\AdvancedPermissions\Model\ResourceModel\RoleScope\Collection as RoleScopeCollection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\User;

/**
 * Class AccessPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Website
 */
class AccessPlugin extends AbstractPlugin
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var User
     */
    private $user;

    /**
     * @var AdvancedPermissionCollection
     */
    private $advancedPermissionCollection;

    /**
     * @var RoleScopeCollection
     */
    private $roleScopeCollection;

    /**
     * AccessPlugin constructor.
     *
     * @param UserConfig                   $userConfig
     * @param ModuleConfig                 $moduleConfig
     * @param ManagerInterface             $messageManager
     * @param ResultFactory                $resultFactory
     * @param LayoutFactory                $layoutFactory
     * @param JsonFactory                  $resultJsonFactory
     * @param StoreManagerInterface        $storeManager
     * @param User                         $user
     * @param AdvancedPermissionCollection $advancedPermissionCollection
     * @param RoleScopeCollection          $roleScopeCollection
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        User $user,
        AdvancedPermissionCollection $advancedPermissionCollection,
        RoleScopeCollection $roleScopeCollection
    ) {
        $this->advancedPermissionCollection = $advancedPermissionCollection;
        $this->roleScopeCollection = $roleScopeCollection;
        $this->user = $user;
        $this->storeManager = $storeManager;

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
     * Check for website restrictions for user role.
     *
     * @param \Magento\Backend\Model\Auth $subject
     * @param callable $proceed
     * @param $username
     * @param $password
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function aroundLogin(\Magento\Backend\Model\Auth $subject, callable $proceed, $username, $password)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed($username, $password);
        }

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $user = $this->user->load($username, 'username');
        $userRole = $user->getRole();
        $hasUserAccess = true;

        // check is user role allowing to see subject website
        $advancedPermission = $this->advancedPermissionCollection
            ->addFieldToFilter('role_id', $userRole->getId())
            ->getFirstItem();

        if ($advancedPermission->getRoleId() === $userRole->getId()) {
            if ($advancedPermission->getIsScopeLimit() == true) {
                $roleScope = $this->roleScopeCollection
                    ->addFieldToFilter(
                        'role_id',
                        $advancedPermission->getRoleId()
                    )->getFirstItem();
                if ($roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES) {
                    $websites = explode(',',$roleScope->getReferenceValue());

                    $hasUserAccess = in_array($websiteId, $websites) ? true : false;
                }
            }
        }

        if ($hasUserAccess === false) {
//            $subject->logout();
            $subject::throwException(__('You do not have access to this website.'));
        }

        return $proceed($username, $password);
    }
}
