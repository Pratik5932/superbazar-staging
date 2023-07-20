<?php
namespace Vnecoms\SmsKapsystem\Model;

use Vnecoms\Sms\Model\Sms;

class Kapsystem implements \Vnecoms\Sms\Model\GatewayInterface
{
    /**
     * @var \Vnecoms\SmsKapsystem\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * @param \Vnecoms\SmsKapsystem\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Vnecoms\SmsKapsystem\Helper\Data $helper,
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
        return __("Kapsystem");
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\Sms\Model\GatewayInterface::validateConfig()
     */
    public function validateConfig(){
        return
            $this->helper->getUsername() &&
            $this->helper->getPassword() &&
            $this->helper->getSender() &&
            $this->helper->getApiUrl();
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\Sms\Model\GatewayInterface::sendSms()
     */
    public function sendSms($number, $message){
        $username   = $this->helper->getUsername();
        $password   = $this->helper->getPassword();
        $sender     = $this->helper->getSender();
        $apiUrl     = $this->helper->getApiUrl();
        
        $client = new \Vnecoms\SmsKapsystem\Rest\Client($username, $password);
        $response = $client->sendSms($number, $message, $sender, $apiUrl);
        $statusCode = $response[0];
        
        if($statusCode == '1701'){
            $response = explode(":", $response[1]);
            $result = [
                'sid'       => $response[1],
                'status'    => sms::STATUS_SENT,
            ];
        }

        return $result;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\Sms\Model\GatewayInterface::getMessageStatus()
     */
    public function getMessageStatus($message){
        $status = Sms::STATUS_FAILED;
    
        return $status;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Vnecoms\Sms\Model\GatewayInterface::getSms()
     */
    public function getSms($sid){
        throw new \Exception(__("this method is not supported"));
    }
}
