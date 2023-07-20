<?php

namespace Cminds\AdvancedPermissions\Observer\Product;

use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ProductSaveBeforeObserver
 *
 * @package Cminds\AdvancedPermissions\Observer\Product
 */
class ProductSaveBeforeObserver implements ObserverInterface
{
    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var UserConfig
     */
    private $userConfig;

    /**
     * ProductSaveBeforeObserver constructor.
     *
     * @param ModuleConfig $moduleConfig
     * @param UserConfig   $userConfig
     */
    public function __construct(
        ModuleConfig $moduleConfig,
        UserConfig $userConfig
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->userConfig = $userConfig;
    }

    /**
     * Save creator user id into 'catalog_product_entity' table.
     * Only for new products.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleConfig->isActive() === false) {
            return;
        }

        $product = $observer->getProduct();

        if ($product->isObjectNew() !== true) {
            return;
        }

        $userId = $this->userConfig->currentUser->getId();
        $product->setData('owner_user_id', $userId);
    }
}
