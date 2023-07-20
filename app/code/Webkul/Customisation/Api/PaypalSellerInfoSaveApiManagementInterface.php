<?php
namespace Webkul\Customisation\Api;

interface PaypalSellerInfoSaveApiManagementInterface
{
    /**
     * get test Api data.
    *
    * @api
    *
    * @param string $paypal_id
    * @param string $paypal_fname
    * @param string $paypal_lname
    * @param string $paypal_merchant_id
    * @param string $seller_id
    * @return \Webkul\Customisation\Api\Data\PaypalSellerInfoSaveApiInterface
    */
    public function getApiData($paypal_id, $paypal_fname, $paypal_lname, $paypal_merchant_id, $seller_id);
}