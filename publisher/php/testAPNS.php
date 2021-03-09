<?php 
    include_once "database.inc";
class testAPNS {
	// (iOS) Private key's passphrase.
	private static $passphrase = CERTIFICATE_PASSWORD; //pem secret
	//(iOS) Location of certificate file
	private static $certificate_file = CERTIFICATE_FILE; //pem file

    private static $apns_topic = "com.hig.opalstaging"; //apns topic

	// Change the above three vriables as per your app.
	public function __construct() {
		exit('Init function is not allowed');
	}

    public static function APN_iOS($data, $devicetoken) {
	
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
        

        // use curl to send APN
        if(defined('CURL_HTTP_VERSION_2_0')){
            $deviceToken = $devicetoken;
            $apns_topic = self::$apns_topic;
            $url = "https://api.development.push.apple.com/3/device/$device_token";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("apns-topic: $apns_topic")); //opal app bundle ID
            curl_setopt($ch, CURLOPT_SSLCERT, self::$certificate_file); //pem file
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, self::$passphrase); //pem secret
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            echo($response); //success status code 200
            echo($httpcode);



        }else{
            echo("Error, CURL_HTTP_VERSION_2_0 does not exist");
        }
       

		
  }


}







?>