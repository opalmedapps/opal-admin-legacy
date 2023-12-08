<?php
// Server file
include_once "database.inc";
include_once("../../php/config.php");
include_once("../../php/classes/NewOpalApiCall.php");

class PushNotifications {
	// (Android)API access key from Google API's Console.
	private static $api_key = API_KEY ;
	// (iOS) Private key's passphrase.
	private static $passphrase = CERTIFICATE_PASSWORD;
	//(iOS) Location of certificate file
	private static $certificate_file = CERTIFICATE_FILE;
	// iOS Location of cert key
	private static $certificate_key = CERTIFICATE_KEY;
	// (iOS) APNS topic (staging, preprod, prod)
	private static $apns_topic = APNS_TOPIC;
	// (iOS) APN Url target (development or sandbox)
	private static $ios_url = IOS_URL;

	// **************************************************
	// Get patient caregiver devices information
	// **************************************************
	/**
	* @param $patientSerNum
	* @return patient caregiver devices info
	**/
	public static function getPatientDevicesInfo($patientSerNum)
	{
		$backendApi = new NewOpalApiCall(
		'/api/patients/legacy/'.$patientSerNum.'/caregiver-devices/',
		'GET',
		'en',
			[],
		);
		$response = $backendApi->execute();
		$response = json_decode($response, true);
		$caregivers = $response['caregivers'];
		$userNameArray = [];
		foreach ($caregivers as $caregiver) {
			$userNameArray[] = $caregiver['username'];
		}

		$userNameArrayString = implode("','", $userNameArray);
		$userNameArrayString = "'".$userNameArrayString."'";

		return self::getPatientDevicesByUsernames($userNameArrayString);
	}

	// **************************************************
	// Get patient devices information by user names
	// **************************************************
	/**
	 * @param $patientSerNum
	 * @return patient caregiver devices info
	 **/
	private static function getPatientDevicesByUsernames($userNameArrayString)
	{
		global $pdo;

		$sql = "
			SELECT DISTINCT
				ptdid.PatientDeviceIdentifierSerNum,
				ptdid.RegistrationId,
				ptdid.DeviceType
			FROM
				PatientDeviceIdentifier ptdid
			WHERE ptdid.DeviceType in ('0', '1')
			AND Username in ($userNameArrayString)
			AND IfNull(RegistrationId, '') <> ''
		";

		try {
			return $pdo->query($sql);
		} catch(PDOException $e) {
			return array();
		}
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
	* 
	**/
	public static function android($data, $reg_id) {
		// $url = 'https://fcm.googleapis.com/fcm/send';
		$url = ANDROID_URL;

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

		// **** Uncomment the below lines for troubleshooting
		// $myfile = fopen("/var/www/html/opalAdmin/publisher/logs/PushNotification.log", "a");
		// fwrite($myfile, print_r([$response, $message],true)."\n");
		// fclose($myfile);

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
		//curl_setopt($ch, CURLOPT_SSLCERTPASSWD, self::$passphrase); //pem secret
		curl_setopt($ch, CURLOPT_SSLKEY, self::$certificate_key); // cert key
		//curl_setopt($ch, CURLOPT_SSLKEYPASSWD, ); if we add a password to the key file we'll specify that here
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// **** Uncomment the below lines for troubleshooting
		// $myfile = fopen("/var/www/html/opalAdmin/publisher/logs/PushNotification.log", "a");
		// fwrite($myfile, "http code: $httpcode");
		// fwrite($myfile, print_r([$response,$body],true)."\n");
		// fclose($myfile);

        if ($httpcode != 200) {
			$err = curl_error($ch);
			$response =  array("success"=>0,"failure"=>1,"error"=>"$err");
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
			$outTitle = stripslashes(utf8_encode($inTitle));
		}
	
		if ($validUTF8inBody) {
			$outBody = stripslashes($inBody);
		} else {
			$outBody = stripslashes(utf8_encode($inBody));
		}

	// return title and body in an array
	return array($outTitle, $outBody);
   }

}
?>
