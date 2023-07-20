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

class Save extends \Mirasvit\RewardsAdminUi\Controller\Adminhtml\Transaction
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            if (empty($data['in_transaction_user'])) {
                $this->messageManager->addErrorMessage('Please select customer in table');
                $this->_redirect('*/*/add');
                return;
            }
            try {
                foreach ($data['in_transaction_user'] as $customerId) {
                    if ((int) $customerId > 0) {
                        $emailMessage = '';
                        if (isset($data['email_message'])) {
                            $emailMessage = $data['email_message'];
                        }
                        $this->rewardsBalance->changePointsBalance(
                            $customerId,
                            $data['amount'],
                            $data['history_message'],
                            false,
                            false,
                            true,
                            $emailMessage
                        );
                    }
                }

                $this->messageManager->addSuccessMessage(__('Transaction was successfully saved'));
                $this->backendSession->setFormData(false);

                $this->_redirect('*/*/index');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->backendSession->setFormData($data);
                $this->_redirect('*/*/index');

                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find Transaction to save'));
        $this->_redirect('*/*/');
    }
}
