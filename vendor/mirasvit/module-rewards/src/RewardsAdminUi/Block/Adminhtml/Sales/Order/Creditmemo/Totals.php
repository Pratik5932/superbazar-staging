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

class Totals extends \Magento\Sales\Block\Adminhtml\Order\Totals
{
    /**
     * @var \Mirasvit\Rewards\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Mirasvit\Rewards\Helper\Data                    $rewardsData
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Sales\Helper\Admin                      $adminHelper
     * @param array                                            $data
     */
    public function __construct(
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->rewardsData = $rewardsData;

        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Initialize totals object.
     *
     * @return $this
     * @throws \Magento\Framework\Currency_Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Translate_Exception
     */
    protected function initTotals()
    {
        parent::_initTotals();
        /** @var mixed $block */
        $block = $this->getParentBlock();
        $creditmemo = $block->getCreditmemo();
        if ($creditmemo && ($creditmemo->getRewardsRefundedPoints() > 0 || $creditmemo->getRewardsBaseRefunded())) {
            $block->addTotal(new \Magento\Framework\DataObject([
                'code'       => 'rewards_refunded_amount',
                'value'      => $creditmemo->getRewardsBaseRefunded(),
                'base_value' => $creditmemo->getRewardsRefunded(),
                'label'      => __(
                    '%1 %2 Refunded', $creditmemo->getRewardsRefundedPoints(), $this->rewardsData->getPointsName()),
                'area'       => 'footer',
                'strong'     => $this->getStrong(),
            ]), 'refunded');
        }
        if ($creditmemo->getRewardsDiscountAmount() > 0) {
            $block->addTotal(new \Magento\Framework\DataObject([
                'code'       => 'rewards_discount',
                'value'      => $creditmemo->getRewardsDiscountAmount() * -1,
                'base_value' => $creditmemo->getBaseRewardsDiscountAmount() * -1,
                'label'      => __(
                    '%1 %2 Spent', $creditmemo->getRewardsSpendPoints(), $this->rewardsData->getPointsName()),
            ]), 'rewards_discount');
        }
        if ($creditmemo->getRewardsEarnPoints() > 0) {
            $block->addTotal(new \Magento\Framework\DataObject([
                'code'        => 'rewards_earned',
                'is_formated' => true,
                'value'       => $creditmemo->getRewardsEarnPoints(),
                'label'       => __(
                    '%1 Earned', $this->rewardsData->getPointsName()),
            ]), 'earned');
        }

        return $this;
    }

    /**
     * @return float
     */
    public function getRewardsPoints()
    {
        $total = $this->getParentBlock()->getTotal('rewards_refunded_amount');

        return $total->getValue();
    }

    /**
     * @return string
     */
    public function getRewardsLabel()
    {
        $total = $this->getParentBlock()->getTotal('rewards_refunded_amount');

        return $total->getLabel();
    }
}
