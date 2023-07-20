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



namespace Mirasvit\Rewards\Service\Order;

class Transaction
{
    private $appEmulation;
    private $transactionCollectionFactory;

    public function __construct(
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Mirasvit\Rewards\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
    ) {
        $this->appEmulation = $appEmulation;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
    }

    /**
     * Translate transaction comment according to the store. Receive the array of arguments like this
     * [
     *     store_id,
     *     phrase_to_translate,
     *     translation_params1,
     *     ...,
     *     translation_paramsN,
     * ]
     *
     * @return string
     */
    public function translateComment()
    {
        $args = func_get_args();

        $storeId = $args[0];
        unset($args[0]);

        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);

        $message = call_user_func_array('__', $args)->render();

        $this->appEmulation->stopEnvironmentEmulation();

        return $message;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \Mirasvit\Rewards\Model\Transaction|null
     */
    public function getEarnedPointsTransaction($order)
    {
        $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('code', "order_earn-{$order->getId()}")
        ;
        if ($collection->count()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    /**
     * @param string $code
     * @return \Mirasvit\Rewards\Model\ResourceModel\Transaction\Collection|null
     */
    public function getEarnedBehaviorTransaction($code)
    {
        $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('code', array('like' => $code . '%'))
        ;

        if ($collection->count()) {
            return $collection;
        }

        return null;
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditMemo
     * @return int
     */
    public function getEarnedRefundedPointsTransaction($creditMemo)
    {
        $order = $creditMemo->getOrder();
        $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('code', "order_refund-{$order->getId()}-{$creditMemo->getId()}")
        ;

        return $collection->count();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \Mirasvit\Rewards\Model\Transaction|null
     */
    public function getSpendPointsTransaction($order)
    {
        $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('code', "order_spend-{$order->getId()}")
        ;
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
        return null;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return int
     */
    public function getSumSpendPointsRestored($order)
    {
        $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('code', ['like' => "order_spend_restore-{$order->getId()}-%"])
        ;
        $points = 0;
        if ($collection->count()) {
            foreach ($collection as $transaction) {
                $points += $transaction->getAmount();
            }
        }

        return $points;
    }
}
