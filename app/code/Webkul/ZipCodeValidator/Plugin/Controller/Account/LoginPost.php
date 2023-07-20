<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ZipCodeValidator\Plugin\Controller\Account;

use Magento\Customer\Controller\Account\LoginPost as Login;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;

class LoginPost
{
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param AccountRedirect $accountRedirect
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $httpRequest,
        AccountRedirect $accountRedirect,
        \Webkul\ZipCodeValidator\Helper\Data $helper
    ) {
        $this->_httpRequest = $httpRequest;
        $this->accountRedirect = $accountRedirect;
        $this->_helper = $helper;
    }

    public function beforeExecute()
    {
        if ($this->_helper->getEnableDisable()) {
            $currentUrl = $this->_httpRequest->getServer('HTTP_REFERER');
            $this->accountRedirect->setRedirectCookie($currentUrl);
        }
    }
}
