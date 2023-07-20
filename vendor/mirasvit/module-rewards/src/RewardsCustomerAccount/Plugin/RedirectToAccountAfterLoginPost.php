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



namespace Mirasvit\RewardsCustomerAccount\Plugin;

use Magento\Customer\Controller\Account\LoginPost;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\Forward as ResultForward;

class RedirectToAccountAfterLoginPost
{
    /**
     * @var \Mirasvit\Rewards\Model\Config
     */
    private $config;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->config       = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * @param LoginPost $subject
     * @param ResultRedirect|ResultForward $result
     *
     * @return ResultRedirect|ResultForward
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(LoginPost $subject, $result)
    {
        if ($this->config->getGeneralIsRedirectAfterLogin($this->storeManager->getStore())
            && !$subject->getRequest()->getParam('product')) {
            $result->setPath('rewards/account');
        }

        return $result;
    }
}
