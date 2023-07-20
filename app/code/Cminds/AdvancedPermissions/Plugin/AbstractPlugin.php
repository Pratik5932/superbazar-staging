<?php

namespace Cminds\AdvancedPermissions\Plugin;

use Cminds\AdvancedPermissions\Model\User\Config as UserConfig;
use Cminds\AdvancedPermissions\Model\Config as ModuleConfig;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Class AbstractPlugin
 *
 * @package Cminds\AdvancedPermissions\Plugin
 */
abstract class AbstractPlugin
{
    /**
     * @var UserConfig
     */
    protected $userConfig;

    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * AbstractPlugin constructor.
     *
     * @param UserConfig                   $userConfig
     * @param ModuleConfig                 $moduleConfig
     * @param ManagerInterface             $messageManager
     * @param ResultFactory                $resultFactory
     * @param LayoutFactory                $layoutFactory
     * @param JsonFactory                  $resultJsonFactory
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->userConfig = $userConfig;
        $this->moduleConfig = $moduleConfig;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->layoutFactory = $layoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Check is module enabled.
     *
     * @return bool
     */
    protected function checkIsModuleEnabled()
    {
        return $this->moduleConfig->isActive();
    }

    /**
     * Redirect back.
     *
     * @param $path
     * @param $message
     *
     * @return mixed
     */
    protected function redirectBack($path, $message)
    {
        $this->messageManager->addErrorMessage($message);

        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT)
            ->setPath($path);
    }

    /**
     * Redirect back with Ajax.
     *
     * @param $message
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function redirectBackAjax($message)
    {
        $this->messageManager->addErrorMessage($message);
        $block = $this->layoutFactory->create()->getMessagesBlock();
        $block->setMessages($this->messageManager->getMessages(true));
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData([
            'messages' => $block->getGroupedHtml()
        ]);
    }

    /**
     * Check current user access to resources.
     *
     * @param $accessResources
     *
     * @return bool
     */
    protected function checkPermission($accessResources)
    {
        // check for admin user roles
        $resources = $this->userConfig->getCurrentUserResources();

        // check is user has access to the resource
        $hasUserAccess = false;
        foreach ($resources as $resource) {
            if (in_array(
                    $resource,
                    $accessResources
                ) === true
            ) {
                $hasUserAccess = true;
            }
        }

        return $hasUserAccess;
    }
}
