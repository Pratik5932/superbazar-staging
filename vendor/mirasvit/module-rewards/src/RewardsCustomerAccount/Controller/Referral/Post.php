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



namespace Mirasvit\RewardsCustomerAccount\Controller\Referral;

use Magento\Framework\Controller\ResultFactory;

class Post extends \Mirasvit\RewardsCustomerAccount\Controller\Referral
{
    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Try again'));
            $this->_redirect('*/*/');
            return;
        }

        $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $customer = $this->customerSession->getCustomer();
        $names = $this->getRequest()->getParam('name');
        $emails = $this->getRequest()->getParam('email', []);
        $invitations = [];
        foreach ($emails as $key => $email) {
            if (empty($email) || empty($names[$key])) {
                continue;
            }
            $invitations[$email] = $names[$key];
        }
        $message = $this->getRequest()->getParam('message');
        $rejectedEmails = $this->rewardsReferral->frontendPost($customer, $invitations, $message);
        if (count($rejectedEmails)) {
            foreach ($rejectedEmails as $email) {
                $this->messageManager->addNoticeMessage(
                    __('Customer with email %1 has been already invited to our store', $email)
                );
            }
        }
        if (count($rejectedEmails) < count($invitations)) {
            $this->messageManager->addSuccessMessage(__('Your invitations were sent. Thanks!'));
        }
        $this->_redirect('rewards/account/referral');
    }
}
