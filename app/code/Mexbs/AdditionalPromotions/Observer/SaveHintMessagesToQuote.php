<?php
namespace Mexbs\AdditionalPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveHintMessagesToQuote implements ObserverInterface{
    protected $serializer;

    public function __construct(
        \Magento\Framework\Serialize\Serializer\Serialize $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        $hintMessages = $quote->getHintMessages();

        $hintMessagesSerialized = $hintMessages;
        if(!is_string($hintMessagesSerialized)){
            $hintMessagesSerialized = $this->serializer->serialize($hintMessages);
        }

        $quote->setHintMessages($hintMessagesSerialized);
    }
}