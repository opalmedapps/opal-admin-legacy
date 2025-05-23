<?php

// SPDX-FileCopyrightText: Copyright (C) 2023 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/*
    Updated this test script so that it can be called without changing its code by using command-line arguments.

    The first argument (arg[1]) is the OpalDB.PatientDeviceIdentifier.RegistrationId for the desired device.
    The second argument (arg[2]) is device type, 0 is IOS and 1 is Android.
    The third argument (arg[3]) is language, en is English and fr is French.

    Example run in dev: `dcd exec opaladmin php publisher/php/tests/testPushNotification.php 65e3cb0453f2be9293efc3fd31ec1133a0f6f0d031ee0865d7629de09a487c7a 0 en`
*/
    $scriptDir = dirname(__FILE__);
    include_once $scriptDir . '/../database.inc';
    require_once $scriptDir . '/../PushNotification.php';

    if(count($argv) < 4)
    {
        echo "*********Push Notification Test Script Usage*************************" . PHP_EOL
            . PHP_EOL
            . "Note: Run the command below together with at least 3 arguments as mentioned." . PHP_EOL
            . PHP_EOL
            . "php testPushNotification.php 'registration id' 'device type' 'language' " . PHP_EOL
            . PHP_EOL
            . "* registration Id (in the column RegistrationId of PatientDeviceIdentifier table) " . PHP_EOL
            . PHP_EOL
            . "* device type is 0 (IOS) or 1 (Android) " . PHP_EOL
            . PHP_EOL
            . "* language is en (English) or fr (French) " . PHP_EOL
            . PHP_EOL
            . "*********************************************************************" . PHP_EOL;
    }
    else
    {
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
                "mdesc"=>'Vous avez reçu un nouveau questionnaire. Veuillez le compléter avant votre rendez-vous avec votre professionnel de la santé.',
                "encode"=>'Yes'
            );
        }

        // device id
        $device = $argv[1];

        echo "<br />SENDING at " . date("Y-m-d H:i:s") . "<br />";

        if($argv[2]=="0")
        {
            $response = PushNotification::iOS($message, $device);
        }
        else if($argv[2]=="1")
        {
            $response = PushNotification::android($message, $device);
        }

        echo "<br />Complete<br />\n";

        // Return responses
        print json_encode($response);
        echo "\n";
    }
?>
