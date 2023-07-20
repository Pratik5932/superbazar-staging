<?php
/**
 * Created by tvl.
 * Date: 6/11/2020
 * Time: 10:46
 */

namespace Tvl\MultipleWeight\Observer;


use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

class UpdateCartItemAfter implements ObserverInterface
{
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Session $session,
        SerializerInterface $serializer
    ) {
        $this->checkoutSession = $session;
        $this->productRepo = $productRepository;
        $this->serializer = $serializer;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */                  
        $item = $observer->getQuoteItem();
        $product = $this->productRepo->getById($item->getProductId());      
        if (!$product->getRequiredWeight()) {
            return;
        }
        if ($optionValue = $item->getOptionByCode('additional_options')) {
            $value = $this->serializer->unserialize($optionValue->getValue());
            if (!isset($value['weights_data'])) {
                return;
            }

            $itemWeightData = explode('|', $value['weights_data']['value']);
            $weightdatas = [];

            foreach ($itemWeightData as $data) {
                $data = explode(':', $data);
                $weightdatas['qty' .$data[0]] = $data[1];
            }
            $weights = $product->getWeights();    
            if (!$weights) return;
            if (is_string($weights)) {
                $weights = $this->serializer->unserialize($weights);
            }
            foreach ($weights as &$weight) {
                if (!array_key_exists('qty' .$weight['weight'], $weightdatas)) continue;
                $qty = $this->getQtyByWeight($product, $weight['weight']);         
                if ($weight['qty'] < $qty) {
                    throw new LocalizedException(__('The requested qty is not available'));
                }
            }
        }
    }

    protected function getQtyByWeight($product, $weightCompare)
    {
        $qty = 0;
        foreach ($this->checkoutSession->getQuote()->getAllItems() as $item) {
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