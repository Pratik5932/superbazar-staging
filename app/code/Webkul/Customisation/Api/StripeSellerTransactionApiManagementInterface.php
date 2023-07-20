<?php
namespace Webkul\Customisation\Api;

interface StripeSellerTransactionApiManagementInterface
{
    /**
     * get test Api data.
    *
    * @api
    *
    * @param int $id
    * @return \Webkul\Customisation\Api\StripeSellerTransactionApiManagementInterface
    */
    public function getApiData($id);
}