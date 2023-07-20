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



namespace Mirasvit\RewardsAdminUi\Controller\Adminhtml\Tier;

use Magento\Framework\Controller\ResultFactory;

class Add extends \Mirasvit\RewardsAdminUi\Controller\Adminhtml\Tier
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->_initTier();

        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('New Tier'));

        $warning = 'All changes will be applied by cron or ' .
            'by running this command \'bin/magento mirasvit:rewards:update-tiers\'.';
        $this->messageManager->addWarningMessage(__($warning));

        return $resultPage;
    }
}
