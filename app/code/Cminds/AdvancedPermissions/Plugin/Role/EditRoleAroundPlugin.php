<?php

namespace Cminds\AdvancedPermissions\Plugin\Role;

use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Plugin\AbstractPlugin;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;

/**
 * Class EditRoleAroundPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin\Role
 */
class EditRoleAroundPlugin extends AbstractPlugin
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * EditRoleAroundPlugin constructor.
     *
     * @param UserConfig       $userConfig
     * @param ModuleConfig     $moduleConfig
     * @param ManagerInterface $messageManager
     * @param ResultFactory    $resultFactory
     * @param LayoutFactory    $layoutFactory
     * @param JsonFactory      $resultJsonFactory
     * @param Registry         $registry
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        Registry $registry
    ) {
        $this->registry = $registry;

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
     * Save current role id for next steps.
     *
     * @param \Magento\User\Controller\Adminhtml\User\Role\EditRole $subject
     * @param $proceed
     *
     * @return mixed
     */
    public function aroundExecute(\Magento\User\Controller\Adminhtml\User\Role\EditRole $subject, $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        $ruleId = $subject->getRequest()->getParam('rid');

        $this->registry->register('cminds_advancedpermissions_edited_rule_id', $ruleId);

        return $proceed();
    }
}
