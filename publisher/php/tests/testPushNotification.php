<?php
/*
    Update this test script so that it can be called without changing its code by using command-line arguments.

	The first argument (arg[1]) is device Id.
    The second argument (arg[2]) is device type, 0 is IOS and 1 is Android.
    The third argument (arg[3]) is language, en is English and fr is French.

    Run this script by calling  php testPushNotification.php "device Id" "device type" "language"
*/
    include_once "../database.inc";
    require_once('../PushNotifications.php');

    if($argv[3]=="en")
    {
        $message = array(
            "mtitle"=>'Opal Test ' . date("Y-m-d H:i:s"),
            "mdesc"=>'This is a test of your Opal push notifications.',
            "encode"=>'Yes'
        );
    }
    else if($argv[3]=="fr")
    {
        $message = array(
            "mtitle"=>'Test Opal ' . date("Y-m-d H:i:s"),
            "mdesc"=>'Ceci est un test de vos notifications Opal.',
            "encode"=>'Yes'
        );
    }
		 
	// device id
    $device = $argv[1];

	echo "<br />SENDING at " . date("Y-m-d H:i:s") . "<br />";

    if($argv[2]=="0")
    {
        $response = PushNotifications::iOS($message, $device);
    }
    else if($argv[2]=="1")
    {
        $response = PushNotifications::android($message, $device);
    }

	echo "<br />Complete<br />";

    // Return responses
    print json_encode($response);
?>
