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
namespace Webkul\MpStripe\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level.
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     *
     * @var int
     */
    protected $loggerType = StripeLogger::CRITICAL;

    /**
     * File name.
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     *
     * @var string
     */
    protected $fileName = '/var/log/stripe.log';
}
