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



namespace Mirasvit\Rewards\Repository;

use Magento\Framework\Exception\NoSuchEntityException;
use Mirasvit\Rewards\Model\Purchase;

class PurchaseRepository implements \Mirasvit\Rewards\Api\Repository\PurchaseRepositoryInterface
{
    /**
     * @var \Mirasvit\Rewards\Helper\Purchase
     */
    private $purchaseHelper;
    /**
     * @var \Mirasvit\Rewards\Model\PurchaseFactory
     */
    private $purchaseFactory;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;

    /**
     * @var Purchase[]
     */
    protected $instances = [];

    public function __construct(
        \Mirasvit\Rewards\Helper\Purchase $purchaseHelper,
        \Mirasvit\Rewards\Model\PurchaseFactory $purchaseFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository
    ) {
        $this->purchaseHelper = $purchaseHelper;
        $this->purchaseFactory = $purchaseFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function get($orderId)
    {
        if (!isset($this->instances[$orderId])) {
            $order = $this->orderRepository->get($orderId);

            /** @var Purchase $purchase */
            $purchase = $this->purchaseHelper->getByOrder($order);
            if (!$purchase->getId()) {
                throw NoSuchEntityException::singleField('id', $orderId);
            }
            $this->instances[$orderId] = $purchase;
        }

        return $this->instances[$orderId];
    }
}
