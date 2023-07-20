<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Api;
 
interface WebhookInterface
{
    /**
     * handle stripe webhook request
     *
     * @api
     * @return void
     */
    public function executeWebhook();
}
