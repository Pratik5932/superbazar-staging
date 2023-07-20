<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\RewardsAdminUi\Controller\Adminhtml;

use Magento\Framework\App\Response\Http\FileFactory;

abstract class Tier extends \Magento\Backend\App\Action
{
    protected $tierFactory;
    protected $registry;
    protected $storeManager;
    protected $context;
    protected $backendSession;
    protected $resultFactory;

    public function __construct(
        \Mirasvit\Rewards\Model\TierFactory $tierFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->tierFactory     = $tierFactory;
        $this->registry        = $registry;
        $this->storeManager    = $storeManager;
        $this->context         = $context;

        parent::__construct($context);

        $this->backendSession = $context->getSession();
        $this->resultFactory  = $context->getResultFactory();
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Rewards::rewards');
        $resultPage->getConfig()->getTitle()->prepend(__('Reward Points'));
        $resultPage->getConfig()->getTitle()->prepend(__('Tiers'));

        return $resultPage;
    }

    /**
     * @return \Mirasvit\Rewards\Model\Tier
     */
    public function _initTier()
    {
        $tier = $this->tierFactory->create();
        $tierId = (int)$this->getRequest()->getParam('id');
        if ($tierId) {
            $tier->getResource()->load($tier, $tierId);
            $storeId = (int) $this->getRequest()->getParam('store');
            $tier->setStoreId($storeId);
        }

        $this->registry->register('current_tier', $tier);

        return $tier;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Rewards::reward_points_tier');
    }
}
