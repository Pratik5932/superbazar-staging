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



namespace Mirasvit\RewardsBehavior\Controller\Facebook;

use Magento\Framework\Controller\ResultFactory;

class Unlike extends \Mirasvit\RewardsBehavior\Controller\Facebook
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $url = $this->getRequest()->getParam('url');
        $this->rewardsSocialBalance->cancelEarnedPoints(
            $this->_getCustomer(),
            \Mirasvit\Rewards\Model\Config::BEHAVIOR_TRIGGER_FACEBOOK_LIKE.'-'.$url
        );

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setJsonData(__("Facebook Like Points has been canceled"));
    }
}
