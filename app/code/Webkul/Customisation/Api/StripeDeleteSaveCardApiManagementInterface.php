<?php
namespace Webkul\Customisation\Api;

interface StripeDeleteSaveCardApiManagementInterface
{
    /**
     * get test Api data.
    *
    * @api
    *
    * @param int $id
    * @param int $cardId
    * @return \Webkul\Customisation\Api\StripeDeleteSaveCardApiManagementInterface
    */
    public function getApiData($id, $cardId);
}