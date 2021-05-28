<?php
header('Content-Type: application/javascript');
include_once('sms.inc');
//$type = strip_tags($_POST['type']);
//$speciality = strip_tags($_POST['speciality']);
$type = $_POST['type'];
$speciality = $_POST['speciality'];

$sms = new Sms();
$smsEventList = $sms->getEvents($type,$speciality);


//http_response_code(HTTP_STATUS_SUCCESS);
echo json_encode($smsEventList);
