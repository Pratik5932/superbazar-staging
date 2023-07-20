<?php
namespace Vnecoms\SmsBulkSms\Rest;

use GuzzleHttp\json_decode;
use Vnecoms\SmsBulkSms\Model\Config\Source\MessageType;

class Client
{
    const API_URL = 'https://bulksms.vsms.net/eapi/submission/send_sms/2/2.0';
    
    /**
     * BulkSms username
     * 
     * @var string
     */
    protected $username;
    
    /**
     * BulkSms password
     *
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
     * Send SMS
     * 
     * @param string $number
     * @param string $message
     */
    public function sendSms($number, $message, $messageType = MessageType::TYPE_UNICODE){
        switch($messageType){
            case MessageType::TYPE_8BIT:
                $postBody = $this->eightBitSms($message, $number);
                break;
            case MessageType::TYPE_UNICODE:
                $postBody = $this->unicodeSms($message, $number);
                break;
            default:
                $postBody = $this->unicodeSms($message, $number);
        }
        return $this->sendMessage($postBody);
    }
    
    /**
     * Format server response
     * 
     * @param unknown $result
     * @return string
     */
    public function formatServerResponse($result) {
        $this_result = "";
    
        if ($result['success']) {
            $this_result .= "Success: batch ID " .$result['api_batch_id']. "API message: ".$result['api_message']. "\nFull details " .$result['details'];
        }
        else {
            $this_result .= "Fatal error: HTTP status " .$result['http_status_code']. ", API status " .$result['api_status_code']. " API message " .$result['api_message']. " full details " .$result['details'];
    
            if ($result['transient_error']) {
                $this_result .=  "This is a transient error - you should retry it in a production environment";
            }
        }
        return $this_result;
    }
    
    /**
     * Send message
     * 
     * @param string $postBody
     * @param string $url
     * @return multitype:number string unknown mixed Ambigous <>
     */
    protected function sendMessage($postBody) {

        $ch = curl_init();
        curl_setopt ( $ch, CURLOPT_URL, self::API_URL );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postBody );
        // Allowing cUrl funtions 20 second to execute
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
        // Waiting 20 seconds while trying to connect
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 20 );
    
        $responseString = curl_exec( $ch );
        $curl_info = curl_getinfo( $ch );
        $smsResult = array();
        $smsResult['success'] = 0;
        $smsResult['details'] = '';
        $smsResult['transient_error'] = 0;
        $smsResult['http_status_code'] = $curl_info['http_code'];
        $smsResult['api_status_code'] = '';
        $smsResult['api_message'] = '';
        $smsResult['api_batch_id'] = '';
    
        if ( $responseString == FALSE ) {
            $smsResult['details'] .= "cURL error: " . curl_error( $ch ) . "\n";
        } elseif ( $curl_info[ 'http_code' ] != 200 ) {
            $smsResult['transient_error'] = 1;
            $smsResult['details'] .= "Error: non-200 HTTP status code: " . $curl_info[ 'http_code' ] . "\n";
        }
        else {
            $smsResult['details'] .= "Response from server: $responseString\n";
            $apiResult = explode( '|', $responseString );
            $status_code = $apiResult[0];
            $smsResult['api_status_code'] = $status_code;
            $smsResult['api_message'] = $apiResult[1];
            if ( count( $apiResult ) != 3 ) {
                $smsResult['details'] .= "Error: could not parse valid return data from server.\n" . count( $apiResult );
            } else{
                if ($status_code == '0') {
                    $smsResult['success'] = 1;
                    $smsResult['api_batch_id'] = $apiResult[2];
                    $smsResult['details'] .= "Message sent - batch ID $apiResult[2]\n";
                }else if ($status_code == '1') {
                    # Success: scheduled for later sending.
                    $smsResult['success'] = 1;
                    $smsResult['api_batch_id'] = $apiResult[2];
                }else {
                    $smsResult['details'] .= "Error sending: status code [$apiResult[0]] description [$apiResult[1]]\n";
                }
            }
        }
        curl_close( $ch );
    
        return $smsResult;
    }

    /**
     * Format Unicode Sms
     * 
     * @param string $message
     * @param string $msisdn
     * @return string
     */
    protected function unicodeSms ($message, $msisdn) {
        $postFields = array (
            'username' => $this->username,
            'password' => $this->password,
            'message'  => $this->stringToUtf16Hex($message),
            'msisdn'   => $msisdn,
            'dca'      => '16bit'
        );
    
        return $this->makePostBody($postFields);
    }
    
    /**
     * Format 8bit sms
     * 
     * @param string $message
     * @param string $msisdn
     * @return string
     */
    protected function eightBitSms($message, $msisdn) {
        $postFields = array (
            'username' => $this->username,
            'password' => $this->password,
            'message'  => $message,
            'msisdn'   => $msisdn,
            'dca'      => '8bit'
        );
    
        return $this->makePostBody($postFields);
    }
    
    /**
     * Make post body fields
     * 
     * @param array $postFields
     * @return string
     */
    protected function makePostBody($postFields) {
        $stopDupId = $this->makeStopDupId();
        if ($stopDupId > 0) {
            $postFields['stop_dup_id'] = $this->makeStopDupId();
        }
        $postBody = '';
        foreach( $postFields as $key => $value ) {
            $postBody .= urlencode( $key ).'='.urlencode( $value ).'&';
        }
        $postBody = rtrim( $postBody,'&' );
    
        return $postBody;
    }


    protected function makeStopDupId() {
        return 0;
    }
    
    /**
     * Convert string to UTF16 Hex
     * 
     * @param string $string
     * @return string
     */
    protected function stringToUtf16Hex($string) {
        return bin2hex(mb_convert_encoding($string, "UTF-16", "UTF-8"));
    }
    
    /**
     * Get message by message id
     * 
     * @param string $messageId
     * @throws \Exception
     * @return mixed
     */
    public function getMessage($messageId){
        throw new \Exception(__("Get message method is not supported"));
    }
}
