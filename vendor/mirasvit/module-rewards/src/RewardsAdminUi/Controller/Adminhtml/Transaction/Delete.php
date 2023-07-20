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



namespace Mirasvit\RewardsAdminUi\Controller\Adminhtml\Transaction;

use Magento\Framework\Controller\ResultFactory;

class Delete extends \Mirasvit\RewardsAdminUi\Controller\Adminhtml\Transaction
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $transaction = $this->transactionFactory->create();

                $transaction->setId($this->getRequest()
                    ->getParam('id'))
                    ->delete();

                $this->messageManager->addSuccess(
                        __('Transaction was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()
                    ->getParam('id'), ]);
            }
        }
        $this->_redirect('*/*/');
    }
}
