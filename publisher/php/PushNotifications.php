<?php
// Server file

class PushNotifications {
	// (Android)API access key from Google API's Console.
	private static $api_key = API_KEY ;
	// (iOS) Private key's passphrase.
	private static $passphrase = CERTIFICATE_PASSWORD;
	//(iOS) Location of certificate file
	private static $certificate_file = CERTIFICATE_FILE;

	// Change the above three vriables as per your app.
	public function __construct() {
		exit('Init function is not allowed');
	}

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
/* 		$response =  array("success"=>1,"failure"=>0);
		return $response;
 */

		$deviceToken = $devicetoken;
		$ctx = stream_context_create();
		// ck.pem is your certificate file
		stream_context_set_option($ctx, 'ssl', 'local_cert', self::$certificate_file);
		stream_context_set_option($ctx, 'ssl', 'passphrase', self::$passphrase);
		// Open a connection to the APNS server
		// $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err,
		$fp = stream_socket_client(IOS_URL, $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		if (!$fp) {
			$response = array("success"=>0,"failure"=>1,"error"=>"Failed to connect: $err $errstr" . PHP_EOL);
			return $response;
		}

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
		// Build the binary notification

		// echo 'Device Token :' .  $deviceToken . '<br />';
		if (strlen($deviceToken) == 64) {
			$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
			// Send it to the server
			$result = fwrite($fp, $msg, strlen($msg));
			// Close the connection to the server
			fclose($fp);
			if (!$result) {
				$response =  array("success"=>0,"failure"=>1,"error"=>"Unable to send packets to APN socket");
			} else {
				$response =  array("success"=>1,"failure"=>0);
			}
			return $response;
			}

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
