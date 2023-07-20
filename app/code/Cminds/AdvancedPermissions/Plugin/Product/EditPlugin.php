<?php

namespace Cminds\AdvancedPermissions\Plugin\Product;

use Cminds\AdvancedPermissions\Model\AdvancedPermission;
use Cminds\AdvancedPermissions\Model\RoleProductFactory;
use Cminds\AdvancedPermissions\Model\ResourceModel\AdvancedPermission\Collection as AdvancedPermissionCollection;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Class EditPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Product
 */
class EditPlugin extends AbstractPlugin
{
    const REDIRECT_PATH = 'catalog/product/index';

    /**
     * @var AdvancedPermissionCollection
     */
    private $advancedPermissionsCollection;

    /**
     * @var RoleProductFactory
     */
    private $roleProductFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * EditPlugin constructor.
     *
     * @param UserConfig                   $userConfig
     * @param ModuleConfig                 $moduleConfig
     * @param ManagerInterface             $messageManager
     * @param ResultFactory                $resultFactory
     * @param LayoutFactory                $layoutFactory
     * @param JsonFactory                  $resultJsonFactory
     * @param AdvancedPermissionCollection $advancedPermissionCollection
     * @param RoleProductFactory           $roleProductFactory
     * @param ProductRepositoryInterface   $productRepository
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        AdvancedPermissionCollection $advancedPermissionCollection,
        RoleProductFactory $roleProductFactory,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $userConfig,
            $moduleConfig,
            $messageManager,
            $resultFactory,
            $layoutFactory,
            $resultJsonFactory
        );

        $this->advancedPermissionsCollection = $advancedPermissionCollection;
        $this->roleProductFactory = $roleProductFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Edit $subject
     * @param $proceed
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute(\Magento\Catalog\Controller\Adminhtml\Product\Edit $subject, $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        $productId = $subject->getRequest()->getParam('id');

        $resourcesToCheck = $this->moduleConfig->getResourcesToCheck(
            $this->moduleConfig::PRODUCT_CREATE_EDIT_ALLOWED_RESOURCES
        );

        $hasUserAclAccess = $this->checkPermission($resourcesToCheck);
        $hasUserProductAccess = $this->hasUserAccessToTheProduct($productId);

        if ($hasUserAclAccess === true && $hasUserProductAccess === true) {
            return $proceed();
        } elseif ($hasUserAclAccess === true && $hasUserProductAccess === false) {
            return $this->redirectBack(
                self::REDIRECT_PATH,
                __('You do not have permission to edit product.')
            );
        } elseif($hasUserAclAccess === false) {
            return $this->redirectBack(
                self::REDIRECT_PATH,
                __('You do not have permission to edit product.')
            );
        }

        if ($this->hasUserAccessToTheProduct($productId)) {
            return $proceed();
        }
    }

    /**
     * Has user access to edit specific product.
     *
     * @param $productId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function hasUserAccessToTheProduct($productId)
    {
        $roleId = $this->userConfig->currentUser->getRole()->getId();

        $roleCollection = $this->advancedPermissionsCollection
            ->addFieldToFilter('role_id', $roleId);

        if ($roleCollection->getSize() == 0) {
            return true;
        }

        $role = $roleCollection->getFirstItem();
        $productLimit = $role->getProductLimit();

        if ($productLimit == 0) {
            return true;
        }

        $roleProduct = $this->roleProductFactory->create()->load($roleId);

        if ($roleProduct === null) {
            return false;
        }

        if ($roleProduct->getAccessLevel() == AdvancedPermission::ACCESS_TO_ALL_PRODUCTS) {
            return true;
        } elseif ($roleProduct->getAccessLevel() == AdvancedPermission::ACCESS_TO_SPECIFIED_PRODUCTS) {
            $allowedProducts = $roleProduct->getReferenceValue();

            if ($allowedProducts == '' || $allowedProducts == null) {
                $allowedProducts = [];
            } else {
                $allowedProducts = explode(',', $allowedProducts);
            }

            if (in_array($productId, $allowedProducts)) {
                return true;
            }

            return false;
        } elseif ($roleProduct->getAccessLevel() == AdvancedPermission::ACCESS_TO_OWN_CREATED_PRODUCTS) {
            $productEntity = $this->productRepository->getById($productId);
            $productOwner = $productEntity->getData('owner_user_id');
            if ($productOwner == $this->userConfig->currentUser->getId()) {
                return true;
            }

            return false;
        }

    }
}
