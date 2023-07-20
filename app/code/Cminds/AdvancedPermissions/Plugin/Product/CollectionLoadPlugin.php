<?php

namespace Cminds\AdvancedPermissions\Plugin\Product;

use Cminds\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Cminds\AdvancedPermissions\Model\Config;
use Cminds\AdvancedPermissions\Model\RoleScope;
use Cminds\AdvancedPermissions\Model\RoleScopeFactory;
use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Cminds\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Cminds\AdvancedPermissions\Model\RoleProductFactory;
use Cminds\AdvancedPermissions\Model\RoleCategoryFactory;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;
use \Magento\Eav\Model\Config as EavConfig;

/**
 * Class CollectionLoadPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Product
 */
class CollectionLoadPlugin extends AbstractPlugin
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
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * CollectionLoadPlugin constructor.
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
     * @param EavConfig                 $eavConfig
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
        EavConfig $eavConfig
    ) {
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleProductFactory = $roleProductFactory;
        $this->roleCategoryFactory = $roleCategoryFactory;
        $this->roleScopeFactory = $roleScopeFactory;
        $this->eavConfig = $eavConfig;

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
    public function aroundLoad(\Magento\Catalog\Model\ResourceModel\Product\Collection $subject, callable $proceed)
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
        /**@var \Magento\User\Model\ResourceModel\User\Collection $obj*/
       /* $obj = ObjectManager::getInstance()->get(\Magento\User\Model\ResourceModel\User\Collection::class);
        var_dump($this->userConfig->currentUser->getUserId());
        echo($obj->addFieldToFilter('main_table.user_id', $this->userConfig->currentUser->getUserId())->getSelect());die;*/
        if (!empty($this->userConfig->currentUser->getPostCode())) {
            
           # echo $this->userConfig->currentUser->getPostCode();exit;
            $this->filterByPostCodes($this->userConfig->currentUser->getPostCode(), $subject);
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
            && $roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES
        ) {
            if ($roleScope->getReferenceValue() === '' || $roleScope->getReferenceValue() === null) {
                $allowedWebsiteIds = [];
            } else {
                $allowedWebsiteIds = explode(',', $roleScope->getReferenceValue());
            }

            $subject->addWebsiteFilter($allowedWebsiteIds);
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

    /**
     * @param string $postCodes
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     */
    private function filterByPostCodes($postCodes, &$subject)
    {
        $postCodes = $this->getPostCodesAsArray($postCodes);
        #print_r($postCodes);exit;
        $attributeOptions = $this->getAttributeOptions();
        if (!empty($attributeOptions)) {
            $filterOptionIds = [];
            foreach ($attributeOptions as $option) {
                if (!empty($option['value']) && in_array($option['label'], $postCodes)) {
                    $filterOptionIds[] = $option['value'];
                }
            }
            $subject->addAttributeToFilter(
                Config::PRODUCT_POST_CODE_ATTRIBUTE,
                ['in' => $filterOptionIds]
            );
        }
    }

    /**
     * @param $postCodes
     * @return array
     */
    private function getPostCodesAsArray($postCodes)
    {
        return array_map(
            function ($item) {
                if (!empty(trim($item))) {
                    return trim($item);
                }
            }, explode(',', $postCodes)
        );
    }

    /**
     * @return array|bool
     */
    private function getAttributeOptions()
    {
        try {
            $eavAttribute = $this->eavConfig->getAttribute(
                Product::ENTITY,
                Config::PRODUCT_POST_CODE_ATTRIBUTE
            );
            $source = $eavAttribute->getSource();
        } catch (LocalizedException $e) {
            return false;
        }

        return !empty($source->getAllOptions())
            ? $source->getAllOptions()
            : false;
    }
}
