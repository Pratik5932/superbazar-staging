<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_UvDeskConnector
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\UvDeskConnector\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level.
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     *
     * @var int
     */
    protected $loggerType = UvdeskLogger::CRITICAL;

    /**
     * File name.
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     *
     * @var string
     */
    protected $fileName = '/var/log/uvdesk.log';
}
