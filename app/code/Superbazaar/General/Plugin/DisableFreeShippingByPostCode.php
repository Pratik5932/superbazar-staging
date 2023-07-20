<?php
/**
* Copyright © 2020 MageWorx. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Superbazaar\General\Plugin;

/**
* Class DisableFreeShippingByPostCode
*/
class DisableFreeShippingByPostCode
{
    /**
    * @param \Magento\OfflineShipping\Model\Carrier\Freeshipping $subject
    * @param callable $proceed
    * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
    * @return \Magento\Shipping\Model\Rate\Result|bool
    */
    public function aroundCollectRates(
        \Magento\OfflineShipping\Model\Carrier\Freeshipping $subject,
        callable $proceed,
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
     /*   if ($request->getDestPostcode() == "3149") { // Check is postcode exists in request
            //  if ($this->postCodeContainsNullOnSecondPosition($request->getDestPostcode())) { // Check is second symbol == 0
            return false; // Disable method
            //  }
        }*/

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