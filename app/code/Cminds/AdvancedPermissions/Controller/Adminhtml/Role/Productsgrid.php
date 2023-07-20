<?php

namespace Cminds\AdvancedPermissions\Controller\Adminhtml\Role;

use Cminds\AdvancedPermissions\Block\Adminhtml\Role\Grid\Product;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Registry;
use Magento\User\Model\UserFactory;

/**
 * Class Productsgrid
 *
 * @package Cminds\AdvancedPermissions\Controller\Adminhtml\Role
 */
class Productsgrid extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * @var Product
     */
    private $gridProducts;

    /**
     * Productsgrid constructor.
     *
     * @param Context       $context
     * @param Registry      $coreRegistry
     * @param RoleFactory   $roleFactory
     * @param UserFactory   $userFactory
     * @param RulesFactory  $rulesFactory
     * @param Session       $authSession
     * @param FilterManager $filterManager
     * @param Product       $gridProducts
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        RoleFactory $roleFactory,
        UserFactory $userFactory,
        RulesFactory $rulesFactory,
        Session $authSession,
        FilterManager $filterManager,
        Product $gridProducts
    ) {
        $this->gridProducts = $gridProducts;

        parent::__construct(
            $context,
            $coreRegistry,
            $roleFactory,
            $userFactory,
            $rulesFactory,
            $authSession,
            $filterManager
        );
    }

    /**
     * Action for ajax request from assigned products grid
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $newTempValues = $this->getRequest()->getParam('selected_products');
        $this->gridProducts->setSelectedProducts($newTempValues);

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
