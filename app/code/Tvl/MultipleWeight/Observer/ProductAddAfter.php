<?php
/**
 * Created by tvl.
 * Date: 6/4/2020
 * Time: 20:59
 */

namespace Tvl\MultipleWeight\Observer;


use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

class ProductAddAfter implements ObserverInterface
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
        /** @var \Magento\Quote\Model\Quote\Item $item */     
        $item = $observer->getEvent()->getData('quote_item');
        $item = ( $item->getParentItem() ? $item->getParentItem() : $item );

        $product = $observer->getProduct();
                 
        if ($optionValue = $item->getOptionByCode('additional_options')) {
            $value = $this->serializer->unserialize($optionValue->getValue());    
            if (!isset($value['multiple_weight'])) {
                return;
            }
            $weight = str_replace('gm', '', $value['multiple_weight']['value']);
            $price = $this->getPriceByWeight($product, $weight);
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);

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

    /**
     * @param $product
     * @param $weight
     * @return float|int
     */
    protected function getPriceByWeight($product, $weight)
    {
        if ($weight < 1000) {
            $priceUnit = $product->getFinalPrice();
        } else if ($weight < 5000){
            $priceUnit = $product->getPriceOneFive() ? : $product->getFinalPrice();
        } else {
            $priceUnit = $product->getPriceFive() ? : $product->getFinalPrice();
        }

        return $priceUnit * $weight / 1000;
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