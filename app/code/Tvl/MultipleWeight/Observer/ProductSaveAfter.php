<?php
/**
* Created by tvl.
* Date: 6/7/2020
* Time: 21:49
*/

namespace Tvl\MultipleWeight\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class ProductSaveAfter implements ObserverInterface
{
    protected $stockRegistry;

    public function __construct(SerializerInterface $serializer,\Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->serializer = $serializer;
        $this->stockRegistry = $stockRegistry;


    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEntity();
        if (!$product->getRequiredWeight()) {
            return;
        }
        $weights = $product->getWeights();
        $weights = is_string($weights) ? $this->serializer->unserialize($weights) : $weights;
        $qty = 0;
        if(is_array($weights)){
            foreach ($weights as $item) {
                $qty += $item['qty'];
            }   
        }

        $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
        // $stockItem = $product->getExtensionAttributes()->getStockItem();
        $status = $qty > 0 ? 1 : 0;
        $stockItem->setQty($qty)->setIsInStock($status)->save();
    }
}