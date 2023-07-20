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



namespace Mirasvit\RewardsCheckout\Block\Checkout\Cart;

use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * Class Tooltip
 *
 * Displays rewards tooltip on cart page
 *
 * @package Mirasvit\Rewards\Block\Checkout\Cart
 */
class Tooltip extends \Magento\Framework\View\Element\Messages
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $sessionFactory;

    /**
     * @var \Mirasvit\Rewards\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Mirasvit\Rewards\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    public function __construct(
        \Magento\Customer\Model\Session $sessionFactory,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        array $data = []
    ) {
        $this->sessionFactory = $sessionFactory;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->rewardsData = $rewardsData;
        $this->context = $context;
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
     * @return bool
     */
    public function hasGuestNote()
    {
        if ($this->sessionFactory->isLoggedIn() && $this->sessionFactory->getCustomer()->getId()) {
            return false;
        }

        return true;
    }

    /**
     * @return int|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Currency_Exception
     */
    public function getEarnPoints()
    {
        $points = 0;
        $purchase = $this->rewardsPurchase->getPurchase();
        if ($purchase) {
            $quote = $purchase->getQuote();

            if (strtotime((string) $quote->getUpdatedAt()) < (time() - $purchase->getRefreshPointsTime())) {
                $purchase->updatePoints();
                $purchase = $this->rewardsPurchase->getPurchase(); // load updated purchase
            }

            $points = $purchase
//                ->refreshPointsNumber(true)
                ->getEarnPoints();

            if ($points) {
                $points = $this->rewardsData->formatPoints($points);
            }
        }

        return $points;
    }
}
