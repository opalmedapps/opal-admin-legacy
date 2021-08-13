<?php
include_once("../config.php");

$sms = new Sms();
$smsSpecialityList = $sms->getSpecialityMessage();

header('Content-Type: application/javascript');
echo json_encode($smsSpecialityList);