<?php

namespace Cminds\AdvancedPermissions\Plugin\Product;

use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;

/**
 * Class Grid
 *
 * @package Cminds\AdvancedPermissions\Plugin\Product
 */
class GridPlugin
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
     * Grid constructor.
     *
     * @param ModuleConfig $moduleConfig
     * @param UserConfig   $userConfig
     */
    public function __construct(
        ModuleConfig $moduleConfig,
        UserConfig $userConfig
    )
    {
        $this->moduleConfig = $moduleConfig;
        $this->userConfig = $userConfig;
    }

    /**
     * Remove action column from grid.
     *
     * @param \Magento\Catalog\Ui\Component\Listing\Columns $subject
     * @param $result
     *
     * @return \Magento\Catalog\Ui\Component\Listing\Columns
     */
    public function afterPrepare(\Magento\Catalog\Ui\Component\Listing\Columns $subject, $result)
    {
        if ($this->moduleConfig->isActive() === false) {
            return $subject;
        }

        // check for admin user roles
        $resources = $this->userConfig->getCurrentUserResources();
        $resourcesToCheck = $this->moduleConfig->getResourcesToCheck(
            $this->moduleConfig::PRODUCT_CREATE_EDIT_ALLOWED_RESOURCES
        );

        // check is user has access to the resource
        $hasUserAccess = false;
        foreach ($resources as $resource) {
            if (in_array($resource, $resourcesToCheck) === true) {
                $hasUserAccess = true;
            }
        }

        if ($hasUserAccess === true) {
            return $subject;
        }

        // if user does not have access then disable action column
        $config = ['componentDisabled' => true];
        $actions = $subject->getComponent('actions')->setData('config', $config);

        return $subject;
    }
}
