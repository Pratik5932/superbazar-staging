<?php

namespace Cminds\AdvancedPermissions\Plugin\Customer;

use Cminds\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Cminds\AdvancedPermissions\Model\RoleScopeFactory;
use Cminds\AdvancedPermissions\Model\ResourceModel\AdvancedPermission\Collection as AdvancedPermissionCollection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Customer\Model\ResourceModel\Address\Collection;

/**
* Class CustomerGridCollectionPlugin
*
* @package Cminds\AdvancedPermissions\Plugin\Customer
*/
class CustomerGridCollectionPlugin extends AbstractPlugin
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
    * @var Collection
    */
    private $addressCollection;

    /**
    * CustomerGridCollectionPlugin constructor.
    *
    * @param UserConfig                   $userConfig
    * @param ModuleConfig                 $moduleConfig
    * @param ManagerInterface             $messageManager
    * @param ResultFactory                $resultFactory
    * @param LayoutFactory                $layoutFactory
    * @param JsonFactory                  $resultJsonFactory
    * @param AdvancedPermissionCollection $advancedPermissionCollection
    * @param RoleScopeFactory             $roleScopeFactory
    * @param Collection                   $addressCollection
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
        Collection $addressCollection
    ) {
        $this->advancedPermissionCollection = $advancedPermissionCollection;
        $this->roleScopeFactory = $roleScopeFactory;
        $this->addressCollection = $addressCollection;

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
    public function aroundGetItems(\Magento\Customer\Model\ResourceModel\Grid\Collection $subject, callable $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        if ($this->userConfig->currentUser === null) {
            return $proceed();
        }
        $role = $this->userConfig->currentUser->getRole();
        $roleId = $role->getId();
        $advancedPermissionCollection = $this->advancedPermissionCollection
        ->addFieldToFilter('role_id', $roleId);

        if ($advancedPermissionCollection->count() > 0) {
            $advancedPermission = $advancedPermissionCollection->getFirstItem();

            if ($advancedPermission->getRoleId() != $roleId) {
                return $proceed();
            }

            $postCodes = $this->userConfig->currentUser->getData('post_codes');
            $shippingPostcode = $this->userConfig->currentUser->getData('post_code');
            # print_r($postCodes);exit;
            if (!empty($postCodes)) {
                $this->filterByPostCode($postCodes, $subject,$shippingPostcode);
            }

            if ($advancedPermission->getIsScopeLimit() == true) {

                $roleScope = $this->roleScopeFactory
                ->create()
                ->load($advancedPermission->getRoleId());

                if ($roleScope->getReferenceValue() === '' || $roleScope->getReferenceValue() === null) {
                    $allowedStoresViews = [];
                } else {
                    $allowedStoresViews = explode(',', $roleScope->getReferenceValue());
                }


                if ($roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES) {

                    $joinTable = $subject->getTable('customer_entity');
                    $subject
                    ->getSelect()
                    ->join($joinTable,'main_table.entity_id = customer_entity.entity_id', ['store_id']);
                    $subject->addFieldToFilter('customer_entity.website_id',['in' => $allowedStoresViews]);

                } elseif ($roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {

                    $joinTable = $subject->getTable('customer_entity');
                    $subject
                    ->getSelect()
                    ->join($joinTable,'main_table.entity_id = customer_entity.entity_id', ['store_id']);
                    $subject->addFieldToFilter('store_id',['in' => $allowedStoresViews]);

                }

                return $proceed();
            }
        }

        return $proceed();
    }

    /**
    * @param string $postCodes
    * @param \Magento\Customer\Model\ResourceModel\Grid\Collection $subject
    */
    private function filterByPostCode($postCodes, &$subject,$shippingPostcode)
    {
        # echo $shippingPostcode;exit;
        $postCodes = array_map(
            function ($item) {
                return trim($item);
            }, explode(',', $postCodes)
        );
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $postCodes = [];
        $collection = $objectManager->get('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
        ->getCollection()
        ->addFieldToSelect('seller_id')
        ->addFieldToFilter('address_type', 'postcode')          
        ->addFieldToFilter('postcode', $shippingPostcode);
        $sellerId = $collection->getColumnValues('seller_id');         
        $collectionpostcode = $objectManager->get('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
        ->getCollection()
        ->addFieldToSelect('postcode')
        ->addFieldToFilter('address_type', 'postcode')          
        ->addFieldToFilter('seller_id', $sellerId);
        $postCodes = ($collectionpostcode->getColumnValues('postcode'));
        $postCodes1= array_map('trim', $postCodes);

        #rint_r($postCodes);exit;

        $addressCollection = $this->addressCollection->addFieldToFilter('postcode', ['in' => $postCodes1]);

       #cho $addressCollection->getSelect()->__toString();exit;
        if ($addressCollection->getSize()) {
            $parentIds = array_unique($addressCollection->getColumnValues('parent_id'));
            # print_r($parentIds);exit;
            $subject->addFieldToFilter('entity_id', ['in' => $parentIds]);
        }
    }
}
