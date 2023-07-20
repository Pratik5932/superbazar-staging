<?php

namespace Cminds\AdvancedPermissions\Model\User;

use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Backend\Model\Auth\Session;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 *
 * @package Cminds\AdvancedPermissions\Model\User
 */
class Config
{
    /**
     * @var AclRetriever
     */
    private $aclRetriever;

    /**
     * @var Session
     */
    private  $authSession;

    /**
     * @var \Magento\User\Model\User|null
     */
    public  $currentUser;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Config constructor.
     *
     * @param AclRetriever          $aclRetriever
     * @param Session               $authSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        AclRetriever $aclRetriever,
        Session $authSession,
        StoreManagerInterface $storeManager
    )
    {
        $this->aclRetriever = $aclRetriever;
        $this->authSession = $authSession;
        $this->storeManager = $storeManager;
        $this->currentUser = $this->authSession->getUser();
    }

    /**
     * Get current user resources.
     *
     * @return array
     */
    public function getCurrentUserResources()
    {
        $role = $this->currentUser->getRole();
        $userResources = $this->aclRetriever->getAllowedResourcesByRole($role->getId());

        return $userResources;
    }

    /**
     * Get all available websites.
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getAllWebsites()
    {
        $this->getStoreViewsTree();
        return $this->storeManager->getWebsites();
    }

    /**
     * Get store view tree.
     *
     * @return array;
     */
    public function getStoreViewsTree()
    {
        $storeViews = $this->storeManager->getStores();
        $storeViewsTree = [];

        foreach ($storeViews as $storeView) {
            $websiteId = $storeView->getWebsiteId();
            $websiteName = $this->storeManager->getWebsite($storeView->getWebsiteId())->getName();

            $websiteGroupId = $storeView->getStoreGroupId();
            $websiteGroupName = $this->storeManager->getGroup($storeView->getStoreGroupId())->getName();

            $storeViewId = $storeView->getId();
            $storeViewName = $storeView->getName();

            $storeViewsTree[$websiteId]['website_name'] = $websiteName;
            $storeViewsTree[$websiteId]['website_groups'][$websiteGroupId]['group_name'] = $websiteGroupName;

            $storeViewsTree[$websiteId]['website_groups'][$websiteGroupId]['group_stores'][$storeViewId]
                = $storeViewName;
        }

        return $storeViewsTree;
    }
}
