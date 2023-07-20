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

class Delete extends \Mirasvit\RewardsAdminUi\Controller\Adminhtml\Tier
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $spendingRule = $this->tierFactory->create();

                $spendingRule->setId($this->getRequest()
                    ->getParam('id'))
                    ->delete();

                $this->messageManager->addSuccessMessage(
                        __('Tier was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()
                    ->getParam('id'), ]);
            }
        }
        $this->_redirect('*/*/');
    }
}
