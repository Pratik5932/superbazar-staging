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



namespace Mirasvit\Rewards\Model\Printpage\Pdf\Total;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Mirasvit\Rewards\Helper\Purchase as PurchaseHelper;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory as OrdersFactory;
use Mirasvit\Rewards\Service\Order\Transaction\CancelEarnedPoints;

class Earned extends DefaultTotal
{
    private $purchaseHelper;

    private $cancelEarnedPointsService;

    public function __construct(
        PurchaseHelper     $purchaseHelper,
        TaxHelper          $taxHelper,
        TaxCalculation     $taxCalculation,
        OrdersFactory      $ordersFactory,
        CancelEarnedPoints $cancelEarnedPointsService,
        array              $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);

        $this->purchaseHelper            = $purchaseHelper;
        $this->cancelEarnedPointsService = $cancelEarnedPointsService;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        $invoice           = $this->getSource();
        $order             = $invoice->getOrder();
        $orderEarnedAmount = 0;

        if (!$order instanceof \Magento\Sales\Model\Order) {
            return 0;
        }

        if (!$order->getCustomerIsGuest()) {
            $purchase          = $this->purchaseHelper->getByOrder($order);
            if ($purchase && $purchase->getEarnPoints() > 0) {
                $orderEarnedAmount = $purchase->getEarnPoints();
            }
        }

        return $orderEarnedAmount;
    }

    /**
     * @inheritdoc
     */
    public function getTotalsForDisplay()
    {
        $amount = $this->getAmount();

        $title = __($this->getTitle());
        if ($this->getTitleSourceField()) {
            $label = $title . ' (' . $this->getTitleDescription() . '):';
        } else {
            $label = $title . ':';
        }

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => $amount, 'label' => $label, 'font_size' => $fontSize];
        return [$total];
    }
}
