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



namespace Mirasvit\Rewards\Service;

class Order
{
    private $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Lock order to process in events
     * @param \Magento\Sales\Model\Order $order
     */
    public function lock($order)
    {
        $name = $this->getLockName($order);
        if (!$this->registry->registry($name)) {
            $this->registry->register($name, true);
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return mixed
     */
    public function isLocked($order)
    {
        return $this->registry->registry($this->getLockName($order));
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    private function getLockName($order)
    {
        return 'mst_ordercompleted_done_'.$order->getId();
    }
}