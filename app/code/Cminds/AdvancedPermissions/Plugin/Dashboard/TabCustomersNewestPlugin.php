<?php

namespace Cminds\AdvancedPermissions\Plugin\Dashboard;

use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Model\ResourceModel\AdvancedPermission\Collection as AdvancedPermissionCollection;
use Cminds\AdvancedPermissions\Model\RoleScopeFactory;
use Cminds\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Class TabCustomersNewestPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Dashboard
 */
class TabCustomersNewestPlugin extends AbstractPlugin
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
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * TabCustomersNewestPlugin constructor.
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
        RoleScopeFactory $roleScopeFactory,
        Registry $coreRegistry,
        StoreManagerInterface $storeManagerInterface      
    ) {
        $this->advancedPermissionCollection = $advancedPermissionCollection;
        $this->roleScopeFactory = $roleScopeFactory;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManagerInterface;        

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
     * Filter customer grid.
     *
     * @param \Magento\Customer\Model\ResourceModel\Grid\Collection $subject
     * @param callable $proceed
     *
     * @return mixed
     */
    public function afterGetCollection(\Magento\Backend\Block\Dashboard\Tab\Customers\Newest $subject, $result)
    {
        if (
            $this->checkIsModuleEnabled() === false
            || $this->userConfig->currentUser === null
            || $this->coreRegistry->registry('cminds_advancedpermissions_dash_tab_newest_customer_modified')
        ) {
            return $result;
        }
        
        $role = $this->userConfig->currentUser->getRole();
        $roleId = $role->getId();

        $advancedPermissionCollection = $this->advancedPermissionCollection
            ->addFieldToFilter('role_id', $roleId);

        if ( $result && $advancedPermissionCollection->count() > 0) {

            $advancedPermission = $advancedPermissionCollection->getFirstItem();
    
            if ( $advancedPermission->getIsScopeLimit() == true ) {
                $roleScope = $this->roleScopeFactory
                    ->create()
                    ->load($advancedPermission->getRoleId());

                $allowedValues = [];
                if( $roleScope->getReferenceValue() )
                    $allowedValues = explode(',', $roleScope->getReferenceValue());
                if( count( $allowedValues ) ) {
                    if( $roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS ) {
                        $storesList = $this->storeManager->getStores(true, false);
                        $stores = [];
                        foreach($storesList as $store){
                            if( !in_array($store->getWebsiteId(), $allowedValues) ) continue;
                            $stores[] = $store->getId();
                        }
                        $allowedValues = $stores;
                    }
                    // elseif( $roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES ) {
                    // }
                    $result->addFieldToFilter('store_id', [ 'in'=>$allowedValues ] );
                    $this->coreRegistry->register('cminds_advancedpermissions_dash_tab_newest_customer_modified', true);
                }
            }
        }
        return $result;
    }
}
