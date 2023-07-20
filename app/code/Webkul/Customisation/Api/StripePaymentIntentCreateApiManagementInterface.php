<?php
namespace Webkul\Customisation\Api;

interface StripePaymentIntentCreateApiManagementInterface
{
    /**
     * get test Api data.
    *
    * @api
    *
    * @param int $amount
    * @param string $currency
    * @param string $sellerStripeAccountId 
    * @return \Webkul\Customisation\Api\StripePaymentIntentCreateApiManagementInterface
    */
    public function getApiData($amount, $currency, $sellerStripeAccountId);
}