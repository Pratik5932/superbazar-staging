<?php

namespace Ren\Pushapp\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Ordersaveafter implements ObserverInterface {
    public function execute(\Magento\Framework\Event\Observer $observer) {
		
		
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$tableName = $resource->getTableName('riw_notification'); //gives table name with prefix
			$tableName2 = $resource->getTableName('pushapp_pushapp'); //gives table name with prefix

			$sql = "Select token FROM " . $tableName;
			$result = $connection->fetchAll($sql);
			$registrationIds = array();
			foreach($result as $value){
				$registrationIds[] = $value['token'] ;
			}
			
			$sql2 = "Select server_key FROM " . $tableName2;
			$result2 = $connection->fetchAll($sql2);
            $API_ACCESS_KEY = '';
			foreach($result2 as $value2){
				$API_ACCESS_KEY = $value2['server_key'];
			}
		    
		if($API_ACCESS_KEY!=''){
		    define('API_ACCESS_KEY', $API_ACCESS_KEY);
       
            $title = "Order status change in Superbazzar";
            $body = "Order status change in Superbazzar from a customer";
            $notification = array('title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1');
		    //$registrationIds = array();
		    //$registrationIds[] = 'elVObjlaZ9U:APA91bGlEZIcXSejLUNtMnro68VuPjo4FDltPydwPlxty_OK5nyQjIng0prMr20NRjYUxJzMCd0a2FvmG8aR0J9XTaGvBeelJKcAiLuWteBDEjz4vDjwUKoWGPt6_0J_Er4weiKkMUGP';
       
       $json_data = array('registration_ids' => array_values($registrationIds), 'notification' => $notification, 'priority' => 'high');
            $data = json_encode($json_data);
		    //echo '<pre>';
		    //print_r($json_data);
            $url = 'https://fcm.googleapis.com/fcm/send';
            $headers = array(
                'Content-Type:application/json',
                'Authorization:key=' . API_ACCESS_KEY
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $result = curl_exec($ch);
		    
		    $response=curl_exec($ch);
		    //print_r($response);
       //$response = json_decode($response,true);
      // print_r($response);
		    
            if ($result === FALSE) {
                die('Oops! FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
            if (count($registrationIds) > 0) {
			    
    //            mail('testdeveloper140@gmail.com', 'FCM Send', implode(', ', $registrationIds));
            }
        }

       
    }
}
