<?php

include_once('sms.inc');
//$type = strip_tags($_POST['type']);
//$speciality = strip_tags($_POST['speciality']);
$type = $_GET['type'];
$speciality = $_GET['speciality'];

$sms = new Sms();
$smsEventList = $sms->getEvents($type,$speciality);

//header('Content-Type: application/javascript');
//http_response_code(HTTP_STATUS_SUCCESS);
echo json_encode($smsEventList);
