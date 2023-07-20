<?php

namespace Cminds\AdvancedPermissions\Plugin\Order;

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
use Magento\Sales\Model\ResourceModel\Order\Address\Collection;

/**
* Class OrderGridPlugin
*
* @package Cminds\AdvancedPermissions\Plugin\Order
*/
class OrderGridPlugin extends AbstractPlugin
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
    private $orderAddressCollection;

    /**
    * OrderGridPlugin constructor.
    *
    * @param UserConfig                   $userConfig
    * @param ModuleConfig                 $moduleConfig
    * @param ManagerInterface             $messageManager
    * @param ResultFactory                $resultFactory
    * @param LayoutFactory                $layoutFactory
    * @param JsonFactory                  $resultJsonFactory
    * @param AdvancedPermissionCollection $advancedPermissionCollection
    * @param RoleScopeFactory             $roleScopeFactory
    * @param Collection                    $orderAddressCollection
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
        Collection $orderAddressCollection
    ) {
        $this->advancedPermissionCollection = $advancedPermissionCollection;
        $this->roleScopeFactory = $roleScopeFactory;
        $this->orderAddressCollection = $orderAddressCollection;

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
    * Filter order grid index.
    *
    * @param \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject
    * @param callable $proceed
    *
    * @return mixed
    */
    public function aroundGetItems(\Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject, callable $proceed)
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
           # PRINT_R($postCodes);exit;
            $shippingPostcode = $this->userConfig->currentUser->getData('post_code');
            if (!empty($postCodes)) {
                $this->filterByPostCode($postCodes, $subject,$shippingPostcode);
            }

            if ($advancedPermission->getIsScopeLimit() == true) {
                $roleScope = $this->roleScopeFactory
                ->create()
                ->load($advancedPermission->getRoleId());

                if ($roleScope->getAccessLevel() != AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {
                    return $proceed();
                }

                if ($roleScope->getReferenceValue() === '' || $roleScope->getReferenceValue() === null) {
                    $allowedStoresViews = [];
                } else {
                    $allowedStoresViews = explode(',', $roleScope->getReferenceValue());
                }

                $subject->addFieldToFilter('store_id',['in' => $allowedStoresViews]);
                return $proceed();
            }
        }

        return $proceed();
    }

    /**
    * @param string $postCodes
    * @param \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject
    */
    private function filterByPostCode($postCodes, &$subject,$shippingPostcode)
    {
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
       # print_r($sellerId);exit;
        $collectionpostcode = $objectManager->get('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
        ->getCollection()
        ->addFieldToSelect('postcode')
        ->addFieldToFilter('address_type', 'postcode')          
        ->addFieldToFilter('seller_id', $sellerId);
        $postCodes = $collectionpostcode->getColumnValues('postcode');
        
       # print_r($postCodes);exit;

        $addressCollection = $this->orderAddressCollection->addFieldToFilter('postcode', ['in' => $postCodes]);
        if ($addressCollection->getSize()) {
            $parentIds = array_unique($addressCollection->getColumnValues('parent_id'));
            $subject->addFieldToFilter('entity_id', ['in' => $parentIds]);
            $subject->getSelect()->group('main_table.entity_id');
        }
    }
}
