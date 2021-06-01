<?php
header('Content-Type: application/javascript');
include_once("../config.php");
$speciality = strip_tags($_POST['speciality']);
$type = strip_tags($_POST['type']);
$event = strip_tags($_POST['event']);
$language = strip_tags($_POST['language']);


$sms = new Sms();
$smsMessage = $sms->getMessage($speciality,$type,$event,$language);

echo json_encode($smsMessage);
//http_response_code(HTTP_STATUS_SUCCESS);
