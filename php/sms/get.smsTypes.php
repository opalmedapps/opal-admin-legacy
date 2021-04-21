<?php

include_once('sms.inc');
$speciality = $_GET['speciality'];

$sms = new Sms();
$smsTypeList = $sms->getTypeMessage($speciality);

echo json_encode($smsTypeList);