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



namespace Mirasvit\RewardsBehavior\Controller\Pinterest;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;


class Pin extends Action
{
    /**
     * @var \Mirasvit\Rewards\Helper\BehaviorRule
     */
    protected $rewardsBehavior;

    /**
     * @var \Mirasvit\Rewards\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Mirasvit\Rewards\Helper\BehaviorRule                $rewardsBehavior
     * @param \Mirasvit\Rewards\Helper\Data                    $rewardsData
     * @param \Magento\Customer\Model\CustomerFactory          $customerFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Framework\App\Action\Context            $context
     */
    public function __construct(
        \Mirasvit\Rewards\Helper\BehaviorRule $rewardsBehavior,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->rewardsBehavior = $rewardsBehavior;
        $this->rewardsData = $rewardsData;
        $this->customerFactory = $customerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->context = $context;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function _getCustomer()
    {
        return $this->customerFactory->create()->load($this->customerSession->getCustomerId());
    }
    
    /**
     * @return $this|string
     */
    public function execute()
    {
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $response = '';
        $url = $this->getRequest()->getParam('url');
        $transaction = $this->rewardsBehavior->processRule(
            \Mirasvit\Rewards\Model\Config::BEHAVIOR_TRIGGER_PINTEREST_PIN,
            $this->_getCustomer(),
            false,
            $url
        );
        if ($transaction) {
            $resultJson = $this->resultJsonFactory->create();

            $response = $resultJson->setJsonData(
                __("You've earned %1 for Pin!", $this->rewardsData->formatPoints($transaction->getAmount()))
            );
        }

        return $response;
    }
}
