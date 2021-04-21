<?php
header('Content-Type: application/javascript');
include_once('sms.inc');

$sms = new Sms();
$smsSpecialityList = $sms->getSpecialityMessage();

echo json_encode($smsSpecialityList);