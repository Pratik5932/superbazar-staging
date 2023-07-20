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



namespace Mirasvit\RewardsCustomerAccount\Controller\Account;

use Magento\Framework\Exception\NotFoundException;

class Referral extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $url = $this->_url->getUrl(\Magento\Customer\Model\Url::ROUTE_ACCOUNT_LOGIN);

        if (!$this->config->getReferralIsActive()) {
            throw new NotFoundException(__('Page not found.'));
        } elseif (!$this->customerSession->authenticate($url)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }
}
