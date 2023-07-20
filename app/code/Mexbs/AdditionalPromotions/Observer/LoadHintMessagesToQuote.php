<?php
namespace Mexbs\AdditionalPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class LoadHintMessagesToQuote implements ObserverInterface{
    protected $serializer;

    public function __construct(
        \Magento\Framework\Serialize\Serializer\Serialize $serializer
    ) {
        $this->serializer = $serializer;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        $hintMessagesSerialized = $quote->getHintMessages();

        $hintMessages = [];
        if(is_string($hintMessagesSerialized)){
            $hintMessages = $this->serializer->unserialize($hintMessagesSerialized);
        }elseif(is_array($hintMessagesSerialized)){
            $hintMessages = $hintMessagesSerialized;
        }

        $quote->setHintMessages($hintMessages);
    }
}