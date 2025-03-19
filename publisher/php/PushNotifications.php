<?php
// Server file

class PushNotifications {
	// (Android)API access key from Google API's Console.
	private static $api_key = API_KEY ;
	// (iOS) Private key's passphrase.
	private static $passphrase = CERTIFICATE_PASSWORD;
	//(iOS) Location of certificate file
	private static $certificate_file = CERTIFICATE_FILE;
	// (iOS) APNS topic (staging, preprod, prod)
	private static $apns_topic = APNS_TOPIC;
	// (iOS) APN Url target (development or sandbox)
	private static $ios_url = IOS_URL;

	// **************************************************
	// Sends Push notification for Android users
	// **************************************************
	/**
	*	(android($data, $reg_id)) Consumes an array with message
	*	to be sent and a registration id.
	*	Description: Creates curl request to FCM (Firebase Cloud Messaging) and sends
	*                push notification to android device
	*   Requires: $data must contain mtitle, and mdesc for the
	*             push notification.
	**/
	public static function android($data, $reg_id) {
		// $url = 'https://fcm.googleapis.com/fcm/send';
		$url = ANDROID_URL;

		// Flag to identify when to use the utf8_encode because message coming
		// from PERL alters the French characters
		$wsFlag = (isset($data['encode'])? $data['encode'] :'Yes' );

		if ($wsFlag == 'Yes') {
			$wsTitle = utf8_encode($data['mtitle']);
			$wsBody = utf8_encode($data['mdesc']);
		} else {
			$wsTitle = $data['mtitle'];
			$wsBody = $data['mdesc'];
		}

		// Create a unique Post ID so that the push notification
		// will not override the previous push notification by using
		// time format (hours, minutes, and seconds)
		// Ex: 10:35:23 would be 103523
		$wsDate = date("His");

		$message = array(
			'notId' 				=> $wsDate,
			'title'					=> $wsTitle,
			'body'					=> $wsBody,
			'channelId'				=> 'opal',
			'payload'				=> array(
				'aps'				=> array(
					'category'		=> 'opal'
				)
			)
		);

		$headers = array(
			'Authorization: key=' .self::$api_key,
			'Content-Type: application/json'
		);

		// data -->> is the message of the body
		// notification -->> is a short title of the text message (about 64 characters)
		$fields = array(
			'registration_ids' => array($reg_id),
			'data' => $message
			// 'notification' => $message
		);

		$response = self::useCurl($url, $headers, json_encode($fields));
		$response = json_decode($response,true);

		$data = array();
		$data["success"] = $response["success"];
		$data["failure"] = $response["failure"];
		if($response["success"]==0) {
			$data["error"]=$response["results"][0]["error"];
		}
		return $data;

	}

	// **************************************************
	// Sends Push notification for iOS users
	// **************************************************
  /**
	*	(iOS($data, $devicetoken)) Consumes an array with message
	*	to be sent and a registration id.
	*	Description: Creates a connection to APN (Apple Push Notification
	*              socket and sends push notification to android device
	*   Requires: $data must contain mtitle, and mdesc for the
	*             push notification.
	**/
	public static function iOS($data, $devicetoken) {
		//validation and message prep
		if(is_array($data)){
			if(isset($data['encode']) && ($data['encode'] == 'Yes')){
				$wsTitle = utf8_encode($data['mtitle']);
				$wsBody = utf8_encode($data['mdesc']);
			}else{ // caller did not set encode property
				$wsTitle = $data['mtitle'];
				$wsBody = $data['mdesc'];
			}
        }else{ //data not array error
			$response =  array("success"=>0,"failure"=>1,"error"=>"Request data invalid, unable to send push notification.");
			return $response;
		}

		// Create the payload body
		$body['aps'] = array(
			'alert' => array(
				'title' => $wsTitle,
				'body' => $wsBody,
			),
			'sound' => 'default'
		);
		// Encode the payload as JSON
        $payload = json_encode($body);
	
		$apns_topic = self::$apns_topic;
		$url = self::$ios_url . $devicetoken;    
        $ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_HTTP_VERSION,3);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["apns-topic: $apns_topic"]); //opal app bundle ID
		curl_setopt($ch, CURLOPT_SSLCERT, self::$certificate_file); //pem file
		curl_setopt($ch, CURLOPT_SSLCERTPASSWD, self::$passphrase); //pem secret
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != 200) {
			$response =  array("success"=>0,"failure"=>1,"error"=>"Unable to send packets to APN socket");
		} else {
			$response =  array("success"=>1,"failure"=>0);
        }
		return $response;
	}
	// Curl
	private static function useCurl($url, $headers, $fields = null) {
		// Open connection
		$ch = curl_init();
		if ($url) {
			// Set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			// Disabling SSL Certificate support temporarly
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			if ($fields) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			}

			// Execute post
			$result = curl_exec($ch);
			if ($result === FALSE) {
				$result = "{\"Success\":0,\"Failure\":1,\"Error\":\"Connection to Google servers failed\"}";
				die('Curl failed: ' . curl_error($ch));
			}

			// Close connection
			curl_close($ch);
			return $result;
		}
   }
}
?>
