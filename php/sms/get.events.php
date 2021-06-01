<?php
header('Content-Type: application/javascript');
include_once("../config.php");

$sms = new Sms();
$smsEventList = $sms->getEvents($_POST);

echo json_encode($smsEventList);
