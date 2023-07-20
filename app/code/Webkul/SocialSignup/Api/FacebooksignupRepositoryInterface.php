<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Api;

/**
 * @api
 */
interface FacebooksignupRepositoryInterface
{
    /**
     * get customer collection
     * @param  int $customer_id customer id
     * @return object
     */
    public function getByCustomerId($customerId);

    /**
     * get customer collection by facebook id
     * @param  int $fbId facebook id
     * @return object
     */
    public function getByFbId($fbId);
    /**
     * get customer by id
     * @return object
     */
    public function getById($entityId);
    /**
     * save data in table
     * @return [type] [description]
     */
}
