<?php
header('Content-Type: application/javascript');
include_once('sms.inc');
$speciality = $_POST['speciality'];

$sms = new Sms();
$smsTypeList = $sms->getTypeMessage($speciality);

echo json_encode($smsTypeList);