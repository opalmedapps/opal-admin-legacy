<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

// Server file
include_once("../../php/config.php");
include_once("../../php/classes/FirebaseOpal.php");


class PushNotification {
	//(iOS) Location of certificate file
	private static $certificate_file = CERTIFICATE_FILE;
	// iOS Location of cert key
	private static $certificate_key = CERTIFICATE_KEY;
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
	* 
	**/
	public static function android($data, $reg_id) {
		$url = ANDROID_URL;

		// Validation and message prep
		if (is_array($data)) {
			// Encode (UTF8) the title and body
			$result = self::encodePayload($data['mtitle'], $data['mdesc'] );
			$wsTitle = $result[0];
			$wsBody =  $result[1];
		}
		else {
			$response =  array("success"=>0,"failure"=>1,"error"=>"Request data invalid, unable to send push notification.");
			return $response;
		}

		$notification = array(
			'message' => array(
				// Target device's registration ID (to which the notification will be sent)
				'token' => $reg_id,

				// General notification content
				'notification' => array(
					'title' => $wsTitle,
					'body' => $wsBody,
				),

				// Android-specific settings
				'android' => array(
					'notification' => array(
						'channel_id' => 'opal',
					),
				),
			),
		);

		// For Authorization format, see: https://firebase.google.com/docs/cloud-messaging/migrate-v1#update-authorization-of-send-requests
		$headers = array(
			'Authorization: Bearer ' . FirebaseOpal::getFCMAuthToken(),
			'Content-Type: application/json'
		);

		$response = self::useCurl($url, $headers, json_encode($notification));
		$response = json_decode($response,true);

		// **** Uncomment the below lines for troubleshooting
		// $myfile = fopen("/var/www/html/publisher/logs/PushNotification.log", "a");
		// fwrite($myfile, print_r([$response, $message],true)."\n");
		// fclose($myfile);

		$data = array();
		$data["success"] = $response["name"] ? 1 : 0;
		$data["failure"] = $response["error"] ? 1 : 0;
		if ($data["failure"] == 1) {
			$data["error"] = $response["error"]["message"];
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

			// encode (UTF8) the title and body
			$result = self::encodePayload($data['mtitle'], $data['mdesc'] );
			$wsTitle = $result[0];
			$wsBody =  $result[1];

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
		curl_setopt($ch, CURLOPT_SSLKEY, self::$certificate_key); // cert key
		//curl_setopt($ch, CURLOPT_SSLKEYPASSWD, ); if we add a password to the key file we'll specify that here
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// **** Uncomment the below lines for troubleshooting
		// $myfile = fopen("/var/www/html/publisher/logs/PushNotification.log", "a");
		// fwrite($myfile, "http code: $httpcode");
		// fwrite($myfile, print_r([$response,$body],true)."\n");
		// fclose($myfile);

        if ($httpcode != 200) {
			$err = curl_error($ch);
			$error_message = ($err) ? "$err" : json_decode($response);
			$response =  array("success" => 0, "failure" => 1, "error" => $error_message);
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
   
	// **************************************************
	// encode Payload
	// **************************************************
	/**
	*	(encodePayload($inTitle, $inBody)) receive message,
	*	convert to utf8, and return message
	*	Description: if the title or messsage is not utf8 then proceed to
	*				to encode the title and/or message to utf8.
    *				Then proceed to strip slashes to the title and message
	*   Requires: 	$inTitle -> title of the message
	*				$inBody -> body of the message
	**/
	private static function encodePayload($inTitle, $inBody) {
	
		$validUTF8inTitle = mb_check_encoding($inTitle, 'UTF-8');
		$validUTF8inBody = mb_check_encoding($inBody, 'UTF-8');
	
		if ($validUTF8inTitle) {
			$outTitle = stripslashes($inTitle);
		} else {
			$titleStr =  mb_convert_encoding($inTitle, 'UTF-8', 'ISO-8859-1');
			$outTitle = stripslashes($titleStr);
		}
	
		if ($validUTF8inBody) {
			$outBody = stripslashes($inBody);
		} else {
			$bodyStr =  mb_convert_encoding($inBody, 'UTF-8', 'ISO-8859-1');
			$outBody = stripslashes($bodyStr);
		}

	// return title and body in an array
	return array($outTitle, $outBody);
   }
}
?>
