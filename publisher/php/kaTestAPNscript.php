<?php

	$device="06e65558a942e00ef8a5651472638ad6144bbfa936c4a5c78abce2b08e1341c7"; //jinal
	$message = array(
		"mtitle"=>'Test APNS ' . date("Y-m-d H:i:s"),
		"mdesc"=>'Ping ping ping read me I\'m a notification',
		"encode"=>'Yes'
			);
	// Combine room info

    //include_once('HospitalPushNotification.php');
	include_once('testAPNS.php');
	
    // Call API
    // $responses = HospitalPushNotification::sendCallPatientNotification($patientId, $room, $apptSourceUID);

	echo "SENDING APNS at " . date("Y-m-d H:i:s") . "<br>";

	// $response = PushNotifications::iOS($message, $device);
	$response = testAPNS::APN_iOS($message, $device);

	echo "Complete <br>";

    // Return responses
    print json_encode($response);
?>
