<?php
namespace Webkul\Customisation\Api;

interface PaypalSellerInfoApiManagementInterface
{
    /**
     * get Api data by id.
    *
    * @api
    *
    * @param int $id
    * @return \Webkul\Customisation\Api\PaypalSellerInfoApiManagementInterface
    */
    public function getApiData($id);
}