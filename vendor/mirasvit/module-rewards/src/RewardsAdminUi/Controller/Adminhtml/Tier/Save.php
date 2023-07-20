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
use Mirasvit\Rewards\Model\Tier\Backend\FileProcessor;

class Save extends \Mirasvit\RewardsAdminUi\Controller\Adminhtml\Tier
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($data = $this->getRequest()->getParams()) {
            $tier = $this->_initTier();
            $tierLogo = '';
            if (!empty($data['tier_logo'])) {
                $tierLogo = $data['tier_logo'];
                unset($data['tier_logo']);
            }
            $tier->addData($data);

            if ($tier->getTierLogo() && empty($tierLogo)) {
                $tier->setTierLogo('');
            }
            try {
                if (!empty($tierLogo) && isset($tierLogo[0])) {
                    if (isset($tierLogo[0]['tmp_name'])) {
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        /** @var \Mirasvit\Rewards\Model\Tier\Backend\FileProcessor $fileProcessor */
                        $fileProcessor = $objectManager->create('Mirasvit\Rewards\Model\Tier\Backend\FileProcessor');
                        $logoData      = $fileProcessor->moveTmpToFile($tierLogo[0]);
                        $tier->setTierLogo($logoData['file']);
                    } else {
                        $tier->setTierLogo($tierLogo[0]['file']);
                    }
                }
                $tier->getResource()->save($tier);

                $this->messageManager->addSuccessMessage(__('Spending Rule was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit',
                        ['id' => $tier->getId(), 'store' => $tier->getStoreId()]
                    );

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->backendSession->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => (int)$this->getRequest()->getParam('id')]);

                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find Tier to save'));
        $this->_redirect('*/*/');
    }
}
