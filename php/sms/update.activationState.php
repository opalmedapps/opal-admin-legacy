<?php
header('Content-Type: application/javascript');
include_once('sms.inc');

$sms = new Sms();

$smsUpdates	= $_POST['updateList'];
//$user = $_POST['user'];

$response = 1;
foreach ($smsUpdates as $information){
    $response = $sms->updateActivationState($information);
    if($response != 1) break;
}

echo json_encode($response);
