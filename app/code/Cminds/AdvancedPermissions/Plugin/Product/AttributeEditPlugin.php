<?php

namespace Cminds\AdvancedPermissions\Plugin\Product;

use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;

/**
 * Class AttributeEditPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Product
 */
class AttributeEditPlugin extends AbstractPlugin
{
    const REDIRECT_PATH = 'catalog/product/index';

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Edit $subject
     * @param $proceed
     *
     * @return mixed
     */
    public function aroundExecute(\Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Edit $subject, $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        $resourcesToCheck = $this->moduleConfig->getResourcesToCheck(
            $this->moduleConfig::PRODUCT_CREATE_EDIT_ALLOWED_RESOURCES
        );

        if ($this->checkPermission($resourcesToCheck)) {
            return $proceed();
        }

        return $this->redirectBack(
            self::REDIRECT_PATH,
            __('You do not have permission to edit product.')
        );
    }
}
