<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_UvDeskConnector
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\UvDeskConnector\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Webkul\UvDeskConnector\Logger\UvdeskLogger;

/**
 * Data class
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $resourceConfig;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    
    /**
     * @var \Webkul\UvDeskConnector\Logger\UvdeskLogger
     */
    private $logger;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * __construct function
     *
     * @param \Magento\Framework\App\Helper\Context                 $context
     * @param \Magento\Config\Model\ResourceModel\Config            $resourceConfig
     * @param \Magento\Customer\Model\Session                       $customerSession
     * @param UvdeskLogger                                          $uvdeskLogger
     * @param \Magento\Framework\Encryption\EncryptorInterface      $encryptor
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Customer\Model\Session $customerSession,
        UvdeskLogger $uvdeskLogger,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
    
        $this->resourceConfig = $resourceConfig;
        $this->customerSession = $customerSession;
        $this->logger = $uvdeskLogger;
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    /**
     * Return the status of module.
     *
     * @return Boolean.
     */
    public function getAvilabilityOfUvdesk()
    {
        $status =  $this->scopeConfig
                                 ->getValue(
                                     'uvdesk_conn/uvdesk_config/uvdesk_status',
                                     \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                 );
        return $status;
    }

    /**
     * Return the access token.
     *
     * @return String.
     */
    public function getAccessToken()
    {
        $accessToken =  $this->scopeConfig
                                 ->getValue(
                                     'uvdesk_conn/uvdesk_config/uvdesk_accesstoken',
                                     \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                 );
        return $this->encryptor->decrypt($accessToken);
    }

    /**
     * Return the company domain name.
     *
     * @return String.
     */
    public function getCompanyDomainName()
    {
        $companyDomainName =  $this->scopeConfig
                                 ->getValue(
                                     'uvdesk_conn/uvdesk_config/uvdesk_companydomain',
                                     \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                 );
        return $companyDomainName;
    }

    /**
     * Return the status of customer log in.
     *
     * @return Boolean.
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Return the secret key for encoding of customer data for SSO.
     *
     * @return String.
     */
    public function getSecretket()
    {
        $secretkey =  $this->scopeConfig
                                 ->getValue(
                                     'uvdesk_conn/uvdesk_config_sso/uvdesk_sso_secret_key',
                                     \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                 );
        return $secretkey;
    }

    /**
     * Return the redirecting url for SSO.
     *
     * @return String.
     */
    public function getRedirectUrl()
    {
        $url =  $this->scopeConfig
                                 ->getValue(
                                     'uvdesk_conn/uvdesk_config_sso/uvdesk_sso_redirect_url',
                                     \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                 );
        return $url;
    }

    /**
     * log function log the error message.
     *
     * @param string $message
     * @param array $data
     * @return void
     */
    public function loge($message = "", $data = [])
    {
        $this->logger->critical($message, $data);
    }

    /**
     * getErrorMessage function return the error message acc to key of response array
     *
     * @param array $tickets
     * @return string
     */
    public function getErrorMessage($tickets = [])
    {
        if (isset($tickets['error_description'])) {
            return $tickets['error_description'];
        }
        if (isset($tickets['error']) && $tickets['error']!==1 && $tickets['error']!==0) {
            return $tickets['error'];
        }
        return __("Please properly filled the Uvdesk configuration.");
    }
}
