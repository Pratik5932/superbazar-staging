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



namespace Mirasvit\Rewards\Observer\Order;

use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Rewards\Model\Config;

class OrderAfterShipmentSave implements ObserverInterface
{
    private $config;

    private $orderFactory;

    private $appState;

    private $earnBehaviorPoints;

    private $earnCartPoints;

    private $earnReferralPoints;

    public function __construct(
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Service\Order\Transaction\Earn\BehaviorPoints $earnBehaviorPoints,
        \Mirasvit\Rewards\Service\Order\Transaction\Earn\CartPoints $earnCartPoints,
        \Mirasvit\Rewards\Service\Order\Transaction\Earn\ReferralPoints $earnReferralPoints,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\State $appState
    ) {
        $this->config             = $config;
        $this->orderFactory       = $orderFactory;
        $this->appState           = $appState;
        $this->earnBehaviorPoints = $earnBehaviorPoints;
        $this->earnCartPoints     = $earnCartPoints;
        $this->earnReferralPoints = $earnReferralPoints;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $object = $observer->getObject();
        if (!($object && ($object instanceof \Magento\Sales\Model\Order\Shipment))) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return;
        }

        /**
         * To prevent error during installation(Helpers are using session).
         */
        try {
            $this->appState->getAreaCode();
        }
        catch (\Exception $e) {
            return;
        }

        $order = $this->orderFactory->create()->load((int) $object->getOrderId());

        if ($order && $this->config->getGeneralIsEarnAfterShipment()) {
            $this->earnCartPoints->add($order);
            $this->earnBehaviorPoints->add($order);
            $this->earnReferralPoints->add($order);
        }
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
