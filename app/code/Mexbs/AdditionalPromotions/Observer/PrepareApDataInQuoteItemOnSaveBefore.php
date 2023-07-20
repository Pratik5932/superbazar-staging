<?php
namespace Mexbs\AdditionalPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class PrepareApDataInQuoteItemOnSaveBefore implements ObserverInterface{

    protected $serializer;

    public function __construct(
        \Magento\Framework\Serialize\Serializer\Serialize $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getItem();

        $apRuleMatches = $item->getApRuleMatches();

        if(is_array($apRuleMatches)){
            $apRuleMatches = $this->serializer->serialize($apRuleMatches);
            $item->setApRuleMatches($apRuleMatches);
        }

        $apPriceTypeFlags = $item->getApPriceTypeFlags();

        if(is_array($apPriceTypeFlags)){
            $apPriceTypeFlags = $this->serializer->serialize($apPriceTypeFlags);
            $item->setApPriceTypeFlags($apPriceTypeFlags);
        }
    }
}