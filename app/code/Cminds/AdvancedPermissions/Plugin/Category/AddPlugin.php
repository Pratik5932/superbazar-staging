<?php

namespace Cminds\AdvancedPermissions\Plugin\Category;

use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;

/**
 * Class AddPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Category
 */
class AddPlugin extends AbstractPlugin
{
    /**
     *
     */
    const REDIRECT_PATH = 'catalog/category/index';

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Category\Add $subject
     * @param $proceed
     *
     * @return mixed
     */
    public function aroundExecute(\Magento\Catalog\Controller\Adminhtml\Category\Add $subject, $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
           return $proceed();
        }

        $resourcesToCheck = $this->moduleConfig->getResourcesToCheck(
            $this->moduleConfig::CATEGORY_CREATE_EDIT_ALLOWED_RESOURCES
        );

        if ($this->checkPermission($resourcesToCheck)) {
           return $proceed();
        }

        return $this->redirectBack(
            self::REDIRECT_PATH,
            __('You do not have permission to add category.')
        );
    }
}
