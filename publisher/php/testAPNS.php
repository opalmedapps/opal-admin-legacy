<?php 
    include_once "database.inc";
class testAPNS {
	// (iOS) Private key's passphrase.
	private static $passphrase = CERTIFICATE_PASSWORD; //pem secret
	//(iOS) Location of certificate file
	private static $certificate_file = CERTIFICATE_FILE; //pem file

    private static $apns_topic = "com.hig.opalstaging"; //apns topic TODO move to config

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
        $apns_topic = self::$apns_topic;
        $url = "https://api.sandbox.push.apple.com/3/device/$devicetoken";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTP_VERSION,3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["apns-topic: $apns_topic"]); //opal app bundle ID
        curl_setopt($ch, CURLOPT_SSLCERT, self::$certificate_file); //pem file
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, self::$passphrase); //pem secret
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //var_dump(curl_error($ch));
        var_dump($response); //success status code 200 
        //echo("$httpcode\n");

  }


}


// The above as a bash: (on dev)
// /opt/curl/src/curl --tlsv1.3 --http2 -X POST \
// -H "Content-Type: application/json" -H "apns-topic: com.hig.opalstaging" \
// -d '{"aps":{"alert":{"title":"Test APNS 2021-03-11 11:07:38","body":"Ping ping ping read me Im a notification"},"sound":"default"}}' \
// --cert ./certificates/development_com.hig.opalstaging.pem \
// https://api.sandbox.push.apple.com/3/device/06e65558a942e00ef8a5651472638ad6144bbfa936c4a5c78abce2b08e1341c7





?>
