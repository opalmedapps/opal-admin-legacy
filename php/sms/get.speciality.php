<?php
header('Content-Type: application/javascript');
include_once("../config.php");

$sms = new Sms(); // Object
$smsSpecialityList = $sms->getSpecialityMessage();

echo json_encode($smsSpecialityList);