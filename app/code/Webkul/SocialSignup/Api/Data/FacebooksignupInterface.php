<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Api\Data;

/**
 * SocialSignup FacebooksignupInterface interface.
 *
 * @api
 */
interface FacebooksignupInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';

    const CUSTOMER_ID = 'customer_id';

    const FB_ID = 'fb_id';

    /**#@-*/

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\SocialSignup\Api\Data\SocialSignupFbCustomerInterface
     */
    public function setId($id);

    /**
     * get facebook id
     *
     * @return int|null
     */
    public function getFbId();

    /**
     * get customer id
     *
     * @return int|null
     */
    public function getCustomerId();
}
