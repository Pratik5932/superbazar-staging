<?php
/**
* Copyright © 2020 MageWorx. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Superbazaar\General\Plugin;

/**
* Class DisableFreeShippingByPostCode
*/
class DisableFlatShippingByPostCode
{
    /**
    * @param \Magento\OfflineShipping\Model\Carrier\Freeshipping $subject
    * @param callable $proceed
    * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
    * @return \Magento\Shipping\Model\Rate\Result|bool
    */
    public function aroundCollectRates(
        \Magento\OfflineShipping\Model\Carrier\Flatrate $subject,
        callable $proceed,
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
        #  echo $request->getDestPostcode();exit;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig =$objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $postcodesValues =  $scopeConfig->getValue(
            'carriers/matrixrate/disableflatshipping',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );


        //$postcodes = array("3168","3134");
        $postcodes = explode(",",$postcodesValues);
		 #$objectManager->get('Psr\Log\LoggerInterface')->info(print_r($postcodesValues,true));

		#mail("er.bharatmali@gmail.com","sadsad",$request->getDestPostcode());
		//print_r($postcodes);exit;
        if (in_array($request->getDestPostcode(),$postcodes)) { // Check is postcode exists in request
            // if ($this->postCodeContainsNullOnSecondPosition($request->getDestPostcode())) { // Check is second symbol == 0
            return false; // Disable method
        }
        // }

        return $proceed($request);
    }

    /**
    * Test postcode
    *
    * @param string $postCode
    * @return bool
    */
    private function postCodeContainsNullOnSecondPosition(string $postCode): bool
    {
    return stripos($postCode, '0') === 1;
}
}