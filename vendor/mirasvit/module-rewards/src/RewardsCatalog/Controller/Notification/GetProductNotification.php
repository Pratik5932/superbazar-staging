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



namespace Mirasvit\RewardsCatalog\Controller\Notification;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\RewardsCatalog\Block\Product\Message;
use \Magento\Framework\Exception\NoSuchEntityException;

class GetProductNotification extends \Mirasvit\RewardsCatalog\Controller\Notification
{
    /**
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        /** @var Message $notificationBlock */
        $notificationBlock = $resultPage->getLayout()->createBlock(
            'Mirasvit\RewardsCatalog\Block\Product\Message',
            'rewards-notification'
        );
        $notificationBlock->setTemplate('Mirasvit_RewardsCatalog::product/message.phtml');

        try {
            $productId = (int)$this->getRequest()->getParam('product_id');
            $product   = $this->productRepository->getById($productId);
            $notificationBlock->setCurrentProduct($product);
            $html = $notificationBlock->toHtml();
        } catch (NoSuchEntityException $e) {
            $html = '';
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store', true);
        $resultJson->setHeader('Pragma', 'no-cache', true);

        $response = $resultJson->setData(
            [
                'text' => $html
            ]
        );

        return $response;
    }
}
