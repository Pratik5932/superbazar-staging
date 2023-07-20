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



namespace Mirasvit\RewardsAdminUi\Block\Adminhtml\Sales\Order;

use Mirasvit\Rewards\Api\Service\RefundServiceInterface;
use Mirasvit\Rewards\Service\Order\Transaction\CancelEarnedPoints;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;
use Mirasvit\Rewards\Model\Config;

class Totals extends \Magento\Sales\Block\Adminhtml\Order\Totals
{
    private $config;

    private $cancelEarnedPointsService;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Mirasvit\Rewards\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Mirasvit\Rewards\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;
    protected $context;
    protected $refundService;

    public function __construct(
        Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        RefundServiceInterface $refundService,
        CancelEarnedPoints $cancelEarnedPointsService,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->config          = $config;
        $this->resource        = $resource;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->rewardsData     = $rewardsData;
        $this->refundService   = $refundService;
        $this->localeCurrency  = $localeCurrency;
        $this->context         = $context;

        $this->cancelEarnedPointsService = $cancelEarnedPointsService;

        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Initialize totals object.
     *
     * @return $this
     * @throws \Magento\Framework\Currency_Exception
     */
    protected function initTotals()
    {
        parent::_initTotals();
        $order = $this->getOrder();

        if (!$purchase = $this->rewardsPurchase->getByOrder($order)) {
            return $this;
        }

        $orderId = $order->getId();
        $resource = $this->resource;
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('mst_rewards_transaction');

        $sum = $purchase->getSpendPoints();

        if ($sum) {
            /**
             *   $this->addTotalBefore(new \Magento\Framework\DataObject([
            'code' => 'spend',
            'value' => $sum,
            'label' => __('%1 Spent', $this->rewardsData->getPointsName()),
            'is_formated' => true,
            ], ['discount']));
             */
            $this->addTotalBefore(new \Magento\Framework\DataObject([
                'code' => 'spend',
                'value' => $sum,
                'label' => __('%1 Spent', $this->rewardsData->getPointsName()),
                'is_formated' => true,
            ]));
        }

        $sumActual = (int) $readConnection->fetchOne(
            "SELECT SUM(amount) FROM $table WHERE code='order_earn-{$orderId}'"
        );
        $sum = $purchase->getEarnPoints();
        $pending = '';

        if ($sumActual == 0) {
            $pending = ' (pending)';
        }
        /** @var \Magento\Sales\Block\Adminhtml\Order\Totals $block */
        $block = $this->getParentBlock();
        $proportion = $this->getProportion();

        if ($sum) {
            $sum = round($sum * $proportion, 0);
            $sum = $this->localeCurrency->getCurrency($order->getOrderCurrencyCode())
                ->toCurrency($sum, ['display' => \Magento\Framework\Currency::NO_SYMBOL, 'precision' => 0]);
            $block->addTotal(new \Magento\Framework\DataObject([
                'code'        => 'earn',
                'value'       => $sum,
                'label'       => __('%1 Earned'.$pending, $this->rewardsData->getPointsName()),
                'is_formated' => true,
                'area'        => $this->getDisplayArea(),
                'strong'      => $this->getStrong(),
            ]), 'grand_total');
        }

        if ($this->config->getAdvancedSpendingCalculationMethod() == Method::METHOD_TOTALS && $purchase->getSpendAmount() > 0) {
            $spentPoints = round($purchase->getSpendPoints() * $proportion, 0);
            $spentAmount = round($purchase->getSpendAmount() * $proportion, 2);
            $baseSpentAmount = round($purchase->getBaseSpendAmount() * $proportion, 2);
            $block->addTotal(new \Magento\Framework\DataObject([
                'code'        => 'spent',
                'value'       => -$spentAmount,
                'base_value'  => -$baseSpentAmount,
                'label'       => __(
                    '%1 %2 Spent', $spentPoints, $this->rewardsData->getPointsName()
                ),
                'area'        => $this->getDisplayArea(),
                'strong'      => $this->getStrong(),
            ]), 'grand_total');
        }

        $refundedIfno = $this->refundService->getByOrderId($orderId);

        if ($refundedIfno->getBaseRefundedSum()) {
            $block->addTotal(new \Magento\Framework\DataObject([
                'code'        => 'rewards_refunded_amount',
                'value'       => $refundedIfno->getRefundedSum(),
                'base_value'  => $refundedIfno->getBaseRefundedSum(),
                'label'       => __(
                    '%1 %2 Refunded', $refundedIfno->getRefundedPointsSum(), $this->rewardsData->getPointsName()),
                'area'        => 'footer',
                'strong'      => $this->getStrong(),
            ]), 'refunded');
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * @return float
     */
    public function getRewardsPoints()
    {
        $total = $this->getParentBlock()->getTotal('earn');
        if (!$total) {
            $total = $this->getParentBlock()->getTotal('spent');
        }

        return $total->getValue();
    }

    /**
     * @return string
     */
    public function getRewardsLabel()
    {
        $total = $this->getParentBlock()->getTotal('earn');
        if (!$total) {
            $total = $this->getParentBlock()->getTotal('spent');
        }

        return $total->getLabel();
    }

    /**
     * @return float
     */
    private function getProportion()
    {
        $creditmemo = $this->getCreditmemo();
        if (!$creditmemo) {
            return 1;
        }
        $order = $creditmemo->getOrder();
        if ($order->getSubtotal() > 0) {
            $proportion = $creditmemo->getSubtotal() / $order->getSubtotal();
        } else { // for zero orders with earning points
            $proportion = $this->cancelEarnedPointsService->getCreditmemoItemsQty($creditmemo) /
                $this->cancelEarnedPointsService->getCreditmemoOrderItemsQty($creditmemo);
        }
        if ($proportion > 1) {
            $proportion = 1;
        }

        return $proportion;
    }
}
