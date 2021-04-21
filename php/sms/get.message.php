<?php

include_once('sms.inc');
//$speciality = strip_tags($_POST['speciality']);
//$type = strip_tags($_POST['type']);
//$event = strip_tags($_POST['event']);
//$language = strip_tags($_POST['language']);
$speciality = $_GET['speciality'];
$type = $_GET['type'];
$event = $_GET['event'];
$language = $_GET['language'];


$sms = new Sms();
$smsMessage = $sms->getMessage($speciality,$type,$event,$language);

echo json_encode($smsMessage);
//header('Content-Type: application/javascript');
//http_response_code(HTTP_STATUS_SUCCESS);
