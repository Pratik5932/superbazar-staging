<?php

namespace Cminds\AdvancedPermissions\Plugin\Sales;

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

use Cminds\AdvancedPermissions\Model\ResourceModel\AdvancedPermission\Collection as AdvancedPermissionCollection;

/**
 * Class ReportBestsellersCollectionPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Sales
 */
class ReportBestsellersCollectionPlugin extends AbstractPlugin
{
    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * @var AdvancedPermissionCollection
     */
    private $advancedPermissionCollection;

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
     * Catalog products table name
     *
     * @var string
     */
    protected $_bestsellersTable;

    /**
     * ReportBestsellersCollectionPlugin constructor.
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
     * @param StoreManagerInterface     $storeManagerInterface
     * @param AdvancedPermissionCollection $advancedPermissionCollection
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
        AdvancedPermissionCollection $advancedPermissionCollection,
        HttpRequest $request
    ) {
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->advancedPermissionCollection = $advancedPermissionCollection;

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
    public function aroundLoad(\Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection $subject, callable $proceed)
    {
        if (
            $this->checkIsModuleEnabled() === false
            || $this->userConfig->currentUser === null
        ) {
            return $proceed();
        }

        $role = $this->userConfig->currentUser->getRole();

        $advancedPermission = $this->advancedPermissionFactory->create()->load($role->getId());

        if ($advancedPermission === null) {
            return $proceed();
        }

        $advancedPermissionCollection = $this->advancedPermissionCollection
            ->addFieldToFilter('role_id', $role->getId());
        $advancedPermission = $advancedPermissionCollection->getFirstItem();

        if ($advancedPermission->getIsProductLimit() == false
            && $advancedPermission->getIsCategoryLimit() == false
            && $advancedPermission->getIsScopeLimit() == false
        ) {
            return $proceed();
        }

        // determine which table is used
        $period = 'yearly';
        if (!$subject->getPeriod()) {
            if ($subject->getFrom() || $subject->getTo()) {
                $period = 'daily';
            } else {
                $period = 'yearly';
            }
        }else{
            if ('year' == $subject->getPeriod() ) {
                $period = 'yearly';
            } elseif ('month' == $subject->getPeriod() ) {
                $period = 'monthly';
            } else {
                $period = 'daily';
            }
        }
        $this->_bestsellersTable = $subject->getTable( $subject->getTableByAggregationPeriod($period) );

        // filter by categories
        $roleCategory = $this->roleCategoryFactory->create()->load($role->getId());
        if ($advancedPermission->getIsCategoryLimit() && $roleCategory !== null) {

            if ($roleCategory->getReferenceValue() === '' || $roleCategory->getReferenceValue() === null) {
                $allowedCategoryIds = [];
            } else {
                $allowedCategoryIds = explode(',', $roleCategory->getReferenceValue());
            }

            // add category filtring
            $subject->join(
                    ['categoryProductTable' => $subject->getTable('catalog_category_product')],
                    $this->_bestsellersTable . '.product_id = categoryProductTable.product_id',
                    ['category_id' => 'categoryProductTable.category_id']
                )
                ->addFieldToFilter('category_id', ['in' => $allowedCategoryIds]);
            $this->_ruleAppliedFlag = true;
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

            $subject->addFieldToFilter(
                $this->_bestsellersTable.'.product_id',
                ['in' => $allowedProductIds]
            );
        } elseif ($advancedPermission->getIsProductLimit()
            && $roleProduct !== null
            && $roleProduct->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_OWN_CREATED_PRODUCTS
        ) {
            // filter by own created products
            $userId = $this->userConfig->currentUser->getId();
            $subject->join(
                    ['productsTable' => $subject->getTable('catalog_product_entity')],
                    $this->_bestsellersTable . '.product_id = productsTable.entity_id',
                    ['owner_user_id' => 'productsTable.owner_user_id']
                )
                ->addFieldToFilter('owner_user_id', ['eq' => $userId]);
            $this->_ruleAppliedFlag = true;
        }

        // filter by websites or stores
        $roleScope = $this->roleScopeFactory->create()->load($role->getId());
        if ($advancedPermission->getIsScopeLimit()
            && $roleScope !== null
        ) {
            $allowedValues = [];
            if( $roleScope->getReferenceValue() )
                $allowedValues = explode(',', $roleScope->getReferenceValue());

            // check for requst restrictions
            $requestId = false;
            if ($this->request->getParam('website')) {
                $requestIds = $this->storeManager->getWebsite($this->request->getParam('website') )->getStoreIds();
            } elseif ($this->request->getParam('group')) {
                $requestIds = $this->storeManager->getGroup( $this->request->getParam('group') )->getStoreIds();
            } elseif($this->request->getParam('store')) {
                $requestIds = [ $this->request->getParam('store') ];
            }

            // if( $roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS ) {
            // }

            if( $roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES ) {
                // don't forget to check if query contains webistes or stores
                $storesList = $this->storeManager->getStores(true, false);
                $stores = [];
                foreach($storesList as $store){
                    if( !in_array($store->getWebsiteId(), $allowedValues) ) continue;
                    $stores[] = $store->getId();
                }
                $allowedValues = $stores;
            }

            // apply restrictions
            if( $requestIds && count($requestIds) ) {
                $allowedValues = array_intersect( $allowedValues, $requestIds );
            }
            // in case if there are no intersects add big positive value to force an empty collection output
            if( !count($allowedValues) )
                $allowedValues[] = 10000;

            if( count($allowedValues) ) {
                $subject->addStoreFilter($allowedValues);
            }
        }

        return $proceed();
    }
}
