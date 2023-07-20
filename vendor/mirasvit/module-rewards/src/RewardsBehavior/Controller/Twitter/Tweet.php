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



namespace Mirasvit\RewardsBehavior\Controller\Twitter;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

class Tweet extends Action
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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Mirasvit\Rewards\Model\Config
     */
    protected $config;

    /**
     * @param \Mirasvit\Rewards\Model\Config                   $config
     * @param \Mirasvit\Rewards\Helper\BehaviorRule                $rewardsBehavior
     * @param \Mirasvit\Rewards\Helper\Data                    $rewardsData
     * @param \Magento\Customer\Model\CustomerFactory          $customerFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Framework\App\Action\Context            $context
     */
    public function __construct(
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Helper\BehaviorRule $rewardsBehavior,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->config = $config;
        $this->rewardsBehavior = $rewardsBehavior;
        $this->rewardsData = $rewardsData;
        $this->customerFactory = $customerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->context = $context;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }
    
    /**
     * @return $this|string
     */
    public function execute()
    {
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $response = '';
        if (!$this->_getCustomer()) {
            return $response;
        }
        $url = $this->getRequest()->getParam('url');
        $transaction = $this->rewardsBehavior->processRule(
            \Mirasvit\Rewards\Model\Config::BEHAVIOR_TRIGGER_TWITTER_TWEET,
            $this->_getCustomer(),
            $this->storeManager->getWebsite()->getId(),
            $url
        );

        if ($transaction) {
            $resultJson = $this->resultJsonFactory->create();

            $response = $resultJson->setJsonData(
                __("You've earned %1 for Tweet!", $this->rewardsData->formatPoints($transaction->getAmount()))
            );
        }

        return $response;
    }

    /**
     * @return \Mirasvit\Rewards\Model\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function _getCustomer()
    {
        return $this->customerFactory->create()->load($this->customerSession->getCustomerId());
    }
}
