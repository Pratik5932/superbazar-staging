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



namespace Mirasvit\RewardsCheckout\Controller\Checkout;

use Magento\Framework\Controller\ResultFactory;

class ApplyPointsApptanaOnestepcheckout extends \Mirasvit\RewardsCheckout\Controller\Checkout
{
    /**
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $response = $this->processRequest();

        $html = $resultPage->getLayout()
            ->createBlock('\Mirasvit\Onestepcheckout\Block\Onestep\Review\Info')
            ->setTemplate('onestepcheckout/onestep/review/info.phtml')
            ->toHtml();

        // Add updated totals HTML to the output

        $response['summary'] = $html;

        $this->getResponse()->setBody($this->serializer->serialize($response));
    }
}
