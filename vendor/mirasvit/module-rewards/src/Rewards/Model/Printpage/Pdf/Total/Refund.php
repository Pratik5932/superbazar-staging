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
use Mirasvit\Rewards\Api\Service\RefundServiceInterface;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;

class Refund extends DefaultTotal
{
    /**
     * @var \Mirasvit\Rewards\Api\Service\RefundServiceInterface
     */
    private $refundService;

    public function __construct(
        RefundServiceInterface $refundService,
        Data $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        array $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);

        $this->refundService = $refundService;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        $refundAmount = 0;

        if (!$this->getSource() instanceof \Magento\Sales\Model\Order) {
            $refundAmount = $this->refundService->getByCreditMemoId($this->getSource()->getId())->getBaseRefunded();
        }

        return $refundAmount;
    }
}
