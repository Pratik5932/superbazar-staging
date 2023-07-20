<?php
namespace Vnecoms\SmsTeleSign\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_CUSTOMER_ID   = 'vsms/settings/telesign_customer_id';
    const XML_PATH_API_KEY      = 'vsms/settings/telesign_api_key';
    
    /**
     * Get api key
     * 
     * @return string
     */
    public function getApiKey(){
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY);
    }
    
    /**
     * Get Customer Id
     *
     * @return string
     */
    public function getCustomerId(){
        return $this->scopeConfig->getValue(self::XML_PATH_CUSTOMER_ID);
    }
}