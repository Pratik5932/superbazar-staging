<?php

namespace Cminds\AdvancedPermissions\Plugin\Product;

use Cminds\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Cminds\AdvancedPermissions\Model\RoleScope;
use Cminds\AdvancedPermissions\Model\RoleScopeFactory;
use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Cminds\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Cminds\AdvancedPermissions\Model\RoleProductFactory;
use Cminds\AdvancedPermissions\Model\RoleCategoryFactory;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * Class ReportsCollectionLoadPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Product
 */
class ReportsCollectionLoadPlugin extends AbstractPlugin
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
     * @var RoleCategoryFactory
     */
    private $roleCategoryFactory;

    /**
     * @var RoleScopeFactory
     */
    private $roleScopeFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * ReportsCollectionLoadPlugin constructor.
     *
     * @param UserConfig                $userConfig
     * @param ModuleConfig              $moduleConfig
     * @param ManagerInterface          $messageManager
     * @param ResultFactory             $resultFactory
     * @param LayoutFactory             $layoutFactory
     * @param JsonFactory               $resultJsonFactory
     * @param AdvancedPermissionFactory $advancedPermissionFactory
     * @param RoleProductFactory        $roleProductFactory
     * @param RoleCategoryFactory       $roleCategoryFactory
     * @param RoleScopeFactory          $roleScopeFactory
     * @param HttpRequest               $request
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        AdvancedPermissionFactory $advancedPermissionFactory,
        RoleProductFactory $roleProductFactory,
        RoleCategoryFactory $roleCategoryFactory,
        RoleScopeFactory $roleScopeFactory,
        StoreManagerInterface $storeManagerInterface,
        HttpRequest $request
    ) {
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleProductFactory = $roleProductFactory;
        $this->roleCategoryFactory = $roleCategoryFactory;
        $this->roleScopeFactory = $roleScopeFactory;
        $this->storeManager = $storeManagerInterface;
        $this->request = $request;

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
     * Filter products collection.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundLoad(\Magento\Reports\Model\ResourceModel\Product\Collection $subject, callable $proceed)
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

        if ($advancedPermission->getIsProductLimit() == false
            && $advancedPermission->getIsCategoryLimit() == false
            && $advancedPermission->getIsScopeLimit() == false
        ) {
            return $proceed();
        }

        // filter by websites or stores
        $roleScope = $this->roleScopeFactory->create()->load($role->getId());
                        
        if ($advancedPermission->getIsScopeLimit()
            && $roleScope !== null
        ) {
            $allowedValues = [];

            // check for requst restrictions
            $requestId = false;
            if ($this->request->getParam('website')) {
                $requestId = $this->request->getParam('website');
            } elseif ($this->request->getParam('group')) {
                $requestId = $this->storeManager->getGroup( $this->request->getParam('group') )->getWebsiteId();
            } elseif($this->request->getParam('store')) {
                $requestId = $this->storeManager->getStore( $this->request->getParam('store') )->getWebsiteId();
            }

            if( $roleScope->getReferenceValue() )
                $allowedValues = explode(',', $roleScope->getReferenceValue());
            
                // go through all store ids, get associated websiites and then apply filters
            if( $roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS ) {
                // get stores collection
                $storesList = $this->storeManager->getStores(true, false);
                $websites = [];
                foreach($storesList as $store){
                    if( !in_array($store->getId(), $allowedValues) ) continue;
                    if( !in_array( $store->getWebsiteId(), $websites ) ) $websites[] = $store->getWebsiteId();
                }
                // $subject->addWebsiteFilter($websites);
                $allowedValues = $websites;
            }
            // if( $roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES ) {
                // $subject->addWebsiteFilter($allowedValues);
            // }
                        
            // apply restriction
            if( $requestId ) {
                $allowedValues = array_intersect( $allowedValues, [$requestId] );
            }
            // in case if there are no intersects add big positive value to force an empty collection output 
            if( !count($allowedValues) )
                $allowedValues[] = 10000;
            if( count($allowedValues) )
                $subject->addWebsiteFilter($allowedValues);
        }
            
        // filter by categories
        $roleCategory = $this->roleCategoryFactory->create()->load($role->getId());
        if ($advancedPermission->getIsCategoryLimit() && $roleCategory !== null) {
            if ($roleCategory->getReferenceValue() === '' || $roleCategory->getReferenceValue() === null) {
                $allowedCategoryIds = [];
            } else {
                $allowedCategoryIds = explode(',', $roleCategory->getReferenceValue());
            }

            $subject->addCategoriesFilter(['in' => $allowedCategoryIds]);
        }

        // filter by products
        $roleProduct = $this->roleProductFactory->create()->load($role->getId());
        if ($advancedPermission->getIsProductLimit()
            && $roleProduct !== null
            && $roleProduct->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_PRODUCTS
        ) {
            // filter by specified products
            if ($roleProduct->getReferenceValue() === '' || $roleProduct->getReferenceValue() === null) {
                $allowedProductIds = [];
            } else {
                $allowedProductIds = explode(',', $roleProduct->getReferenceValue());
            }

            $subject->addAttributeToFilter('entity_id', ['in' => $allowedProductIds]);
        } elseif ($advancedPermission->getIsProductLimit()
            && $roleProduct !== null
            && $roleProduct->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_OWN_CREATED_PRODUCTS
        ) {
            // filter by own created products
            $userId = $this->userConfig->currentUser->getId();
            $subject->addFilter('e.owner_user_id', $userId);
        }

        return $proceed();
    }
}
