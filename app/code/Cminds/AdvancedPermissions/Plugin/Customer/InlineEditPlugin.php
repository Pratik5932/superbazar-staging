<?php

namespace Cminds\AdvancedPermissions\Plugin\Customer;

use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;

/**
 * Class InlineEditPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Customer
 */
class InlineEditPlugin extends AbstractPlugin
{
    /**
     *
     */
    const REDIRECT_PATH = 'customer/index/index';

    /**
     * @param \Magento\Customer\Controller\Adminhtml\Index\InlineEdit $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return $this
     */
    public function aroundDispatch(
        \Magento\Customer\Controller\Adminhtml\Index\InlineEdit $subject,
        callable $proceed,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed($request);
        }

        $resourcesToCheck = $this->moduleConfig->getResourcesToCheck(
            $this->moduleConfig::CUSTOMER_CREATE_EDIT_ALLOWED_RESOURCES
        );

        if ($this->checkPermission($resourcesToCheck)) {
            return $proceed($request);
        }

        return $this->redirectBackAjax(
            __('You do not have permission to edit customer.')
        );
    }
}
