<?php
namespace Vnecoms\SmsBulkSms\Model;

use Vnecoms\Sms\Model\Sms;

class BulkSms implements \Vnecoms\Sms\Model\GatewayInterface
{
    /**
     * @var \Vnecoms\SmsBulkSms\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * @param \Vnecoms\SmsBulkSms\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Vnecoms\SmsBulkSms\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->helper = $helper;
        $this->logger = $logger;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\Sms\Model\GatewayInterface::getTitle()
     */
    public function getTitle(){
        return __("BulkSms");
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\Sms\Model\GatewayInterface::validateConfig()
     */
    public function validateConfig(){
        return
            $this->helper->getUsername() &&
            $this->helper->getPassword();
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\Sms\Model\GatewayInterface::sendSms()
     */
    public function sendSms($number, $message){
        $username   = $this->helper->getUsername();
        $password   = $this->helper->getPassword();
                
        $client = new \Vnecoms\SmsBulkSms\Rest\Client($username, $password);
        $response = $client->sendSms($number, $message);

        $result = [
            'sid'       => $response['api_batch_id'],
            'status'    => $this->getMessageStatus($response),
        ];

        return $result;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\Sms\Model\GatewayInterface::getMessageStatus()
     */
    public function getMessageStatus($message){
        $status = Sms::STATUS_FAILED;
        switch($message['api_status_code']){
            case "0":
            case "1":
                $status = Sms::STATUS_SENT;
                break;
        }
    
        return $status;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\Sms\Model\GatewayInterface::getSms()
     */
    public function getSms($sid){
        $apiKey     = $this->helper->getApiKey();
        $apiSecret  = $this->helper->getApiSecret();
        
        $client = new \Vnecoms\SmsGlobal\Rest\Client($apiKey, $apiSecret);
        $message = $client->getMessage($sid);
        
        return $message;
    }
}
