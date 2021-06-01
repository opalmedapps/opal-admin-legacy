<?php
header('Content-Type: application/javascript');
include_once("../config.php");

$sms = new Sms();

$smsUpdates	= strip_tags($_POST['updateList']);

$response = 1;
foreach ($smsUpdates as $information){
    $response = $sms->updateActivationState($information);
    if($response != 1) break;
}

echo json_encode($response);
