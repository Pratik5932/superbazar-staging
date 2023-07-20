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



namespace Mirasvit\RewardsCustomerAccount\Controller;

use Magento\Framework\Exception\NotFoundException;
use Mirasvit\RewardsCustomerAccount\Helper\Account\Rule;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Model\TransactionFactory;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Rewards\Service\MenuLink;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

abstract class Account extends Action
{
    protected $menuLink;

    protected $transactionFactory;

    protected $registry;

    protected $customerSession;

    protected $context;

    protected $resultFactory;

    protected $accountRuleHelper;

    protected $config;

    public function __construct(
        MenuLink $menuLink,
        Rule $accountRuleHelper,
        Config $config,
        TransactionFactory $transactionFactory,
        Registry $registry,
        Session $customerSession,
        Context $context
    ) {
        $this->menuLink           = $menuLink;
        $this->accountRuleHelper  = $accountRuleHelper;
        $this->config             = $config;
        $this->transactionFactory = $transactionFactory;
        $this->registry           = $registry;
        $this->customerSession    = $customerSession;
        $this->context            = $context;
        $this->resultFactory      = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * @return bool
     */
    protected function isDisplayedMenu()
    {
        return $this->config->getDisplayOptionsIsShowPointsMenu();
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $action = $this->getRequest()->getActionName();
        if ($action != 'referral' && $action != 'unsubscribe') {
            $url = $this->_url->getUrl(\Magento\Customer\Model\Url::ROUTE_ACCOUNT_LOGIN);

            if ((!$this->customerSession->authenticate($url) && $action != 'history')
                || ($this->customerSession->authenticate($url)
                    && !$this->menuLink->isShowMenuForCurrentCustomer($this->_getSession()->getCustomer()))) {
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                throw new NotFoundException(__('Page not found.'));
            }
        }

        return parent::dispatch($request);
    }

    /**
     * @return \Mirasvit\Rewards\Model\Transaction
     */
    protected function _initTransaction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $transaction = $this->transactionFactory->create()->load($id);
            if ($transaction->getId() > 0) {
                $this->registry->register('current_transaction', $transaction);

                return $transaction;
            }
        }
    }

    /************************/
}
