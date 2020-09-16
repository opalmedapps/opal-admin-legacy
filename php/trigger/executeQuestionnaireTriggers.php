<?php

include_once("../config.php");

header('Content-Type: application/javascript');

$url = 'https://lxkvmap97/opalAdmin/user/system-login';

$postFields = array(
    "username"=>"TriggerSystem",
    "password"=>"pcGNdtwTV8Pd79FkLhP!ejH8Y^KR&4@u"
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);
$header_size = curl_getinfo($ch);
var_dump($result);

echo "\r\n\r\nlocal server session id: ".$_COOKIE['PHPSESSID']."\r\n\r\n";

if(preg_match("/PHPSESSID=(.*?)(?:;|\r\n)/", $result, $matches)){
    $phpSessionId = $matches[1];
    echo "\r\n\r\nremote server session id: $phpSessionId\r\n\r\n";
}else{
    die("Error!");
    /* Do something */
}

   
$trigger = new Trigger(true);
$sourceModuleId = MODULE_QUESTIONNAIRE; // define what type of trigger this is

// Need patientQuestionnaireSerNum from caller
//$trigger->executeTrigger($_POST, $sourceModuleId);
$trigger->executeTrigger(array("id" => 200), $sourceModuleId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);

?>
