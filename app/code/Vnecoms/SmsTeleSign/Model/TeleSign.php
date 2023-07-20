<?php
namespace Vnecoms\SmsTeleSign\Model;

use Vnecoms\Sms\Model\Sms;

class TeleSign implements \Vnecoms\Sms\Model\GatewayInterface
{
    /**
     * @var \Vnecoms\SmsTeleSign\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * @param \Vnecoms\SmsTeleSign\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Vnecoms\SmsTeleSign\Helper\Data $helper,
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
        return __("TeleSign");
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\Sms\Model\GatewayInterface::validateConfig()
     */
    public function validateConfig(){
        $classExist = class_exists('telesign\sdk\messaging\MessagingClient');
        if(!$classExist){
            $this->logger->error(__("Class \telesign\sdk\messaging\MessagingClient does not exist. run 'composer require telesign/telesign' to install TeleSign sdk."));
        }
        return $classExist &&
            $this->helper->getCustomerId() &&
            $this->helper->getApiKey();
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\Sms\Model\GatewayInterface::sendSms()
     */
    public function sendSms($number, $message){
        $customerId     = $this->helper->getCustomerId();
        $apiKey         = $this->helper->getApiKey();
        $messageType    = "ARN";
        $messageClient = new \telesign\sdk\messaging\MessagingClient($customerId, $apiKey);
        $response = $messageClient->message($number, $message, $messageType);
        
        $response = $response->json;
        
        $result = [
            'sid'       => $response['reference_id'],
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
        $messageStatus = $message['status']['code'];
        switch($messageStatus){
            case "290":
            case "291":
            case "292":
                $status = Sms::STATUS_SENT;
                break;
            case "200":
                $status = Sms::STATUS_DELIVERED;
                break;
        }
    
        return $status;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\Sms\Model\GatewayInterface::getSms()
     */
    public function getSms($sid){
        $customerId     = $this->helper->getCustomerId();
        $apiKey         = $this->helper->getApiKey();
        $messageClient = new \telesign\sdk\messaging\MessagingClient($customerId, $apiKey);
        return $messageClient->status($sid);
    }
}
