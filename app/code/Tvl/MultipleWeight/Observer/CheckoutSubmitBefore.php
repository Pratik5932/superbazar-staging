<?php
/**
 * Created by tvl.
 * Date: 6/16/2020
 * Time: 17:03
 */

namespace Tvl\MultipleWeight\Observer;


use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

class CheckoutSubmitBefore implements ObserverInterface
{
    /**
     * CheckoutSubmitBefore constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SerializerInterface $serializer
    ) {
        $this->productRepo = $productRepository;
        $this->serializer = $serializer;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $this->productRepo->getById($item->getProductId());
            if (!$product->getRequiredWeight()) {
                continue;
            }
            if ($optionValue = $item->getOptionByCode('additional_options')) {
                $value = $this->serializer->unserialize($optionValue->getValue());
                if (!isset($value['weights_data'])) {
                    continue;
                }

                $itemWeightData = explode('|', $value['weights_data']['value']);
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
                    if ($weight['qty'] < $this->getQtyByWeight($quote, $product, $weight['weight'])) {
                        throw new LocalizedException(__('The requested qty is not available'));
                    }
                }
            }
        }
    }

    /**
     * @param $cart
     * @param $info
     * @param $product
     * @param $weightCompare
     * @return float|int
     */
    protected function getQtyByWeight($cart, $product, $weightCompare)
    {
        $qty = 0;
        foreach ($cart->getAllVisibleItems() as $item) {
            if ($item->getProductId() != $product->getId()) continue;
            if ($optionValue = $item->getOptionByCode('additional_options')) {
                $value = $this->serializer->unserialize($optionValue->getValue());
                if (!isset($value['multiple_weight'])) {
                    continue;
                }

                $itemWeightData = explode('|', $value['weights_data']['value']);
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
                    if ($weight['weight'] != $weightCompare) continue;
                    if (!array_key_exists('qty' .$weight['weight'], $weightdatas)) continue;
                    $qty += $weightdatas['qty' .$weight['weight']] * $item->getQty();
                }
            }
        }

        return $qty;
    }
}