<?php
namespace Superbazaar\CustomWork\Plugin;

class ListProduct
{

    public function afterGetProductCollection($subject, $result)
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test777.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Your text message'.$result->getFirstItem()->getId());
        return $result;
    }
}