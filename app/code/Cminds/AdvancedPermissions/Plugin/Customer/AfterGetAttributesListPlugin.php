<?php

namespace Cminds\AdvancedPermissions\Plugin\Customer;

use Cminds\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Cminds\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Cminds\AdvancedPermissions\Model\RoleScopeFactory;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AfterGetAttributesList
 *
 * @package Cminds\AdvancedPermissions\Plugin\Customer
 */
class AfterGetAttributesListPlugin extends AbstractPlugin
{
    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * @var RoleScopeFactory
     */
    private $roleScopeFactory;

    private $storeManager;

    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        AdvancedPermissionFactory $advancedPermissionFactory,
        RoleScopeFactory $roleScopeFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleScopeFactory = $roleScopeFactory;
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

    public function afterGetList(AttributeRepository $subject, $result)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $result;
        }

        if ($this->userConfig->currentUser === null) {
            return $result;
        }
        if (!isset($result['website_id'])) {
            return $result;
        }

        $websiteData = $result['website_id'];
        $roleId = $this->userConfig->currentUser->getRole()->getId();
        $scopeRole = $this->roleScopeFactory->create()->load($roleId);

        if ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_ALL_STORES) {
            return $result;
        }

        $allowedIds = $scopeRole->getReferenceValue();
        if ($allowedIds === '' || $allowedIds === null) {
            unset($result['website_id']);
            return $result;
        }

        $allowedIds = explode(',', $allowedIds);

        if ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {
            $websiteIds = $this->getWebsiteIdsByStoreIds($allowedIds);
            foreach ($websiteData['options'] as $key => $website) {
                if (in_array($website['value'], $websiteIds) == false) {
                    unset($websiteData['options'][$key]);
                }
            }
            $result['website_id'] = $websiteData;
        } elseif ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES) {
            foreach ($websiteData['options'] as $key => $website) {
                if (in_array($website['value'], $allowedIds) == false) {
                    unset($websiteData['options'][$key]);
                }
            }
            $result['website_id'] = $websiteData;
        }

        return $result;
    }

    /**
     * @param array $storeIds
     * @return array
     */
    private function getWebsiteIdsByStoreIds(array $storeIds) : array
    {
        $websiteIds = [];
        foreach ($storeIds as $storeId) {
            try {
                $websiteIds[] = $this->storeManager->getStore($storeId)->getWebsiteId();
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }
        return $websiteIds;
    }
}
