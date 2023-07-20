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



namespace Mirasvit\RewardsAdminUi\Block\Adminhtml\Sales\Order\Creditmemo;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Api\Repository\RefundRepositoryInterface;

class Refund extends Template
{
    protected $registry;
    protected $context;
    protected $refundRepository;
    protected $config;

    public function __construct(
        Config $config,
        RefundRepositoryInterface $refundRepository,
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->config           = $config;
        $this->registry         = $registry;
        $this->context          = $context;
        $this->refundRepository = $refundRepository;

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    private function getCreditmemo()
    {
        return $this->registry->registry('current_creditmemo');
    }

    /**
     * @return bool
     */
    public function canRefundToRewards()
    {
        $creditmemo = $this->getCreditmemo();

        return !$creditmemo->getOrder()->getCustomerIsGuest() && (
            $creditmemo->getGrandTotal() > 0 || $creditmemo->getRewardsBaseRefunded() > 0
        );
    }

    /**
     * @return int
     */
    public function getReturnValue()
    {
        $max = round($this->registry->registry('current_creditmemo')->getBaseCreditReturnMax(), 2);

        if ($max) {
            return $max;
        }

        return 0;
    }

    /**
     * @return float
     */
    public function getAppliedPointsValue()
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $this->registry->registry('current_creditmemo');
        if ($creditmemo->getRewardsRefundedPoints()) {
            return $creditmemo->getRewardsRefundedPoints();
        }

        return 0;
    }

    /**
     * @return float
     */
    public function getAppliedValue()
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $this->registry->registry('current_creditmemo');
        if ($creditmemo->getRewardsBaseRefunded()) {
            return $creditmemo->getRewardsBaseRefunded();
        }

        return 0;
    }

    public function getGeneralPointsName()
    {
        return $this->config->getGeneralPointUnitName();
    }

    /**
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        $code = $this->getCreditmemo()->getBaseCurrencyCode();

        return $code ?: '';
    }


    /**
     * Get update url
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl(
            'sales/*/updateQty',
            [
                'order_id'   => $this->registry->registry('current_creditmemo')->getOrderId(),
                'invoice_id' => $this->getRequest()->getParam('invoice_id', null)
            ]
        );
    }
}
