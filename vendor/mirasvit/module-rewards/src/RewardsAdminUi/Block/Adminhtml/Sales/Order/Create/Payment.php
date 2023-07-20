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



namespace Mirasvit\RewardsAdminUi\Block\Adminhtml\Sales\Order\Create;

use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

class Payment extends \Magento\Framework\View\Element\Template
{
    private $rewardsBalance;
    private $rewardsHelper;
    private $rewardsPurchase;
    private $salesOrderCreate;
    private $sessionQuote;
    private $context;
    private $purchase;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        \Mirasvit\Rewards\Helper\Data $rewardsHelper,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Sales\Model\AdminOrder\Create $salesOrderCreate,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order $orderModel,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->rewardsBalance   = $rewardsBalance;
        $this->rewardsHelper    = $rewardsHelper;
        $this->rewardsPurchase  = $rewardsPurchase;
        $this->salesOrderCreate = $salesOrderCreate;
        $this->sessionQuote     = $sessionQuote;
        $this->context          = $context;

        /** start compatibility with Aheadgroups_Ordereditor */
        if ($checkoutSession->getCurrentOrderId() > 0) {
            $order = $orderModel->load((int)$checkoutSession->getCurrentOrderId());
            $this->purchase = $this->rewardsPurchase->getByOrder($order);
        }
        /** end compatibility with Aheadgroups_Ordereditor */
        if (!$this->purchase || !$this->purchase->getId()) {
            $this->purchase = $this->rewardsPurchase->getByQuote($this->getOrderQuote());
        }

        if ($config->getAdvancedSpendingCalculationMethod() == Method::METHOD_ITEMS &&
            $this->purchase && $this->purchase->getSpendAmount() == 0
        ) {
            $this->purchase->refreshPointsNumber(true);
        }

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getOrderQuote()
    {
        return $this->salesOrderCreate->getQuote();
    }

    /**
     * @return string
     */
    public function getApplyUrl()
    {
        return '';
    }

    /**
     * @return bool
     */
    public function canUseRewardsPoints()
    {
        if (!$this->getOrderQuote()->getCustomerId()) {
            return false;
        }

        return (bool)$this->getMaxPointsToSpent();
    }

    /**
     * @return int
     */
    public function getMaxPointsToSpent()
    {
        return $this->purchase->getSpendMaxPoints();
    }

    /**
     * @return int
     */
    public function getPointsAmount()
    {
        return $this->purchase->getSpendPoints();
    }

    /**
     * @return int
     */
    public function getBalancePoints()
    {
        return $this->rewardsBalance->getBalancePoints($this->getOrderQuote()->getCustomerId());
    }

    /**
     * @param float $points
     * @return string
     */
    public function formatPoints($points)
    {
        return $this->rewardsHelper->formatPoints($points);
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        $customer = $this->getOrderQuote()->getCustomer();

        return $customer->getFirstname() . ' ' . $customer->getLastname();
    }
}
