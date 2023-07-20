<?php

namespace Cminds\AdvancedPermissions\Plugin\Customer;

use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;

/**
 * Class SavePlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Customer
 */
class SavePlugin extends AbstractPlugin
{
    /**
     *
     */
    const REDIRECT_PATH = 'customer/index/index';

    /**
     * @param \Magento\Customer\Controller\Adminhtml\Index\Save $subject
     * @param $proceed
     *
     * @return mixed
     */
    public function aroundExecute(\Magento\Customer\Controller\Adminhtml\Index\Save $subject, $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        $resourcesToCheck = $this->moduleConfig->getResourcesToCheck(
            $this->moduleConfig::CUSTOMER_CREATE_EDIT_ALLOWED_RESOURCES
        );

        if ($this->checkPermission($resourcesToCheck)) {
            return $proceed();
        }

        return $this->redirectBack(
            self::REDIRECT_PATH,
            __('You do not have permission to edi customer.')
        );
    }
}
