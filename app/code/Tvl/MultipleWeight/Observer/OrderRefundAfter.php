<?php
/**
 * Created by tvl.
 * Date: 6/7/2020
 * Time: 18:50
 */

namespace Tvl\MultipleWeight\Observer;


use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class OrderRefundAfter implements ObserverInterface
{
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SerializerInterface $serializer
    ) {
        $this->productRepo = $productRepository;
        $this->serializer = $serializer;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $order */
        $creditmemo = $observer->getCreditmemo();
        /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
        foreach ($creditmemo->getAllItems() as $item) {
            $product = $this->productRepo->getById($item->getProductId());
            if (!$product->getRequiredWeight()) {
                return;
            }
            $orderItem = $item->getOrderItem();
            if ($optionValue = $orderItem->getProductOptionByCode('info_buyRequest')) {
                if (!isset($optionValue['weight']) && !isset($optionValue['item_qty'])) {
                    return;
                }

                $itemWeightData = explode('|', $optionValue['item_qty']);
                $weightdatas = [];
                foreach ($itemWeightData as $data) {
                    $data = explode(':', $data);
                    $weightdatas['qty' .$data[0]] = $data[1];
                }
                $weights = $product->getWeights();
                if (!$weights) continue;
                if (is_string($weights)) {
                    $weights = $this->serializer->unserialize($weights);
                }
                foreach ($weights as &$weight) {
                    if (!array_key_exists('qty' .$weight['weight'], $weightdatas)) continue;
                    $weight['qty'] += $weightdatas['qty' . $weight['weight']] * $item->getQty();
                }

                $product->setData('weights', $weights)->save();
            }
        }
    }
}