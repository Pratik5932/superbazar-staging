<?php
/**
 * Created by tvl.
 * Date: 6/4/2020
 * Time: 19:46
 */

namespace Tvl\MultipleWeight\Observer;


use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

class ProductAddBefore implements ObserverInterface
{
    public function __construct(
        SerializerInterface $serializer,
        Session $session
    ){
        $this->checkoutSession = $session;
        $this->serializer = $serializer;
    }

    public function execute(Observer $observer)
    {
        $info = $observer->getInfo();
        if (!isset($info['weight'])) return;
        $product = $observer->getProduct();
        if (!$product->getRequiredWeight()) {
            return;
        }

        $additionalOptions = [
            'multiple_weight' => [
                'label' => 'Weight',
                'value' => sprintf('%sgm', $info['weight']),
            ],
            'weights_data' => [
                'label' => 'Weight Detail',
                'value' => $info['item_qty']
            ]
        ];

        $itemWeightData = explode('|', $info['item_qty']);
        $weightdatas = [];

        foreach ($itemWeightData as $data) {
            $data = explode(':', $data);
            $weightdatas['qty' .$data[0]] = $data[1];
        }
        $weights = $product->getWeights();

        foreach ($weights as &$weight) {
            if (!array_key_exists('qty' .$weight['weight'], $weightdatas)) continue;
            $moreQty = $this->getQtyByWeight($product, $weight['weight']);
            if ($weight['qty'] < $weightdatas['qty' .$weight['weight']] * $info['qty'] + $moreQty) {
                throw new LocalizedException(__('The requested qty is not available'));
            }
        }
        $product->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
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