<?php
namespace Vnecoms\SmsKapsystem\Rest;

use GuzzleHttp\json_decode;

class Client
{
    const API_URL = '103.16.101.52:8080/sendsms/bulksms/';
    
    /**
     * @var string
     */
    protected $username;
    
    /**
     * @var string
     */
    protected $password;
    
    /**
     * Create a new API client
     * 
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Send Sms
     * 
     * @param string $number
     * @param string $message
     * @throws \Exception
     * @return mixed
     */
    public function sendSms($number, $message, $sender, $apiUrl = false, $type = 0, $dlr = 0){
        $apiUrl = $apiUrl?$apiUrl:self::API_URL;
        $apiUrl = trim($apiUrl, '/');
        $postParams = [
            'username'  => $this->username,
            'password'  => $this->password,
            'type'      => $type,
            'dlr'       => $dlr,
            'source'    => $sender,
            'destination' => $number,
            'message'   => $message,            
        ];
        $newPost = [];
        foreach($postParams as $key => $value){
            $newPost[] = $key.'='.urlencode($value);
        }
        $postParam = implode("&", $newPost);
        $apiUrl.='?'.$postParam;

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $apiUrl);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($c);

        if (curl_error($c)) {
            curl_close($c);
            throw new \Exception(curl_error($c));
        }
        
        curl_close($c);
        return explode('|',$result);
    }
    
    /**
     * Get message by message id
     * 
     * @param string $messageId
     * @throws \Exception
     * @return mixed
     */
    public function getMessage($messageId){
        throw new \Exception(__("This method is not supported"));
    }
}
