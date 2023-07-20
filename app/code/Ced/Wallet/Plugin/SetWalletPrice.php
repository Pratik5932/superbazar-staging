<?php

namespace Ced\Wallet\Plugin;

use Magento\Framework\App\Response\Http;
use Magento\Framework\UrlInterface;
class SetWalletPrice
{

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function beforeCall(
        $subject,
        $methodName,
        array $request
    ) {
  
        $needToPay = $this->checkoutSession->getWalletLeftAmount();
        if($needToPay){
            $request['AMT']  = $needToPay;
            $request['ITEMAMT'] = $needToPay;
            $request['SHIPPINGAMT'] = 0;
            $request['TAXAMT'] = 0;
        }
      
        return [$methodName, $request];
    }
}
