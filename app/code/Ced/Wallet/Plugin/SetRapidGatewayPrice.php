<?php

namespace Ced\Wallet\Plugin;

use Magento\Framework\App\Response\Http;
use Magento\Framework\UrlInterface;

class SetRapidGatewayPrice
{

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function beforeBuild(
        $subject,
        array $buildSubject
    ) {
  

        $needToPay = $this->checkoutSession->getWalletLeftAmount();
        if($needToPay){
            $buildSubject['amount']  = $needToPay;
        }
        
        return [$buildSubject];
    }
}
