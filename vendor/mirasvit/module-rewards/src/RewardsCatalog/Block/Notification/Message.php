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



namespace Mirasvit\RewardsCatalog\Block\Notification;

use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * Class Message
 *
 * Displays Rewards Notifications Rules messages
 *
 * @package Mirasvit\RewardsCatalog\Block\Notification
 */
class Message extends \Magento\Framework\View\Element\Messages
{
    /**
     * @var \Mirasvit\Rewards\Helper\Message
     */
    private $messageHelper;
    /**
     * @var \Mirasvit\RewardsCatalog\Helper\Earn
     */
    private $earnHelper;
    /**
     * @var \Mirasvit\Rewards\Helper\Rule\Notification
     */
    private $rewardsPurchase;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        \Mirasvit\Rewards\Helper\Message $messageHelper,
        \Mirasvit\RewardsCatalog\Helper\Earn $earnHelper,
        \Mirasvit\Rewards\Helper\Rule\Notification $rewardsPurchase,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        InterpretationStrategyInterface $interpretationStrategy,
        array $data = []
    ) {
        $this->messageHelper      = $messageHelper;
        $this->earnHelper = $earnHelper;
        $this->rewardsPurchase    = $rewardsPurchase;
        $this->context            = $context;
        $this->messageManager     = $messageManager;
        $this->customerSession     = $customerSession;

        parent::__construct(
            $context,
            $messageFactory,
            $collectionFactory,
            $messageManager,
            $interpretationStrategy,
            $data
        );
    }

    /**
     * @return int
     */
    public function getProductPoints()
    {
        $customer = $this->customerSession->getCustomer();
        $websiteId = $this->context->getStoreManager()->getWebsite()->getId();

        return $this->earnHelper->getProductPoints($this->getProduct(), $customer, $websiteId);
    }

    /**
     * @var bool|array
     */
    protected $rules = false;

    /**
     * @return array|bool
     */
    public function getRules()
    {
        if (!$this->rules) {
            $this->rules = $this->rewardsPurchase->calcNotificationRules();
        }

        return $this->rules;
    }

    /**
     * @param \Mirasvit\Rewards\Model\Notification\Rule $rule
     *
     * @return string
     */
    public function getMessage($rule)
    {
        if ($rule->getMessage()) {
            return $this->messageHelper->processNotificationVariables($rule->getMessage());
        } else {
            return '';
        }
    }
}
