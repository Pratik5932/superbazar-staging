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



namespace Mirasvit\RewardsCustomerAccount\Block\Account\Sales\Order;

use Mirasvit\Rewards\Helper\Data as RewardsData;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;
use Mirasvit\Rewards\Model\Config;

/**
 * Class Total
 * @package Mirasvit\RewardsCustomerAccount\Block\Account\Sales\Order
 * @deprecated
 */
class Total extends \Magento\Framework\View\Element\Template
{
    private $config;

    protected $rewardsData;

    protected $context;

    public function __construct(
        Config $config,
        RewardsData $rewardsData,
        Context $context,
        array $data = []
    ) {
        $this->config      = $config;
        $this->rewardsData = $rewardsData;
        $this->context = $context;
        parent::__construct($context, $data);
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
     * @return mixed
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
     * @return $this
     */
    public function initTotals()
    {
        if ($this->config->getAdvancedSpendingCalculationMethod() == Method::METHOD_TOTALS
            && (float)$this->getOrder()->getRewardsAmount()
        ) {
            $source = $this->getSource();
            $value = -$source->getRewardsAmount();

            /** @var mixed $parent */
            $parent = $this->getParentBlock();
            $parent->addTotal(new \Magento\Framework\DataObject([
                'code' => 'reward_points',
                'strong' => false,
                'label' => $this->rewardsData->formatPoints($source->getRewardsPointsNumber()),
                'value' => $source instanceof \Magento\Sales\Model\Order\Creditmemo ? -$value : $value,
            ]));
        }

        return $this;
    }
}
